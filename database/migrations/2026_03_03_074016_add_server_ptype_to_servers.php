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
            if (!Schema::hasColumn('vpn_servers', 'ptype')) {
                $table->string(column: 'ptype')->after('short_id');
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
            if (Schema::hasColumn('vpn_servers', 'ptype')) {
                $table->dropColumn('ptype');
            }
        });
    }
};
