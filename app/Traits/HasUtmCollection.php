<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Request;

trait HasUtmCollection
{
    /**
     * Собирает UTM метки из кук
     */
    protected function getUtmFromCookies(): array
    {
        $data = [];
        $keys = [
            'utm_source', 'utm_medium', 'utm_campaign', 
            'utm_term', 'utm_content', 
            'gclid', 'yclid', 'fbclid'
        ];

        foreach ($keys as $key) {
            // Проверяем Cookie
            // Laravel автоматически расшифровывает куки, если использовать фасад Cookie или Request
            $value = \Illuminate\Support\Facades\Request::cookie($key);
            
            if ($value) {
                $data[$key] = $value;
            }
        }

        // Добавим реферер (откуда пришел, если есть)
        $referer = request()->headers->get('referer');
        if ($referer && !isset($data['utm_source'])) {
             $data['referer'] = $referer;
        }

        return $data;
    }
}