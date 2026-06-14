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
            if (!Schema::hasColumn('vpn_servers', 'fingerprint')) {
                $table->string('fingerprint')->after('cert_domain');
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
            if (Schema::hasColumn('vpn_servers', 'fingerprint')) {
                $table->dropColumn('fingerprint');
            }
        });
    }
};
