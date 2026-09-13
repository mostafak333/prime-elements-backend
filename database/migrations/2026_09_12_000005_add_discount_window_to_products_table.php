<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('discount_start_at')->nullable()->after('discount');
            $table->timestamp('discount_end_at')->nullable()->after('discount_start_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['discount_start_at', 'discount_end_at']);
        });
    }
};
