<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Курсы
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade'); // Автор курса
            $table->string('title');
            $table->string('slug')->unique(); // ЧПУ ссылка
            $table->text('description')->nullable();
            $table->string('thumbnail_url')->nullable();
            $table->unsignedInteger('price')->default(0); // Цена в копейках
            
            // Даты потока
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            
            $table->boolean('is_published')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Модули (С поддержкой бесконечной вложенности)
        Schema::create('course_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            
            // Рекурсивная связь: parent_id ссылается на id этой же таблицы
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('course_modules')
                  ->onDelete('cascade');

            $table->string('title');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0); // Сортировка
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_modules');
        Schema::dropIfExists('courses');
    }
};