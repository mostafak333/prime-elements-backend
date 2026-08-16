<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('image')->nullable()->after('title_id');
        });

        $categories = DB::table('categories')
            ->join('images', 'categories.image_id', '=', 'images.id')
            ->whereNotNull('categories.image_id')
            ->select('categories.id', 'images.path')
            ->get();

        foreach ($categories as $category) {
            DB::table('categories')
                ->where('id', $category->id)
                ->update(['image' => $category->path]);
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['image_id']);
            $table->dropColumn('image_id');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('image_id')->nullable()->after('title_id')->constrained('images');
        });

        $categories = DB::table('categories')
            ->whereNotNull('image')
            ->get();

        foreach ($categories as $category) {
            $imageId = DB::table('images')
                ->where('path', $category->image)
                ->value('id');

            if ($imageId) {
                DB::table('categories')
                    ->where('id', $category->id)
                    ->update(['image_id' => $imageId]);
            }
        }

        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
