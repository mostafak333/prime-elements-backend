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
        Schema::create('delivery_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_ar');
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('admins')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_methods');
    }
};
