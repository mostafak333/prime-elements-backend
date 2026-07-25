<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country')->nullable()->after('avatar');
            $table->string('city')->nullable()->after('country');
            $table->string('street_address')->nullable()->after('city');
            $table->string('apartment')->nullable()->after('street_address');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['country', 'city', 'street_address', 'apartment']);
        });
    }
};
