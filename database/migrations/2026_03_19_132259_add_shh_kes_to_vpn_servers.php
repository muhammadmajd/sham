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
            if (!Schema::hasColumn('vpn_servers', 'ssh_bin')) {
                $table->string('ssh_bin')->default('ssh');
            }
            if (!Schema::hasColumn('vpn_servers', 'ssh_user')) {
                $table->string('ssh_user')->default('xrayadmin');
            }
            if (!Schema::hasColumn('vpn_servers', 'ssh_key')) {
                $table->string('ssh_key')->default('/home/xrayadmin/.ssh/authorized_keys/id_ed25519');
            }
            if (!Schema::hasColumn('vpn_servers', 'ssh_config_path')) {
                $table->string('ssh_config_path')->default('/usr/local/etc/xray/config.json');
            }
            if (!Schema::hasColumn('vpn_servers', 'xray_bin_path')) {
                $table->string('xray_bin_path')->default('/usr/local/bin/xray');
            }
            if (!Schema::hasColumn('vpn_servers', 'xray_stats_server')) {
                $table->string('xray_stats_server')->default('127.0.0.1:10085');
            }
            if (!Schema::hasColumn('vpn_servers', 'ssh_vless_flow')) {
                $table->string('ssh_vless_flow')->default('xtls-rprx-vision');
            }
            if (!Schema::hasColumn('vpn_servers', 'ssh_timeout')) {
                $table->integer('ssh_timeout')->default(60);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vpn_servers', function (Blueprint $table) {
            if (Schema::hasColumn('vpn_servers', 'ssh_bin')) {
                $table->dropColumn('ssh_bin');
            }
            if (Schema::hasColumn('vpn_servers', 'ssh_user')) {
                $table->dropColumn('ssh_user');
            }
            if (Schema::hasColumn('vpn_servers', 'ssh_key')) {
                $table->dropColumn('ssh_key');
            }
            if (Schema::hasColumn('vpn_servers', 'ssh_config_path')) {
                $table->dropColumn('ssh_config_path');
            }
            if (Schema::hasColumn('vpn_servers', 'xray_bin_path')) {
                $table->dropColumn('xray_bin_path');
            }
            if (Schema::hasColumn('vpn_servers', 'xray_stats_server')) {
                $table->dropColumn('xray_stats_server');
            }
            if (Schema::hasColumn('vpn_servers', 'ssh_vless_flow')) {
                $table->dropColumn('ssh_vless_flow');
            }
            if (Schema::hasColumn('vpn_servers', 'ssh_timeout')) {
                $table->dropColumn('ssh_timeout');
            }
        });
    }
};
