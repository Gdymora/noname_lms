<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class LearningController extends Controller
{
    public function show(Course $course, $lessonSlug = null)
    {
        $user = Auth::user();

        // 1. Проверка заказа и прав
        $order = $course->orders()
            ->where('user_id', $user->id)
            ->where('status', 'paid')
            ->first();

        $hasPrivilegedAccess = ($course->teacher_id === $user->id) || $user->hasRole('Super Admin');

        if (!$order && !$hasPrivilegedAccess) {
            return redirect()->route('courses.show', $course->slug)
                ->with('error', 'Сначала нужно записаться на курс.');
        }

        // 2. Загружаем структуру курса
        $rawSyllabus = $course->modules()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->with([
                'tariffs', 
                'lessons.tariffs', 
                'lessons.module.tariffs',
                'children.tariffs', 
                'children.lessons.tariffs', 
                'children.lessons.module.tariffs'
            ])
            ->get();

        // 3. Настраиваем проверки
        $tariffId = $order?->tariff_id;
        $now = now();

        // Проверка тарифа (Если нет доступа — скрываем полностью)
        $checkTariffAccess = function($entity) use ($tariffId, $hasPrivilegedAccess) {
            if ($hasPrivilegedAccess) return true;
            if ($entity->tariffs->isEmpty()) return true;
            if (!$tariffId) return false;
            return $entity->tariffs->contains('id', $tariffId);
        };

        // Проверка времени (Если рано — блокируем, но показываем)
        $checkTimeAccess = function($lesson) use ($now, $hasPrivilegedAccess) {
            if ($hasPrivilegedAccess) return true;
            
            // Если урок выключен вручную
            if (!$lesson->is_published) return false;
            
            // Если есть дата открытия и она в будущем
            if ($lesson->available_at && $lesson->available_at > $now) return false;
            
            return true;
        };

        // 4. Обработка дерева (Фильтрация + Маркировка)
        $syllabus = $rawSyllabus->filter(function ($module) use ($checkTariffAccess, $checkTimeAccess) {
            // Если модуль недоступен по тарифу - убираем
            if (!$checkTariffAccess($module)) return false;

            // Обработка уроков модуля
            $visibleLessons = $module->lessons->filter(function ($l) use ($checkTariffAccess, $checkTimeAccess) {
                // 1. Тариф
                if (!$checkTariffAccess($l)) return false;
                
                // 2. Время (ставим флаг)
                $l->is_locked_by_date = !$checkTimeAccess($l);
                
                if ($l->is_locked_by_date) {
                    if (!$l->is_published) {
                        $l->locked_message = 'Скоро выйдет';
                    } else {
                        $l->locked_message = 'Откроется ' . $l->available_at->format('d.m H:i');
                    }
                }
                return true; // Оставляем в списке (даже если закрыт по времени)
            })->values();
            $module->setRelation('lessons', $visibleLessons);

            // Обработка подмодулей
            $visibleChildren = $module->children->filter(function ($child) use ($checkTariffAccess, $checkTimeAccess) {
                if (!$checkTariffAccess($child)) return false;

                $childLessons = $child->lessons->filter(function ($l) use ($checkTariffAccess, $checkTimeAccess) {
                    if (!$checkTariffAccess($l)) return false;
                    
                    $l->is_locked_by_date = !$checkTimeAccess($l);
                    if ($l->is_locked_by_date) {
                        if (!$l->is_published) {
                            $l->locked_message = 'Скоро выйдет';
                        } else {
                            $l->locked_message = 'Откроется ' . $l->available_at->format('d.m H:i');
                        }
                    }
                    return true;
                })->values();
                $child->setRelation('lessons', $childLessons);
                return true;
            })->values();
            $module->setRelation('children', $visibleChildren);

            return true;
        })->values();

        // 5. Плоский список ВСЕХ ВИДИМЫХ уроков (включая закрытые по дате)
        $allVisibleLessons = collect();
        foreach ($syllabus as $module) {
            foreach ($module->lessons as $l) $allVisibleLessons->push($l);
            foreach ($module->children as $child) {
                foreach ($child->lessons as $l) $allVisibleLessons->push($l);
            }
        }

        // 6. Определяем текущий урок
        if ($lessonSlug) {
            $currentLesson = $allVisibleLessons->first(fn($l) => $l->slug === $lessonSlug);

            if (!$currentLesson) {
                // Урока нет в списке (значит скрыт тарифом или не существует)
                return redirect()->route('my.learning')->with('error', 'Урок недоступен.');
            }

            // Если пытаются зайти в урок, закрытый по времени
            if ($currentLesson->is_locked_by_date) {
                return redirect()->route('my.learning')->with('error', 'Этот урок пока закрыт: ' . $currentLesson->locked_message);
            }

            // Догружаем связи
            $currentLesson->load([
                'blocks.testResults' => fn($q) => $q->where('user_id', $user->id),
                'homework',
                'tariffs', 'module.tariffs'
            ]);

        } else {
            // Авто-поиск: ищем первый, который НЕ заблокирован по времени
            $currentLesson = $allVisibleLessons->first(fn($l) => !$l->is_locked_by_date);

            if (!$currentLesson) {
                return redirect()->route('my.learning')->with('error', 'Доступных уроков пока нет. Ожидайте открытия.');
            }
            return redirect()->route('learning.lesson', [$course->slug, $currentLesson->slug]);
        }

        // 7. Навигация (Кнопки)
        // Находим индекс в общем списке
        $currentIndex = $allVisibleLessons->search(fn($l) => $l->id === $currentLesson->id);
        
        // Ищем ПРЕДЫДУЩИЙ ОТКРЫТЫЙ урок (пропускаем закрытые по дате, если они есть сзади)
        $prevLesson = null;
        for ($i = $currentIndex - 1; $i >= 0; $i--) {
            if (!$allVisibleLessons[$i]->is_locked_by_date) {
                $prevLesson = $allVisibleLessons[$i];
                break;
            }
        }

        // 8. Логика завершения (Тесты + ДЗ)
        $canComplete = true;
        if (!$hasPrivilegedAccess) {
            foreach ($currentLesson->blocks as $block) {
                if ($block->type === 'quiz') {
                    $passed = $block->testResults->where('is_passed', true)->isNotEmpty();
                    if (!$passed) { $canComplete = false; break; }
                }
            }
            if ($currentLesson->is_stop_lesson && $currentLesson->homework?->is_required) {
                $submission = $currentLesson->homework->submissions()
                    ->where('student_id', Auth::id())
                    ->first();
                if (!$submission || $submission->status !== 'approved') {
                    $canComplete = false;
                }
            }
        }

        $submission = null;
        if ($currentLesson->homework) {
            $submission = $currentLesson->homework->submissions()->where('student_id', Auth::id())->first();
        }

        return Inertia::render('Learning/Show', [
            'course' => $course,
            'syllabus' => $syllabus,
            'lesson' => $currentLesson,
            'homework' => $currentLesson->homework, 
            'submission' => $submission,
            'prevLessonUrl' => $prevLesson ? route('learning.lesson', [$course->slug, $prevLesson->slug]) : null,
            'canComplete' => $canComplete,
            'isAdminView' => $hasPrivilegedAccess,
        ]);
    }

    public function checkTest(Request $request, \App\Models\ContentBlock $block)
    {
        if ($block->type !== 'quiz') abort(404);
        $userAnswers = $request->input('answers', []);
        $correctCount = 0;
        $totalQuestions = count($block->content['questions'] ?? []);

        if ($totalQuestions === 0) return back();

        foreach ($block->content['questions'] as $qIndex => $question) {
            if (isset($userAnswers[$qIndex])) {
                $ansIndex = $userAnswers[$qIndex];
                if (isset($question['answers'][$ansIndex]) && ($question['answers'][$ansIndex]['is_correct'] ?? false)) {
                    $correctCount++;
                }
            }
        }

        $score = round(($correctCount / $totalQuestions) * 100);
        $minScore = $block->content['min_score'] ?? 70;
        $isPassed = $score >= $minScore;

        TestResult::updateOrCreate(
            ['user_id' => auth()->id(), 'content_block_id' => $block->id],
            ['score_percent' => $score, 'is_passed' => $isPassed, 'user_answers' => $userAnswers]
        );

        return back()->with($isPassed ? 'success' : 'error', $isPassed ? "Тест сдан! ($score%)" : "Не сдан ($score% из $minScore%)");
    }

    public function markAsComplete(Lesson $lesson)
    {
        $user = Auth::user();
        $user->lessons()->syncWithoutDetaching([$lesson->id => ['completed_at' => now()]]);

        // Повторяем логику поиска СЛЕДУЮЩЕГО ОТКРЫТОГО
        $course = $lesson->module->course;
        $order = $course->orders()->where('user_id', $user->id)->where('status', 'paid')->first();
        $hasPrivilegedAccess = ($course->teacher_id === $user->id) || $user->hasRole('Super Admin');
        $tariffId = $order?->tariff_id;
        $now = now();

        $rawSyllabus = $course->modules()->whereNull('parent_id')->orderBy('sort_order')
            ->with(['tariffs', 'lessons.tariffs', 'lessons.module.tariffs', 'children.tariffs', 'children.lessons.tariffs'])
            ->get();

        // Хелперы (копируем логику)
        $checkTariff = fn($e) => $hasPrivilegedAccess || $e->tariffs->isEmpty() || ($tariffId && $e->tariffs->contains('id', $tariffId));
        
        $checkTime = function($l) use ($now, $hasPrivilegedAccess) {
            if ($hasPrivilegedAccess) return true;
            if (!$l->is_published) return false;
            if ($l->available_at && $l->available_at > $now) return false;
            return true;
        };

        // Строим список видимых
        $allVisibleLessons = collect();
        foreach ($rawSyllabus as $module) {
            if (!$checkTariff($module)) continue;
            foreach ($module->lessons as $l) {
                if ($checkTariff($l)) $allVisibleLessons->push($l);
            }
            foreach ($module->children as $ch) {
                if (!$checkTariff($ch)) continue;
                foreach ($ch->lessons as $l) {
                    if ($checkTariff($l)) $allVisibleLessons->push($l);
                }
            }
        }

        $currentIndex = $allVisibleLessons->search(fn($l) => $l->id === $lesson->id);
        
        // Ищем следующий НЕЗАБЛОКИРОВАННЫЙ ПО ВРЕМЕНИ
        $nextLesson = null;
        for ($i = $currentIndex + 1; $i < $allVisibleLessons->count(); $i++) {
            if ($checkTime($allVisibleLessons[$i])) {
                $nextLesson = $allVisibleLessons[$i];
                break;
            }
        }

        if ($nextLesson) {
            return redirect()->route('learning.lesson', [$course->slug, $nextLesson->slug]);
        }

        return redirect()->route('my.learning')->with('success', 'Курс завершен (или следующие уроки пока закрыты).');
    }
}