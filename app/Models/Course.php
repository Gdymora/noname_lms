<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_published' => 'boolean',
        'price' => 'integer',
    ];

    // === СВЯЗИ ===

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function modules()
    {
        return $this->hasMany(CourseModule::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // Кураторы курса
    public function curators()
    {
        return $this->belongsToMany(User::class, 'course_curator', 'course_id', 'user_id');
    }

    public function tariffs()
    {
        return $this->hasMany(Tariff::class);
    }
}