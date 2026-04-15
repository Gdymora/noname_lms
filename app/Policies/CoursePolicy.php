<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    /**
     * Выполняется перед любым другим методом.
     * Если это Супер-Админ, разрешаем всё сразу.
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('Super Admin')) {    
            return true;
        }
    }

    // Просмотр списка (разрешено всем, кто вошел в админку)
    public function viewAny(User $user): bool
    {
        // Только Админ и Учитель
        return $user->hasRole('Super Admin') || $user->hasRole('Teacher');
    }

    // Просмотр одного курса
    public function view(User $user, Course $course): bool
    {
        // Учитель видит только свои курсы
        return $user->id === $course->teacher_id;
    }

    // Создание (разрешено Учителям)
    public function create(User $user): bool
    {
        // Куратор НЕ может создавать курсы
        return $user->hasRole('Teacher'); 
    }

    // Редактирование
    public function update(User $user, Course $course): bool
    {
        // Только если это автор курса
        return $user->id === $course->teacher_id;
    }

    // Удаление
    public function delete(User $user, Course $course): bool
    {
        return $user->id === $course->teacher_id;
    }
}