<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Таблица Тарифов
        Schema::create('tariffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Название (Базовый, VIP)
            $table->unsignedInteger('price')->default(0); // Цена тарифа
            $table->timestamps();
        });

        // 2. Связь Модуль <-> Тарифы (Many-to-Many)
        Schema::create('module_tariff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_module_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tariff_id')->constrained()->cascadeOnDelete();
        });

        // 3. Связь Урок <-> Тарифы (Many-to-Many)
        Schema::create('lesson_tariff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tariff_id')->constrained()->cascadeOnDelete();
        });

        // 4. Обновляем Заказы: добавляем tariff_id
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('tariff_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['tariff_id']);
            $table->dropColumn('tariff_id');
        });
        Schema::dropIfExists('lesson_tariff');
        Schema::dropIfExists('module_tariff');
        Schema::dropIfExists('tariffs');
    }
};