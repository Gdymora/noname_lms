<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'is_stop_lesson' => 'boolean',
        'is_published' => 'boolean',    // <--- Добавили
        'available_at' => 'datetime',   // <--- Добавили
    ];

    // === СВЯЗИ ===

    public function module()
    {
        return $this->belongsTo(CourseModule::class, 'module_id');
    }

    // Блоки контента (текст, видео...)
    public function blocks()
    {
        return $this->hasMany(ContentBlock::class)->orderBy('sort_order');
    }

    // Задание к уроку (обычно одно)
    public function homework()
    {
        return $this->hasOne(Homework::class);
    }
    
    // Связь с пользователями через таблицу прогресса
    public function students()
    {
        return $this->belongsToMany(User::class, 'lesson_user')
            ->withPivot(['unlocked_at', 'completed_at'])
            ->withTimestamps();
    }

    public function tariffs()
    {
        return $this->belongsToMany(Tariff::class, 'lesson_tariff');
    }
}