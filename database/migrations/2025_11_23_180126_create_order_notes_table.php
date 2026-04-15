<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained(); // Кто написал
            $table->text('content'); // Текст заметки
            $table->boolean('is_private')->default(true); // На будущее (вдруг захотим писать клиенту)
            $table->timestamps();
        });

        // Удаляем старое поле notes из orders, оно больше не нужно
        if (Schema::hasColumn('orders', 'notes')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('notes');
            });
        }
    }
};
