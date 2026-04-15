<?php

namespace App\Policies;

use App\Models\CourseModule;
use App\Models\User;

class CourseModulePolicy
{
    // Админ может всё
    public function before(User $user, $ability)
    {
        if ($user->hasRole('Super Admin')) return true;
    }

    public function viewAny(User $user): bool
    {
        // Куратор не должен видеть список модулей
        return $user->hasRole('Teacher');
    }

    public function view(User $user, CourseModule $courseModule): bool
    {
        return $user->hasRole('Teacher') && $courseModule->course->teacher_id === $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('Teacher');
    }

    public function update(User $user, CourseModule $courseModule): bool
    {
        return $user->hasRole('Teacher') && $courseModule->course->teacher_id === $user->id;
    }

    public function delete(User $user, CourseModule $courseModule): bool
    {
        return $user->hasRole('Teacher') && $courseModule->course->teacher_id === $user->id;
    }
}