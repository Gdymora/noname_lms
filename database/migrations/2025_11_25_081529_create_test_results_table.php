<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Мы ссылаемся на блок контента, так как тест - это блок
            $table->foreignId('content_block_id')->constrained()->cascadeOnDelete(); 
            
            $table->unsignedInteger('score_percent'); // Сколько набрал (0-100)
            $table->boolean('is_passed'); // Сдал или нет
            $table->jsonb('user_answers')->nullable(); // История ответов (для разбора ошибок)
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_results');
    }
};