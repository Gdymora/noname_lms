<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\User;
use App\Traits\HasUtmCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class FormController extends Controller
{
    use HasUtmCollection;

    public function show($slug)
    {
        $form = Form::where('slug', $slug)->where('is_active', true)->firstOrFail();
        return \Inertia\Inertia::render('Public/FormView', [
            'form' => $form,
        ]);
    }

    public function submit(Request $request, Form $form)
    {
        // 1. Динамическая валидация на основе схемы
        $rules = [];
        $customMessages = [];

        foreach ($form->schema as $field) {
            $key = $field['name'];
            $fieldRules = [];

            if ($field['required'] ?? false) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }

            if ($field['type'] === 'email') {
                $fieldRules[] = 'email';
            }

            $rules[$key] = $fieldRules;
            $customMessages["$key.required"] = "Поле \"{$field['label']}\" обязательно.";
        }

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $user = Auth::user();
        $isNewUser = false;

        // 2. Ищем Email в данных формы (если есть поле типа email)
        // Находим ключ поля, у которого type === email
        $emailField = collect($form->schema)->firstWhere('type', 'email');
        $phoneField = collect($form->schema)->firstWhere('type', 'phone');
        
        $email = $emailField ? ($data[$emailField['name']] ?? null) : null;
        $phone = $phoneField ? ($data[$phoneField['name']] ?? null) : null;

        // Если юзер не авторизован, но есть email -> пробуем найти или создать
        if (!$user && $email) {
            $user = User::where('email', $email)->first();

            if (!$user) {
                // Создаем нового
                $password = Str::random(10);
                $user = User::create([
                    'name' => $data['name'] ?? 'Student', // Пытаемся угадать имя, если есть поле name
                    'email' => $email,
                    'password' => Hash::make($password),
                    'phone' => $phone, // Сразу пишем телефон
                    'utm_data' => $this->getUtmFromCookies(),
                ]);
                $user->assignRole('Student');
                $isNewUser = true;
                
                // Тут можно отправить письмо с паролем
                $user->notify(new \App\Notifications\WelcomeStudent()); // Или специальное письмо с паролем
                Auth::login($user);
            }
        }

        // 3. Если юзер уже был, но в форме указан телефон -> обновляем телефон
        if ($user && $phone && empty($user->phone)) {
            $user->update(['phone' => $phone]);
        }

        // 4. Сохраняем ответ
        FormSubmission::create([
            'form_id' => $form->id,
            'user_id' => $user?->id,
            'data' => $data,
            'utm_data' => $this->getUtmFromCookies(),
        ]);

        return back()->with('success', $form->settings['success_message'] ?? 'Спасибо!');
    }
}