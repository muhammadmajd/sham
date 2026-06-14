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
        Schema::table('vpn_servers', function (Blueprint $table) {
            if (!Schema::hasColumn('vpn_servers', 'server_type')) {
                $table->string('server_Payment_type')->default('paid'); // 'free' or 'paid'
            }
            if (!Schema::hasColumn('vpn_servers', 'public')) {
                $table->boolean('public')->default(true);
            }
            if (!Schema::hasColumn('vpn_servers', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vpn_servers', function (Blueprint $table) {
            if (Schema::hasColumn('vpn_servers', 'server_Payment_type')) {
                $table->dropColumn('server_Payment_type');
            }
            if (Schema::hasColumn('vpn_servers', 'public')) {
                $table->dropColumn('public');
            }
            if (Schema::hasColumn('vpn_servers', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
