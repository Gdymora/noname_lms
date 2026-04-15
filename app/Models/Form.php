<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'schema' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    public function submissions()
    {
        return $this->hasMany(FormSubmission::class);
    }
}