<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('app_versions')) {
            Schema::create('app_versions', function (Blueprint $table) {
                $table->id();
                $table->string('platform')->index();
                $table->string('version');
                $table->string('status')->default('active')->index();
                $table->boolean('is_visible')->default(true)->index();
                $table->string('file_path')->nullable();
                $table->string('file_name')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->text('notes_en')->nullable();
                $table->text('notes_ar')->nullable();
                $table->text('notes_fa')->nullable();
                $table->text('notes_ru')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('app_versions');
    }
};
