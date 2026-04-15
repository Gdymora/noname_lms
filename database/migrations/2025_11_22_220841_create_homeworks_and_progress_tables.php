<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Само задание
        Schema::create('homeworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->text('description'); // Текст задания
            
            // Конфигурация полей для ответа (например: нужен файл или ссылка)
            $table->jsonb('submission_fields')->nullable();
            
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        // 2. Ответ студента
        Schema::create('homework_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('homework_id')->constrained('homeworks')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('curator_id')->nullable()->constrained('users'); // Кто проверил
            
            // Ответ студента (JSON)
            $table->jsonb('content'); 
            
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->text('curator_comment')->nullable();
            
            // Оценка (Decimal для точного расчета рейтинга)
            $table->decimal('grade_percent', 5, 2)->nullable(); 
            
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 3. Таблица прогресса (связь Студент <-> Урок)
        Schema::create('lesson_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            
            $table->timestamp('unlocked_at')->nullable(); // Доступ открыт
            $table->timestamp('completed_at')->nullable(); // Урок пройден
            
            $table->unique(['user_id', 'lesson_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_user');
        Schema::dropIfExists('homework_submissions');
        Schema::dropIfExists('homeworks');
    }
};