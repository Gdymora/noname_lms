<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Inertia\Inertia;

class PublicCourseController extends Controller
{
    // Страница курса (Лендинг)
    public function show($slug)
    {
        $course = Course::where('slug', $slug)
            ->where('is_published', true)
            ->with('teacher:id,name,avatar_url')
            // Грузим тарифы для модального окна и отображения "от X руб"
            ->with(['tariffs' => fn($q) => $q->orderBy('price')])
            ->withMin('tariffs', 'price')
            ->firstOrFail();

        // Программа обучения (Syllabus) - ПОЛНАЯ СТРУКТУРА С ТАРИФАМИ
        // Точно так же, как внутри платформы, чтобы показать бейджики
        $syllabus = $course->modules()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->with([
                'tariffs', // Тарифы модуля
                'lessons' => fn($q) => $q->orderBy('sort_order')->with('tariffs'), // Уроки + их тарифы
                'children' => fn($q) => $q->orderBy('sort_order')
                    ->with(['tariffs', 'lessons' => fn($q2) => $q2->orderBy('sort_order')->with('tariffs')]) // Подмодули + их тарифы
            ])
            ->get();

        return Inertia::render('Public/CourseLanding', [
            'course' => $course,
            'syllabus' => $syllabus,
        ]);
    }

    public function thankYou($slug)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        return Inertia::render('Courses/ThankYou', ['course' => $course]);
    }

    public function orderExists($slug)
    {
        $course = Course::where('slug', $slug)->firstOrFail();
        return Inertia::render('Courses/OrderExists', ['course' => $course]);
    }
}