<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'user_answers' => 'array',
        'is_passed' => 'boolean',
    ];

    public function block()
    {
        return $this->belongsTo(ContentBlock::class, 'content_block_id');
    }
}