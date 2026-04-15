<?php

namespace App\Policies;

use App\Models\Lesson;
use App\Models\User;

class LessonPolicy
{
    public function before(User $user, $ability)
    {
        if ($user->hasRole('Super Admin')) return true;
    }

    public function viewAny(User $user): bool
    {
        // Куратор не видит раздел "Уроки"
        return $user->hasRole('Teacher');
    }

    public function view(User $user, Lesson $lesson): bool
    {
        return $user->hasRole('Teacher') && $lesson->module->course->teacher_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Teacher');
    }

    public function update(User $user, Lesson $lesson): bool
    {
        return $user->hasRole('Teacher') && $lesson->module->course->teacher_id === $user->id;
    }

    public function delete(User $user, Lesson $lesson): bool
    {
        return $user->hasRole('Teacher') && $lesson->module->course->teacher_id === $user->id;
    }
}