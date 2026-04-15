<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Newsletter extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'recipients_filter' => 'array', // JSON в массив
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];
}