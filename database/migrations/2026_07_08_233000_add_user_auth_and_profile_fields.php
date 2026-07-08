<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email_verification_token', 64)->nullable()->unique()->after('remember_token');
            $table->timestamp('email_verification_token_expires_at')->nullable()->after('email_verification_token');
            $table->string('password_reset_token', 64)->nullable()->unique()->after('email_verification_token_expires_at');
            $table->timestamp('password_reset_token_expires_at')->nullable()->after('password_reset_token');
            $table->string('phone', 50)->nullable()->after('password_reset_token_expires_at');
            $table->string('avatar')->nullable()->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_verification_token',
                'email_verification_token_expires_at',
                'password_reset_token',
                'password_reset_token_expires_at',
                'phone',
                'avatar',
            ]);
        });
    }
};
