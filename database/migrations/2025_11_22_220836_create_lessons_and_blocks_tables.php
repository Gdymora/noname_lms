<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Уроки
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained('course_modules')->onDelete('cascade');
            
            $table->string('title');
            $table->string('slug');
            
            $table->boolean('is_stop_lesson')->default(false); // Стоп-урок (не пускать без ДЗ)
            $table->integer('duration_minutes')->default(0);
            $table->integer('sort_order')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Контентные блоки (Лонгрид: текст, видео, файлы)
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            
            // Тип: text, video, image, file, code
            $table->string('type')->index();
            
            // Содержимое блока в JSON (гибкость NoSQL внутри SQL)
            $table->jsonb('content'); 
            
            $table->integer('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
        Schema::dropIfExists('lessons');
    }
};