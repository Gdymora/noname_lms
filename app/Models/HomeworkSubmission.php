<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeworkSubmission extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'content' => 'array', // Ответ студента
        'grade_percent' => 'decimal:2',
        'reviewed_at' => 'datetime',
    ];

    public function homework()
    {
        return $this->belongsTo(Homework::class, 'homework_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function curator()
    {
        return $this->belongsTo(User::class, 'curator_id');
    }
}