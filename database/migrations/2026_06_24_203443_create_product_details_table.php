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
        Schema::create('product_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');

            $table->text('description')->nullable();

            // Product Details (from the image)
            $table->string('book_title')->nullable();
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->string('language')->nullable();
            $table->integer('pages')->nullable();
            $table->string('isbn')->nullable();
            $table->string('format')->nullable();
            $table->date('publication_date')->nullable();

            // Additional fields
            $table->string('name_en');
            $table->string('name_ar');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_details');
    }
};
