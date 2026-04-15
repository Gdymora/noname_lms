<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            // Группа настроек: 'smtp', 'general', 'ui'
            $table->string('group')->index();
            // Ключ: 'mail_host', 'mail_port', 'logo_url'
            $table->string('key')->unique();
            // Значение может быть строкой, булевым или JSON
            $table->jsonb('payload')->nullable();
            $table->boolean('is_locked')->default(false); // Защита от случайного изменения важных настроек
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};