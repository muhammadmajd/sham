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
        if (!Schema::hasTable('user_subscriptions')) {
            Schema::create('user_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('plan_id')->nullable()->constrained('plans')->nullOnDelete();

                $table->string('subscription')->nullable(); // active, canceled, expired, trial, etc.
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ends_at')->nullable();

                $table->integer('price_cents')->default(0);
                $table->string('currency')->nullable();
                $table->bigInteger('traffic_limit')->default(0);

                $table->string('stripe_subscription_id')->nullable();
                $table->string('stripe_price_id')->nullable();

                $table->text('notes')->nullable();

                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
