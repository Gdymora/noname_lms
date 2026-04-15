<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class YoomoneyP2PController extends Controller
{
    public function handle(Request $request)
    {
        // === 1. ЛОГИРОВАНИЕ (САМОЕ ВАЖНОЕ) ===
        // Записываем в файл laravel.log всё, что пришло в запросе.
        // Это поможет увидеть реальные данные от ЮMoney.
        Log::error('🔔 YooMoney P2P Webhook INCOMING:', $request->all());
        // =======================================

        // 2. Получаем секрет из настроек
        $secret = SystemSetting::where('key', 'yoomoney_p2p_secret')->value('payload');
        
        if (!$secret) {
            Log::error('YooMoney P2P: Secret not configured in Admin Panel');
            return response('Error: Secret not found', 500);
        }

        // 3. Валидация подписи (SHA-1)
        // ЮMoney шлет параметры именно в таком порядке для хеша:
        // notification_type & operation_id & amount & currency & datetime & sender & codepro & notification_secret & label
        $string = join('&', [
            $request->input('notification_type'),
            $request->input('operation_id'),
            $request->input('amount'),
            $request->input('currency'),
            $request->input('datetime'),
            $request->input('sender'),
            $request->input('codepro'),
            $secret,
            $request->input('label'), // В label мы передаем ID заказа
        ]);

        $hash = sha1($string);

        // Логируем сравнение хешей для отладки (если вдруг не совпадает)
        if ($hash !== $request->input('sha1_hash')) {
            Log::error('⚠️ YooMoney P2P: Hash mismatch', [
                'generated_hash' => $hash,
                'incoming_hash' => $request->input('sha1_hash'),
                'string_source' => $string
            ]);
            return response('Invalid Hash', 200); // 200 чтобы они отстали
        }

        // 4. Ищем и обновляем заказ
        $orderId = $request->input('label');
        
        if (!$orderId) {
            Log::error('YooMoney P2P: No label (order_id) provided');
            return response('OK');
        }

        $order = Order::find($orderId);

        if (!$order) {
            Log::error("YooMoney P2P: Order #$orderId not found");
            return response('OK');
        }

        if ($order->status !== 'paid') {
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_id' => $request->input('operation_id'),
                'payment_method' => 'yoomoney_p2p',
            ]);
            
            Log::error("✅ Order #{$order->id} marked as PAID via YooMoney P2P");
        } else {
            Log::error("ℹ️ Order #{$order->id} was already paid");
        }

        return response('OK');
    }
}