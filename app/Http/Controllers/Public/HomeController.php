<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Page;
use App\Models\SystemSetting;
use Inertia\Inertia;

class HomeController extends Controller
{
    public function index()
    {
        // 1. Получаем настройки
        $blocks = SystemSetting::where('key', 'landing_page_blocks')->value('payload') ?? [];

        // 2. Гидратация данных (курсы и документы)
        $processedBlocks = collect($blocks)->map(function ($block) {
            
            // А. Обработка КУРСОВ
            if ($block['type'] === 'courses' && !empty($block['data']['course_ids'])) {
                $courses = Course::whereIn('id', $block['data']['course_ids'])
                    ->where('is_published', true)
                    ->with('teacher:id,name,avatar_url')
                    ->withMin('tariffs', 'price')
                    ->get();
                
                // Сортировка как в админке
                $sortedCourses = $courses->sortBy(function ($course) use ($block) {
                    return array_search($course->id, $block['data']['course_ids']);
                })->values();

                $block['data']['courses'] = $sortedCourses;
            }

            // Б. Обработка ФУТЕРА (Документы)
            if ($block['type'] === 'footer' && !empty($block['data']['documents'])) {
                // Собираем ID всех страниц
                $pageIds = collect($block['data']['documents'])->pluck('page_id')->filter()->toArray();
                
                if (!empty($pageIds)) {
                    // Грузим страницы одним запросом
                    $pages = Page::whereIn('id', $pageIds)->get()->keyBy('id');

                    // Проходим по списку и обогащаем данными
                    foreach ($block['data']['documents'] as &$docItem) {
                        if (isset($docItem['page_id']) && isset($pages[$docItem['page_id']])) {
                            $page = $pages[$docItem['page_id']];
                            
                            // Генерируем ссылку
                            $docItem['url'] = route('public.page', $page->slug);
                            
                            // Если название не задано вручную, берем из страницы
                            if (empty($docItem['label'])) {
                                $docItem['label'] = $page->title;
                            }
                        }
                    }
                }
            }

            return $block;
        });

        return Inertia::render('Public/Home', [
            'blocks' => $processedBlocks,
            'user' => auth()->user(),
        ]);
    }
}