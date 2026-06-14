<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add indexes for frequently queried columns to improve performance.
     * Safely skips indexes if columns don't exist.
     */
    public function up(): void
    {
        // Users table indexes
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'uuid') && !Schema::hasIndex('users', 'users_uuid_index')) {
                $table->index('uuid');
            }

            if (Schema::hasColumn('users', 'email') && !Schema::hasIndex('users', 'users_email_index')) {
                $table->index('email');
            }

            if (Schema::hasColumn('users', 'subscription_ends_at') && !Schema::hasIndex('users', 'users_subscription_ends_at_index')) {
                $table->index('subscription_ends_at');
            }

            if (Schema::hasColumn('users', 'plan_id') && !Schema::hasIndex('users', 'users_plan_id_index')) {
                $table->index('plan_id');
            }

            if (Schema::hasColumn('users', 'active') && Schema::hasColumn('users', 'subscription') && !Schema::hasIndex('users', 'users_active_subscription_index')) {
                $table->index(['active', 'subscription']);
            }
        });

        // Devices table indexes
        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasColumn('devices', 'device_uid') && !Schema::hasIndex('devices', 'devices_device_uid_index')) {
                $table->index('device_uid');
            }

            if (Schema::hasColumn('devices', 'user_id') && !Schema::hasIndex('devices', 'devices_user_id_index')) {
                $table->index('user_id');
            }

            if (Schema::hasColumn('devices', 'user_id') && Schema::hasColumn('devices', 'device_uid') && !Schema::hasIndex('devices', 'devices_user_id_device_uid_unique')) {
                $table->unique(['user_id', 'device_uid'], 'devices_user_id_device_uid_unique');
            }
        });

        // VPN servers table indexes
        Schema::table('vpn_servers', function (Blueprint $table) {
            if (Schema::hasColumn('vpn_servers', 'host') && !Schema::hasIndex('vpn_servers', 'vpn_servers_host_index')) {
                $table->index('host');
            }

            // Only add composite index if both columns exist
            if (Schema::hasColumn('vpn_servers', 'available') && Schema::hasColumn('vpn_servers', 'is_active') && !Schema::hasIndex('vpn_servers', 'vpn_servers_available_is_active_index')) {
                $table->index(['available', 'is_active']);
            } elseif (Schema::hasColumn('vpn_servers', 'available') && !Schema::hasIndex('vpn_servers', 'vpn_servers_available_index')) {
                // Fallback: just index available if is_active doesn't exist
                $table->index('available');
            }
        });

        // Refresh tokens table indexes
        Schema::table('refresh_tokens', function (Blueprint $table) {
            if (Schema::hasColumn('refresh_tokens', 'token') && !Schema::hasIndex('refresh_tokens', 'refresh_tokens_token_index')) {
                $table->index('token');
            }

            if (Schema::hasColumn('refresh_tokens', 'user_id') && !Schema::hasIndex('refresh_tokens', 'refresh_tokens_user_id_index')) {
                $table->index('user_id');
            }

            if (Schema::hasColumn('refresh_tokens', 'expires_at') && !Schema::hasIndex('refresh_tokens', 'refresh_tokens_expires_at_index')) {
                $table->index('expires_at');
            }
        });

        // User subscriptions table indexes
        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('user_subscriptions', 'user_id') && Schema::hasColumn('user_subscriptions', 'status') && !Schema::hasIndex('user_subscriptions', 'user_subscriptions_user_id_status_index')) {
                $table->index(['user_id', 'status']);
            }
        });

        // Audit logs table indexes
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'auditable_type') && Schema::hasColumn('audit_logs', 'auditable_id') && !Schema::hasIndex('audit_logs', 'audit_logs_auditable_index')) {
                $table->index(['auditable_type', 'auditable_id']);
            }

            if (Schema::hasColumn('audit_logs', 'event') && Schema::hasColumn('audit_logs', 'type') && !Schema::hasIndex('audit_logs', 'audit_logs_event_type_index')) {
                $table->index(['event', 'type']);
            }

            if (Schema::hasColumn('audit_logs', 'created_at') && !Schema::hasIndex('audit_logs', 'audit_logs_created_at_index')) {
                $table->index('created_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasIndex('users', 'users_uuid_index')) {
                $table->dropIndex(['uuid']);
            }
            if (Schema::hasIndex('users', 'users_email_index')) {
                $table->dropIndex(['email']);
            }
            if (Schema::hasIndex('users', 'users_subscription_ends_at_index')) {
                $table->dropIndex(['subscription_ends_at']);
            }
            if (Schema::hasIndex('users', 'users_plan_id_index')) {
                $table->dropIndex(['plan_id']);
            }
            if (Schema::hasIndex('users', 'users_active_subscription_index')) {
                $table->dropIndex(['active', 'subscription']);
            }
        });

        Schema::table('devices', function (Blueprint $table) {
            if (Schema::hasIndex('devices', 'devices_device_uid_index')) {
                $table->dropIndex(['device_uid']);
            }
            if (Schema::hasIndex('devices', 'devices_user_id_index')) {
                $table->dropIndex(['user_id']);
            }
            if (Schema::hasIndex('devices', 'devices_user_id_device_uid_unique')) {
                $table->dropUnique(['user_id', 'device_uid']);
            }
        });

        Schema::table('vpn_servers', function (Blueprint $table) {
            if (Schema::hasIndex('vpn_servers', 'vpn_servers_host_index')) {
                $table->dropIndex(['host']);
            }
            if (Schema::hasIndex('vpn_servers', 'vpn_servers_available_is_active_index')) {
                $table->dropIndex(['available', 'is_active']);
            }
            if (Schema::hasIndex('vpn_servers', 'vpn_servers_available_index')) {
                $table->dropIndex(['available']);
            }
        });

        Schema::table('refresh_tokens', function (Blueprint $table) {
            if (Schema::hasIndex('refresh_tokens', 'refresh_tokens_token_index')) {
                $table->dropIndex(['token']);
            }
            if (Schema::hasIndex('refresh_tokens', 'refresh_tokens_user_id_index')) {
                $table->dropIndex(['user_id']);
            }
            if (Schema::hasIndex('refresh_tokens', 'refresh_tokens_expires_at_index')) {
                $table->dropIndex(['expires_at']);
            }
        });

        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (Schema::hasIndex('user_subscriptions', 'user_subscriptions_user_id_status_index')) {
                $table->dropIndex(['user_id', 'status']);
            }
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasIndex('audit_logs', 'audit_logs_auditable_index')) {
                $table->dropIndex(['auditable_type', 'auditable_id']);
            }
            if (Schema::hasIndex('audit_logs', 'audit_logs_event_type_index')) {
                $table->dropIndex(['event', 'type']);
            }
            if (Schema::hasIndex('audit_logs', 'audit_logs_created_at_index')) {
                $table->dropIndex(['created_at']);
            }
        });
    }
};
