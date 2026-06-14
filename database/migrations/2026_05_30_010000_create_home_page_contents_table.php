<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('home_page_contents')) {
            Schema::create('home_page_contents', function (Blueprint $table) {
                $table->id();
                $table->boolean('is_published')->default(true)->index();
                $table->string('title_en')->nullable();
                $table->string('title_ar')->nullable();
                $table->string('title_fa')->nullable();
                $table->string('title_ru')->nullable();
                $table->text('description_en')->nullable();
                $table->text('description_ar')->nullable();
                $table->text('description_fa')->nullable();
                $table->text('description_ru')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('home_page_contents');
    }
};
