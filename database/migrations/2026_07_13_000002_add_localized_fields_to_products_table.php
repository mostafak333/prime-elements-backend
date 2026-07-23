<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->string('title_en', 255)->nullable()->after('name_ar');
            $table->string('title_ar', 255)->nullable()->after('title_en');
            $table->text('description_en')->nullable()->after('title_ar');
            $table->text('description_ar')->nullable()->after('description_en');
        });
    }

    public function down(): void
    {
        Schema::table('product_details', function (Blueprint $table) {
            $table->dropColumn([
                'title_en',
                'title_ar',
                'description_en',
                'description_ar',
            ]);
        });
    }
};
