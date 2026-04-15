<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\HasUtmCollection;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    // Подключаем трейт для сбора UTM меток из кук
    use HasUtmCollection;

    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            // Обязательная галочка политики конфиденциальности
            'agree_policy' => 'accepted', 
        ]);

        // Собираем массив данных для создания
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            
            // Сохраняем UTM метки (из трейта)
            'utm_data' => $this->getUtmFromCookies(),
            
            // Фиксируем время согласия с обязательными документами
            // (Оферту тоже считаем принятой по факту регистрации)
            'accepted_offer_at' => now(), 
            'accepted_policy_at' => now(),
        ];

        // Если поставил галочку маркетинга
        if ($request->boolean('agree_marketing')) {
            $userData['accepted_marketing_at'] = now();
        }

        $user = User::create($userData);

        // Выдаем роль
        $user->assignRole('Student');

        // Отправляем приветственное письмо (через очередь)
        $user->notify(new \App\Notifications\WelcomeStudent());

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}