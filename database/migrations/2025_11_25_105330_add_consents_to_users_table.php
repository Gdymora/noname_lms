<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('accepted_offer_at')->nullable(); // Оферта
            $table->timestamp('accepted_policy_at')->nullable(); // Политика
            $table->timestamp('accepted_marketing_at')->nullable(); // Реклама
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['accepted_offer_at', 'accepted_policy_at', 'accepted_marketing_at']);
        });
    }
};
