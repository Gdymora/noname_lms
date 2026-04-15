<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use App\Models\SystemSetting;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TildaWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Проверка безопасности (Секретный ключ)
        // Мы будем хранить ключ в настройках. Тильда должна передать его в скрытом поле 'secret'
        $settingsKey = SystemSetting::where('key', 'tilda_secret')->value('payload');
        
        // Если ключ не задан в админке или не совпадает с присланным
        if (!$settingsKey || $request->input('secret') !== $settingsKey) {
            return response()->json(['error' => 'Invalid secret key'], 403);
        }

        // 2. Валидация данных от Тильды
        // Тильда шлет payment (данные платежа) и поля формы
        // Нам нужны: email, course_id (скрытое), tariff_id (скрытое, опционально)
        
        $email = $request->input('email');
        $courseId = $request->input('course_id');
        $tariffId = $request->input('tariff_id');
        
        if (!$email || !$courseId) {
            return response()->json(['error' => 'Missing required fields'], 400);
        }

        // 3. Поиск или Создание пользователя
        $user = User::where('email', $email)->first();
        $isNewUser = false;
        $password = null;

        if (!$user) {
            $isNewUser = true;
            $password = Str::random(10); // Генерируем пароль
            
            $user = User::create([
                'name' => $request->input('name') ?? 'Student', // Если имя не пришло
                'email' => $email,
                'password' => Hash::make($password),
            ]);
            
            $user->assignRole('Student');
            
            // Тут можно отправить письмо с паролем:
            // $user->notify(new NewUserPasswordNotification($password));
        }

        // 4. Создание заказа
        // Проверяем дубликаты (чтобы не начислять дважды за один webhook)
        // Тильда шлет 'payment' массив, где есть 'orderid' (номер транзакции банка)
        $paymentData = $request->input('payment', []);
        $transactionId = $paymentData['orderid'] ?? null;
        $amount = $paymentData['amount'] ?? 0; // Сумма от Тильды

        // Если такой заказ уже есть (по транзакции или просто активный доступ)
        $existingOrder = Order::where('user_id', $user->id)
            ->where('course_id', $courseId)
            ->where('status', 'paid')
            ->first();

        if ($existingOrder) {
            return response('OK'); // Уже куплено, просто говорим Тильде ОК
        }

        // Создаем заказ
        Order::create([
            'user_id' => $user->id,
            'course_id' => $courseId,
            'tariff_id' => $tariffId,
            'amount' => (int) ($amount * 100), // Переводим в копейки? У нас же рубли. 
            // ВАЖНО: Мы перешли на рубли в базе, значит пишем как есть:
            // 'amount' => (int) $amount, 
            // НО: Если вдруг логика "копейки" где-то осталась, проверяй. 
            // Сейчас у нас в базе рубли, так что:
            'amount' => (int) $amount,
            'status' => 'paid', // Сразу оплачено!
            'paid_at' => now(),
            'history_log' => [
                'source' => 'tilda',
                'transaction_id' => $transactionId,
                'raw_data' => $request->all(),
                'auto_registered' => $isNewUser
            ]
        ]);

        return response('OK');
    }
}