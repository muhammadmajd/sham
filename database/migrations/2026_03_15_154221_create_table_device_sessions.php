<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('device_sessions')) {
            Schema::create('device_sessions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('device_id')
                    ->nullable()
                    ->constrained('devices')
                    ->nullOnDelete();
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('device_sessions');
    }
};
