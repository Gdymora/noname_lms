<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Homework extends Model
{
    // Указываем таблицу явно, чтобы Laravel не искал 'homework'
    protected $table = 'homeworks'; 

    protected $guarded = ['id'];

    protected $casts = [
        'submission_fields' => 'array', // Настройки полей
        'is_required' => 'boolean',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function submissions()
    {
        return $this->hasMany(HomeworkSubmission::class);
    }
}