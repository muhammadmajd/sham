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
        if (!Schema::hasTable('devices')) {
            Schema::create('devices', function (Blueprint $table) {
                $table->id();
                $table->string('device_uid')->unique();
                $table->string('platform')->nullable();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

                $table->uuid('xray_client_uuid')->nullable();
                $table->string('xray_email')->nullable()->unique();

                $table->unsignedBigInteger('download_bytes')->default(0);
                $table->unsignedBigInteger('upload_bytes')->default(0);

                $table->unsignedBigInteger('speed_limit_bps')->nullable();
                $table->unsignedBigInteger('traffic_limit_bytes')->nullable();

                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
