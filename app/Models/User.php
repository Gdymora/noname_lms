<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles;

    /**
     * Разрешаем заполнять все поля, кроме id (защита отключена для удобства разработки)
     */
    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime',
            'is_active' => 'boolean',
            // Новые поля
            'accepted_offer_at' => 'datetime',
            'accepted_policy_at' => 'datetime',
            'accepted_marketing_at' => 'datetime',
            'utm_data' => 'array',
        ];
    }

    // Настройка доступа в админку (Filament)
  public function canAccessPanel(Panel $panel): bool
    {
        // Список ролей, которым можно в Админку
        // Если у пользователя есть ХОТЯ БЫ ОДНА из этих ролей - пускаем.
        // Даже если у него параллельно есть роль "Student".
        return $this->hasAnyRole(['Super Admin', 'Teacher', 'Manager', 'Curator']);
    }

    // === СВЯЗИ ===

    // Курсы, которые ведет этот пользователь (как учитель)
    public function teachedCourses()
    {
        return $this->hasMany(Course::class, 'teacher_id');
    }

    // Сданные домашки (как студент)
    public function homeworkSubmissions()
    {
        return $this->hasMany(HomeworkSubmission::class, 'student_id');
    }

    // Уроки, которые студент открывал или прошел
    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_user')
            ->withPivot(['unlocked_at', 'completed_at']);
    }

    // Связь с заказами (Покупки пользователя)
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Курсы, где я назначен куратором
    public function curatedCourses()
    {
        return $this->belongsToMany(Course::class, 'course_curator', 'user_id', 'course_id');
    }

    
}