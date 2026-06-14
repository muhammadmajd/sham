<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('plans')) {
            Schema::create('plans', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique(); // e.g. 'free', 'pro_monthly'
                $table->string('name');
                $table->integer('price_cents')->default(0);
                $table->string('currency')->default('usd');
                $table->string('interval')->nullable(); // 'month','year' or null (one-off)
                $table->string('stripe_price_id')->nullable(); // map to Stripe Price
                $table->bigInteger('traffic_limit')->nullable(); // bytes, null = unlimited
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
