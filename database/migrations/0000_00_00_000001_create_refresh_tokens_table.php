<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRefreshTokensTable extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('refresh_tokens')) {
            Schema::create('refresh_tokens', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('token', 512);
                $table->boolean('revoked')->default(false);
                $table->timestamp('expires_at');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('refresh_tokens');
    }
}
