<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentBlock extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'content' => 'array', // Автоматически преобразует JSON в массив
        'is_visible' => 'boolean',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function testResults()
{
    return $this->hasMany(TestResult::class, 'content_block_id');
}
}