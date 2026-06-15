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
        Schema::table('devices', function (Blueprint $table) {
            if (!Schema::hasColumn('devices', 'device_uid_source')) {
                $table->string('device_uid_source', 50)->nullable();
            }
            if (!Schema::hasColumn('devices', 'last_ip')) {
                $table->string('last_ip', 45)->nullable();
            }
            if (!Schema::hasColumn('devices', 'last_user_agent')) {
                $table->string('last_user_agent')->nullable();
            }
            if (!Schema::hasColumn('devices', 'first_seen_at')) {
                $table->timestamp('first_seen_at')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasColumn('devices', 'device_uid_source')) {
                $table->dropColumn('device_uid_source');
            }
            if (Schema::hasColumn('devices', 'last_ip')) {
                $table->dropColumn('last_ip');
            }
            if (Schema::hasColumn('devices', 'last_user_agent')) {
                $table->dropColumn('last_user_agent');
            }
            if (Schema::hasColumn('devices', 'first_seen_at')) {
                $table->dropColumn('first_seen_at');
            }
        });
    }
};
