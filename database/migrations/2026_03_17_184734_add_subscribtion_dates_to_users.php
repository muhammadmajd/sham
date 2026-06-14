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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'subscription_started_at')) {
                $table->timestamp('subscription_started_at')->nullable();
            }
            if (!Schema::hasColumn('users', 'subscription_ends_at')) {
                $table->timestamp('subscription_ends_at')->nullable()->after('subscription_started_at');
            }
            if (!Schema::hasColumn('users', 'subscription_renewed_at')) {
                $table->timestamp('subscription_renewed_at')->nullable()->after('subscription_ends_at');
            }
            if (!Schema::hasColumn('users', 'subscription_canceled_at')) {
                $table->timestamp('subscription_canceled_at')->nullable()->after('subscription_renewed_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'subscription_started_at')) {
                $table->dropColumn('subscription_started_at');
            }
            if (Schema::hasColumn('users', 'subscription_ends_at')) {
                $table->dropColumn('subscription_ends_at');
            }
            if (Schema::hasColumn('users', 'subscription_renewed_at')) {
                $table->dropColumn('subscription_renewed_at');
            }
            if (Schema::hasColumn('users', 'subscription_canceled_at')) {
                $table->dropColumn('subscription_canceled_at');
            }
        });
    }
};
