<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        // Учитель может видеть список (своих продаж)
        return $user->hasRole('Teacher');
    }

    public function view(User $user, Order $order): bool
    {
        // Учитель видит конкретный заказ, если это его курс
        if ($user->hasRole('Teacher')) {
            return $order->course->teacher_id === $user->id;
        }
        return false;
    }

    public function update(User $user, Order $order): bool
    {
        // Учитель НЕ может менять статус заказа (это дело менеджеров)
        return false; 
    }

    // Остальные методы (create, delete) запрещаем
    public function create(User $user): bool { return false; }
    public function delete(User $user, Order $order): bool { return false; }
}