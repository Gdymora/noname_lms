<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use YooKassa\Client;

class PaymentController extends Controller
{
    public function checkout(Order $order)
    {
        if ($order->user_id !== auth()->id()) abort(403);
        if ($order->status === 'paid') return redirect()->route('my.learning');

        return Inertia::render('Payment/Checkout', [
            'order' => $order->load('course', 'tariff'),
            'methods' => [
                'yookassa' => (bool) SystemSetting::where('key', 'yookassa_enabled')->value('payload'),
                'yoomoney_p2p' => (bool) SystemSetting::where('key', 'yoomoney_p2p_enabled')->value('payload'),
            ]
        ]);
    }

    public function pay(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) abort(403);

        $method = $request->input('method');

        if ($method === 'yookassa') {
            return $this->payWithYookassa($order);
        }
        
        if ($method === 'yoomoney_p2p') {
            return $this->payWithP2P($order);
        }

        return back()->with('error', 'Неизвестный метод оплаты.');
    }

    private function payWithYookassa(Order $order)
    {
        $shopId = SystemSetting::where('key', 'yookassa_shop_id')->value('payload');
        $secretKey = SystemSetting::where('key', 'yookassa_secret_key')->value('payload');

        if (!$shopId || !$secretKey) return back()->with('error', 'Ошибка конфигурации.');

        $client = new Client();
        $client->setAuth($shopId, $secretKey);

        try {
            $payment = $client->createPayment([
                'amount' => ['value' => $order->amount, 'currency' => 'RUB'],
                'confirmation' => ['type' => 'redirect', 'return_url' => route('courses.thankyou', $order->course->slug)],
                'capture' => true,
                'description' => 'Заказ #' . $order->id,
                'metadata' => ['order_id' => $order->id],
            ], uniqid('', true));

            $order->update(['payment_id' => $payment->getId(), 'payment_method' => 'yookassa']);

            return Inertia::location($payment->getConfirmation()->getConfirmationUrl());
        } catch (\Exception $e) {
            return back()->with('error', 'Ошибка ЮKassa: ' . $e->getMessage());
        }
    }

    private function payWithP2P(Order $order)
    {
        $account = SystemSetting::where('key', 'yoomoney_p2p_account')->value('payload');
        
        if (!$account) return back()->with('error', 'Кошелек не настроен.');

        // Формируем ссылку на форму QuickPay
        $params = [
            'receiver' => $account,
            'quickpay-form' => 'shop', // Тип формы "Магазин"
            'targets' => 'Оплата заказа #' . $order->id,
            'paymentType' => 'PC', // PC = Кошелек ЮMoney, AC = Банковская карта
            'sum' => $order->amount, 
            'label' => $order->id, // ID заказа для вебхука (Критично важно!)
            'successURL' => route('courses.thankyou', $order->course->slug),
        ];

        $url = 'https://yoomoney.ru/quickpay/confirm.xml?' . http_build_query($params);

        $order->update(['payment_method' => 'yoomoney_p2p']);

        return Inertia::location($url);
    }
}