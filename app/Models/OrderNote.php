<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderNote extends Model
{
    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function orderNotes()
    {
        // Связь "Один ко многим": У заказа много заметок
        // Сортируем: новые сверху
        return $this->hasMany(OrderNote::class)->orderBy('created_at', 'desc');
    }
}
