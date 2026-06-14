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
            //
            if (!Schema::hasColumn('vpn_servers', 'security')) {
                $table->string(column: 'security')->after('short_id');
            }
            if (!Schema::hasColumn('vpn_servers', 'flow')) {
                $table->string(column: 'flow')->after('short_id');
            }
            if (!Schema::hasColumn('vpn_servers', 'encryption')) {
                $table->string(column: 'encryption')->after('short_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vpn_servers', function (Blueprint $table) {
            //
            if (Schema::hasColumn('vpn_servers', 'security')) {
                $table->dropColumn('security');
            }
            if (Schema::hasColumn('vpn_servers', 'flow')) {
                $table->dropColumn('flow');
            }
            if (Schema::hasColumn('vpn_servers', 'encryption')) {
                $table->dropColumn('encryption');
            }
        });
    }
};
