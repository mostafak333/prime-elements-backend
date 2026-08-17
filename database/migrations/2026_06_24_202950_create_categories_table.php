<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('categories');
            $table->foreignId('title_id')->nullable()->constrained('titles');
            $table->string('image')->nullable();
            $table->string('name_en');
            $table->string('name_ar');
            $table->string('slug')->nullable()->unique();
            $table->text('description_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('is_filter')->default(false);
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

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
