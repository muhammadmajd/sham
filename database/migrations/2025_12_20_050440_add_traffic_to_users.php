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
            //
            if (!Schema::hasColumn('users', 'traffic_used')) {
                $table->bigInteger('traffic_used')->default(0); // in bytes
            }
            if (!Schema::hasColumn('users', 'traffic_limit')) {
                $table->bigInteger('traffic_limit')->default(10737418240); // 10 GB default
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
            if (Schema::hasColumn('users', 'traffic_used')) {
                $table->dropColumn('traffic_used');
            }
            if (Schema::hasColumn('users', 'traffic_limit')) {
                $table->dropColumn('traffic_limit');
            }
        });
    }
};
