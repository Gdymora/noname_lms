<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        // Только админ видит этот раздел в меню
        return $user->hasRole('Super Admin');
    }

    // Остальное запрещаем всем (админу разрешит before)
    public function view(User $user, Page $page): bool { return false; }
    public function create(User $user): bool { return false; }
    public function update(User $user, Page $page): bool { return false; }
    public function delete(User $user, Page $page): bool { return false; }
}