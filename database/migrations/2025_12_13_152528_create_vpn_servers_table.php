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
        if (!Schema::hasTable('vpn_servers')) {
            Schema::create('vpn_servers', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('host');
                $table->integer('port');
                $table->string('type')->default('vless');
                $table->string('cert_domain')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vpn_servers');
    }
};
