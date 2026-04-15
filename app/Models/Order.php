<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'history_log' => 'array',
        'paid_at' => 'datetime',
        'utm_data' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function orderNotes()
    {
        return $this->hasMany(OrderNote::class)->orderBy('created_at', 'desc');
    }

    public function tariff()
    {
        return $this->belongsTo(Tariff::class);
    }
}