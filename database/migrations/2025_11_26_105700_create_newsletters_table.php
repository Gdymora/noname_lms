<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletters', function (Blueprint $table) {
            $table->id();
            $table->string('subject'); // Тема письма
            $table->longText('content'); // HTML тело письма
            
            // Кому отправляем (храним настройки фильтра в JSON)
            // Например: { "course_id": [1, 2], "tariff_id": [5] }
            $table->jsonb('recipients_filter')->nullable(); 
            
            $table->dateTime('scheduled_at')->nullable(); // Когда отправить
            $table->dateTime('sent_at')->nullable(); // Когда реально ушло
            
            // Статус: draft (черновик), scheduled (ждет), processing (отправляется), sent (готово)
            $table->string('status')->default('draft');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletters');
    }
};