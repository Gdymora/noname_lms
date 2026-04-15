<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use App\Models\Tariff;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Traits\HasUtmCollection;

class FastOrderController extends Controller
{
    use HasUtmCollection;

    public function store(Request $request, Course $course)
    {
        $user = Auth::user();

        // --- ВАЛИДАЦИЯ ГАЛОЧЕК ---
        // Если юзера нет ИЛИ у юзера еще не принята оферта -> она обязательна в запросе
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'tariff_id' => 'nullable|exists:tariffs,id',
            
            // Реклама всегда true/false (не обязательно true)
            'agree_marketing' => 'boolean',
        ];

        // Если пользователь гость или еще не принимал оферту - требуем галочку
        if (!$user || !$user->accepted_offer_at) {
            $rules['agree_offer'] = 'accepted'; // Должно быть true
        }
        // То же самое с политикой
        if (!$user || !$user->accepted_policy_at) {
            $rules['agree_policy'] = 'accepted';
        }

        // Если гость, и такого email нет в базе - нужен пароль
        if (!$user && !User::where('email', $request->email)->exists()) {
             // Если пароля нет в запросе - вернем ошибку, чтобы фронт показал поле
             if (!$request->filled('password')) {
                 return back()->withErrors(['password_required' => true]);
             }
             $rules['password'] = ['required', Rules\Password::defaults()];
        }

        $request->validate($rules);
        // -------------------------

        // Логика поиска/создания юзера
        if (!$user) {
                // Создаем нового
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'utm_data' => $this->getUtmFromCookies(),
                ]);
                $user->assignRole('Student');
                
                // === ДОБАВЛЯЕМ СЮДА ТОЖЕ ===
                $user->notify(new \App\Notifications\WelcomeStudent());
                // ===========================
                
                Auth::login($user);
            }

        // --- СОХРАНЯЕМ СОГЛАСИЯ (Обновляем таймстемпы, если пришла галочка) ---
        if ($request->accepted_offer_at) { 
             // Если уже было принято, не трогаем. Если нет - ставим сейчас.
             // Но с фронта приходит boolean 'agree_offer'.
        }

        $updates = [];
        if ($request->boolean('agree_offer') && !$user->accepted_offer_at) {
            $updates['accepted_offer_at'] = now();
        }
        if ($request->boolean('agree_policy') && !$user->accepted_policy_at) {
            $updates['accepted_policy_at'] = now();
        }
        if ($request->boolean('agree_marketing') && !$user->accepted_marketing_at) {
            $updates['accepted_marketing_at'] = now();
        }

        if (!empty($updates)) {
            $user->update($updates);
        }
        // ---------------------------------------------------------------------

        return $this->createOrderAndRedirect($user, $course, $request);
    }

    private function createOrderAndRedirect(User $user, Course $course, Request $request)
    {
        $amount = $course->price;
        $tariffId = null;

        if ($request->filled('tariff_id')) {
            $tariff = Tariff::find($request->tariff_id);
            if ($tariff && $tariff->course_id === $course->id) {
                $amount = $tariff->price;
                $tariffId = $tariff->id;
            }
        } elseif ($course->tariffs()->exists()) {
             return back()->with('error', 'Выберите тариф');
        }

        $existingOrder = Order::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->whereIn('status', ['new', 'processing', 'paid'])
            ->first();

        if ($existingOrder) {
            if ($existingOrder->status === 'paid') {
                // Если авторизован - в кабинет, иначе на страницу "уже есть"
                return Auth::check() 
                    ? redirect()->route('my.learning') 
                    : redirect()->route('public.course.order_exists', $course->slug);
            }
            return redirect()->route('public.course.order_exists', $course->slug);
        }

        $isFree = $amount === 0;
        
        $order = Order::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'tariff_id' => $tariffId,
            'amount' => $amount,
            'status' => $isFree ? 'paid' : 'new',
            'paid_at' => $isFree ? now() : null,
            'history_log' => ['action' => 'fast_order', 'ip' => request()->ip()],
            'utm_data' => $this->getUtmFromCookies(),
        ]);

        if ($isFree && Auth::check()) {
             return redirect()->route('my.learning');
        }

        return redirect()->route('payment.checkout', $order->id);
    }
}