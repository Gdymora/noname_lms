<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CaptureUtmParameters
{
    /**
     * Список параметров, которые мы хотим отслеживать
     */
    protected array $parameters = [
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'gclid', // Google Click ID
        'yclid', // Yandex Click ID
        'fbclid', // Facebook Click ID
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach ($this->parameters as $param) {
            if ($request->has($param)) {
                // Если параметр есть в URL — сохраняем в куки на 30 дней (43200 минут)
                // queue() добавляет куку к ответу
                Cookie::queue($param, $request->input($param), 43200);
            }
        }

        return $response;
    }
}