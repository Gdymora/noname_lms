<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectStudentsFromAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Если пользователь авторизован...
        if ($user) {
            // ...но у него НЕТ ни одной служебной роли...
            if (!$user->hasAnyRole(['Super Admin', 'Teacher', 'Manager', 'Curator'])) {
                
                // ...то отправляем его учиться
                return redirect()->route('my.learning');
            }
        }

        return $next($request);
    }
}