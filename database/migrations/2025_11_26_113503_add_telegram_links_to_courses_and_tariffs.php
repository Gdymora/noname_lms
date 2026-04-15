<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->string('telegram_channel_link')->nullable();
            $table->string('telegram_chat_link')->nullable();
        });

        Schema::table('tariffs', function (Blueprint $table) {
            $table->string('telegram_channel_link')->nullable();
            $table->string('telegram_chat_link')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['telegram_channel_link', 'telegram_chat_link']);
        });
        Schema::table('tariffs', function (Blueprint $table) {
            $table->dropColumn(['telegram_channel_link', 'telegram_chat_link']);
        });
    }
};
