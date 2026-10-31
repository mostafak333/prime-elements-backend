<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_ebook_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items')->cascadeOnDelete();
            $table->foreignId('ebook_access_code_id')->constrained('ebook_access_codes')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['order_item_id', 'ebook_access_code_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_ebook_codes');
    }
};
