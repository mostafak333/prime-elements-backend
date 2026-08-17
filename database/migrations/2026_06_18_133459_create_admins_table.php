<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_super')->default(false);
            $table->string('avatar')->nullable();
            $table->string('phone')->nullable();
            $table->rememberToken();
            $table->string('invitation_token', 64)->nullable()->unique();
            $table->timestamp('invitation_token_expires_at')->nullable();
            $table->string('password_reset_token', 64)->nullable()->unique();
            $table->timestamp('password_reset_token_expires_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
