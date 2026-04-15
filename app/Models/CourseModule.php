<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourseModule extends Model
{
    protected $guarded = ['id'];

    // === СВЯЗИ ===

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Родительский модуль (если есть)
    public function parent()
    {
        return $this->belongsTo(CourseModule::class, 'parent_id');
    }

    // Дочерние модули (вложенные папки)
    public function children()
    {
        return $this->hasMany(CourseModule::class, 'parent_id')->orderBy('sort_order');
    }

    // Уроки внутри этого модуля
    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'module_id')->orderBy('sort_order');
    }

    public function tariffs()
    {
        return $this->belongsToMany(Tariff::class, 'module_tariff');
    }
}