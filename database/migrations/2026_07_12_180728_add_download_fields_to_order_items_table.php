<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('download_token', 64)->nullable()->unique()->after('discount');
            $table->string('download_url')->nullable()->after('download_token');
            $table->timestamp('download_expire_at')->nullable()->after('download_url');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['download_token', 'download_url', 'download_expire_at']);
        });
    }
};
