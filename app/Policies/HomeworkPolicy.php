<?php

namespace App\Policies;

use App\Models\Homework;
use App\Models\User;

class HomeworkPolicy
{
    /**
     * Мастер-ключ: Супер-Админ может ВСЁ.
     * Этот метод запускается перед любой проверкой.
     */
    public function before(User $user, $ability)
    {
        if ($user->hasRole('Super Admin')) {
            return true;
        }
    }

    // Просмотр списка в меню (Admin Panel)
    public function viewAny(User $user): bool
    {
        // Видят только Админ (через before) и Учитель
        return $user->hasRole('Teacher');
    }

    // Просмотр конкретного задания
    public function view(User $user, Homework $homework): bool
    {
        // Учитель видит, если это ДЗ к его уроку
        return $user->hasRole('Teacher') && 
               $homework->lesson->module->course->teacher_id === $user->id;
    }

    // Создание (Кнопка "New Homework")
    public function create(User $user): bool
    {
        return $user->hasRole('Teacher');
    }

    // Редактирование
    public function update(User $user, Homework $homework): bool
    {
        return $user->hasRole('Teacher') && 
               $homework->lesson->module->course->teacher_id === $user->id;
    }

    // Удаление
    public function delete(User $user, Homework $homework): bool
    {
        return $user->hasRole('Teacher') && 
               $homework->lesson->module->course->teacher_id === $user->id;
    }
}