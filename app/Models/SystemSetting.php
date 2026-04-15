<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'payload' => 'array', // JSON будет автоматически массивом
        'is_locked' => 'boolean',
    ];
}