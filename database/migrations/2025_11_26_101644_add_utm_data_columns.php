<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->jsonb('utm_data')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->jsonb('utm_data')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('utm_data');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('utm_data');
        });
    }
};