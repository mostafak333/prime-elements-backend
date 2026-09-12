<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->longText('terms_conditions')->nullable()->after('vat_enabled');
            $table->longText('privacy_policy')->nullable()->after('terms_conditions');
            $table->longText('return_exchange_policy')->nullable()->after('privacy_policy');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['terms_conditions', 'privacy_policy', 'return_exchange_policy']);
        });
    }
};
