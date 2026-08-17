<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->string('email_verification_token', 64)->nullable()->unique();
            $table->timestamp('email_verification_token_expires_at')->nullable();
            $table->string('password_reset_token', 64)->nullable()->unique();
            $table->timestamp('password_reset_token_expires_at')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('avatar')->nullable();
            $table->enum('status', ['active', 'blocked'])->default('active');
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('street_address')->nullable();
            $table->string('apartment')->nullable();
            $table->string('social_provider')->nullable();
            $table->string('social_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['social_provider', 'social_id']);
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
            $table->softDeletes();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
