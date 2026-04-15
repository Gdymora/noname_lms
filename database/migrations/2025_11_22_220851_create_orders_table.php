<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Покупатель
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()->constrained('users'); // Ответственный менеджер
            
            $table->string('status')->default('new'); // new, paid, cancelled
            $table->unsignedInteger('amount'); // Сумма
            
            $table->jsonb('history_log')->nullable(); // История изменений заказа
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};