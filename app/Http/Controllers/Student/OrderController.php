<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasUtmCollection;
use Inertia\Inertia; // Не забудь этот импорт

class OrderController extends Controller
{
    use HasUtmCollection;

    // === НОВЫЙ МЕТОД: СПИСОК ЗАКАЗОВ ===
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['course', 'tariff']) // Подгружаем связи
            ->latest() // Свежие сверху
            ->get();

        return Inertia::render('MyOrders', [
            'orders' => $orders
        ]);
    }
    // ====================================

    public function enroll(Request $request, Course $course)
    {
        $user = Auth::user();
        
        $amount = $course->price;
        $tariffId = null;

        if ($request->filled('tariff_id')) {
            $tariff = Tariff::where('course_id', $course->id)
                ->where('id', $request->input('tariff_id'))
                ->firstOrFail();
            
            $amount = $tariff->price;
            $tariffId = $tariff->id;
        } 
        elseif ($course->tariffs()->exists()) {
            return redirect()->back()->with('error', 'Пожалуйста, выберите тариф.');
        }

        // Проверяем дубликаты
        $existingOrder = Order::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['new', 'processing', 'paid'])
            ->first();

        if ($existingOrder) {
            if ($existingOrder->status === 'paid') {
                return redirect()->route('my.learning');
            }
            // Если есть неоплаченный - ведем на оплату
            return redirect()->route('payment.checkout', $existingOrder->id);
        }

        // Создаем заказ
        $isFree = $amount === 0;

        $order = Order::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'tariff_id' => $tariffId,
            'amount' => $amount,
            'status' => $isFree ? 'paid' : 'new',
            'paid_at' => $isFree ? now() : null,
            'history_log' => ['action' => 'created_by_student', 'ip' => request()->ip()],
            'utm_data' => $this->getUtmFromCookies(),
        ]);

        if ($isFree) {
            return redirect()->route('my.learning')->with('success', 'Вы успешно записались на курс!');
        }

        return redirect()->route('payment.checkout', $order->id);
    }
}