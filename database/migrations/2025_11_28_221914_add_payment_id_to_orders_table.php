<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // ID платежа во внешней системе (ЮКасса, Stripe и т.д.)
            $table->string('payment_id')->nullable()->index(); 
            // Метод оплаты (tilda, yookassa, manual)
            $table->string('payment_method')->default('manual'); 
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_id', 'payment_method']);
        });
    }
};
