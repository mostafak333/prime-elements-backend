<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->dropColumn(['name_en', 'name_ar', 'format', 'description']);
        });
    }

    public function down(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->string('name_en')->after('publication_date');
            $table->string('name_ar')->after('name_en');
            $table->string('format')->nullable()->after('isbn');
            $table->text('description')->nullable()->after('format');
        });
    }
};
