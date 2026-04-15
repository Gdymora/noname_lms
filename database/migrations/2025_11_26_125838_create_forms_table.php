<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('forms', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->string('slug')->unique();

        // Структура формы (какие поля, типы, обязательность)
        $table->jsonb('schema'); 

        // Настройки (Текст кнопки, сообщение успеха)
        $table->jsonb('settings')->nullable(); 

        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });

    Schema::create('form_submissions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('form_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

        // Ответы пользователя
        $table->jsonb('data');
        // UTM метки
        $table->jsonb('utm_data')->nullable();

        $table->timestamps();
    });
}
};
