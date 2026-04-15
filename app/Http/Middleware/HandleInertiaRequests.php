<?php

namespace App\Http\Middleware;

use App\Models\Page;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? $request->user()->load('roles') : null,
                'roles' => $request->user() ? $request->user()->getRoleNames() : [],
            ],
            'appName' => config('app.name'),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
            ],
            // === ГЛОБАЛЬНЫЕ ДАННЫЕ ФУТЕРА ===
            'footerData' => function () {
                // 1. Берем настройки лендинга (кэшируем в памяти на время запроса)
                static $footerData = null;
                if ($footerData) return $footerData;

                $blocks = SystemSetting::where('key', 'landing_page_blocks')->value('payload') ?? [];
                
                // Ищем блок типа 'footer'
                $footerBlock = collect($blocks)->firstWhere('type', 'footer');
                $data = $footerBlock['data'] ?? [];

                // 2. Гидратация документов (превращаем ID в ссылки)
                if (!empty($data['documents'])) {
                    $pageIds = collect($data['documents'])->pluck('page_id')->filter()->toArray();
                    
                    if (!empty($pageIds)) {
                        $pages = Page::whereIn('id', $pageIds)->get()->keyBy('id');

                        foreach ($data['documents'] as &$docItem) {
                            if (isset($docItem['page_id']) && isset($pages[$docItem['page_id']])) {
                                $page = $pages[$docItem['page_id']];
                                $docItem['url'] = route('public.page', $page->slug);
                                if (empty($docItem['label'])) {
                                    $docItem['label'] = $page->title;
                                }
                            }
                        }
                    }
                }

                return $footerData = $data;
            },
        ];
    }
}