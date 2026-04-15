<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CourseController extends Controller
{
    // 1. Каталог курсов + Поиск
    public function index(Request $request)
    {
        $query = Course::where('is_published', true)
            ->with('teacher:id,name,avatar_url')
            ->withMin('tariffs', 'price'); 

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'ilike', "%{$search}%")
                  ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        $courses = $query->latest()->get();

        return Inertia::render('Courses/Index', [
            'courses' => $courses,
            'filters' => $request->only(['search']),
        ]);
    }

    // 2. Страница курса (Лендинг с программой)
    public function show($slug)
    {
        $course = Course::where('slug', $slug)
            ->where('is_published', true)
            ->with('teacher:id,name,avatar_url')
            ->with(['tariffs' => fn($q) => $q->orderBy('price')])
            ->firstOrFail();

        $syllabus = $course->modules()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->with([
                'lessons' => fn($q) => $q->orderBy('sort_order'), 
                'children' => fn($q) => $q->orderBy('sort_order')
                    ->with(['lessons' => fn($q2) => $q2->orderBy('sort_order')])
            ])
            ->get();

        $userOrder = null;
        
        // По умолчанию берем ссылки курса
        $telegramLinks = [
            'channel' => $course->telegram_channel_link,
            'chat' => $course->telegram_chat_link,
        ];

        if (Auth::check()) {
            $userOrder = Order::where('user_id', Auth::id())
                ->where('course_id', $course->id)
                ->where('status', 'paid')
                ->with('tariff')
                ->first();

            // Если есть заказ и тариф, пробуем взять персональные ссылки
            if ($userOrder && $userOrder->tariff) {
                if (!empty($userOrder->tariff->telegram_channel_link)) {
                    $telegramLinks['channel'] = $userOrder->tariff->telegram_channel_link;
                }
                if (!empty($userOrder->tariff->telegram_chat_link)) {
                    $telegramLinks['chat'] = $userOrder->tariff->telegram_chat_link;
                }
            }
        }

        return Inertia::render('Courses/Show', [
            'course' => $course,
            'syllabus' => $syllabus,
            'userOrder' => $userOrder,
            'telegramLinks' => $telegramLinks, // <-- Передаем на фронт
        ]);
    }

    // 3. Страница "Спасибо за покупку"
    public function thankYou($slug)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        
        return Inertia::render('Courses/ThankYou', [
            'course' => $course
        ]);
    }

    // 4. Страница "Заказ уже существует"
    public function orderExists($slug)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        
        return Inertia::render('Courses/OrderExists', [
            'course' => $course
        ]);
    }

    // 5. Мое обучение (Личный кабинет)
    public function myCourses()
    {
        $user = auth()->user();

        $myCourses = Course::whereHas('orders', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('status', 'paid');
        })
        ->with('teacher:id,name,avatar_url')
        ->get();

        $myCourses->transform(function ($course) use ($user) {
            $totalLessons = \App\Models\Lesson::whereHas('module', function ($q) use ($course) {
                $q->where('course_id', $course->id);
            })->count();

            $completedLessons = $user->lessons()
                ->whereNotNull('completed_at')
                ->whereHas('module', function ($q) use ($course) {
                    $q->where('course_id', $course->id);
                })
                ->count();

            $course->progress = $totalLessons > 0 
                ? round(($completedLessons / $totalLessons) * 100) 
                : 0;

            return $course;
        });

        return Inertia::render('MyLearning', [
            'courses' => $myCourses
        ]);
    }
}