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
            if (!Schema::hasColumn('vpn_servers', 'traffic_used')) {
                $table->string(column: 'traffic_used')->after('short_id');
            }
            if (!Schema::hasColumn('vpn_servers', 'traffic_limit')) {
                $table->string(column: 'traffic_limit')->after('short_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vpn_servers', function (Blueprint $table) {
           if (Schema::hasColumn('vpn_servers', 'traffic_used')) {
                $table->dropColumn('traffic_used');
            }
            if (Schema::hasColumn('vpn_servers', 'traffic_limit')) {
                $table->dropColumn('traffic_limit');
            }
        });
    }
};
