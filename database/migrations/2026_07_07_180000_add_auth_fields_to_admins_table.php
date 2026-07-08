<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('invitation_token', 64)->nullable()->unique()->after('remember_token');
            $table->timestamp('invitation_token_expires_at')->nullable()->after('invitation_token');
            $table->string('password_reset_token', 64)->nullable()->unique()->after('invitation_token_expires_at');
            $table->timestamp('password_reset_token_expires_at')->nullable()->after('password_reset_token');
            $table->boolean('is_active')->default(false)->after('password_reset_token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn([
                'invitation_token',
                'invitation_token_expires_at',
                'password_reset_token',
                'password_reset_token_expires_at',
                'is_active',
            ]);
        });
    }
};
