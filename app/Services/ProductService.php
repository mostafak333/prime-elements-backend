<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['images', 'detail'])
            ->paginate($perPage);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            // 1. Create product
            $product = Product::create([
                'category_id'       => $data['category_id'],
                'name_en'           => $data['name_en'],
                'name_ar'           => $data['name_ar'],
                'short_description' => $data['short_description'] ?? null,
                'price'             => $data['price'],
                'discount'          => $data['discount'] ?? 0,
                'stock'             => $data['stock'],
                'status'            => $data['status'] ?? true,
                'is_new_arrival'    => $data['is_new_arrival'] ?? false,
                'is_best_seller'    => $data['is_best_seller'] ?? false,
                'is_e_copy'         => $data['is_e_copy'] ?? false,
                'publisher'         => $data['publisher'] ?? null,
            ]);

            // 2. Save images (hasMany)
            if (!empty($data['images'])) {
                foreach ($data['images'] as $image) {
                    $product->images()->create([
                        'image_path' => $image,
                    ]);
                }
            }

            // 3. Save detail (ONE TO ONE)
            if (!empty($data['detail'])) {

                $product->detail()->create([
                    'name_en'           => $data['detail']['name_en'],
                    'name_ar'           => $data['detail']['name_ar'],
                    'description'       => $data['detail']['description'] ?? null,
                    'author'            => $data['detail']['author'] ?? null,
                    'publisher'         => $data['detail']['publisher'] ?? null,
                    'language'          => $data['detail']['language'] ?? null,
                    'pages'             => $data['detail']['pages'] ?? null,
                    'isbn'              => $data['detail']['isbn'] ?? null,
                    'format'            => $data['detail']['format'] ?? null,
                    'publication_date'  => $data['detail']['publication_date'] ?? null,
                    'is_active'         => $data['detail']['is_active'] ?? true,
                    'created_by'        => $data['created_by'] ?? null,
                    'updated_by'        => $data['updated_by'] ?? null,
                ]);
            }

            return $product->load(['images', 'detail']);
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {

            // 1. Update product core fields
            $product->update([
                'category_id'       => $data['category_id'] ?? $product->category_id,
                'name_en'           => $data['name_en'] ?? $product->name_en,
                'name_ar'           => $data['name_ar'] ?? $product->name_ar,
                'short_description' => $data['short_description'] ?? $product->short_description,
                'price'             => $data['price'] ?? $product->price,
                'discount'          => $data['discount'] ?? $product->discount,
                'stock'             => $data['stock'] ?? $product->stock,
                'status'            => $data['status'] ?? $product->status,
                'is_new_arrival'    => $data['is_new_arrival'] ?? $product->is_new_arrival,
                'is_best_seller'    => $data['is_best_seller'] ?? $product->is_best_seller,
                'is_e_copy'         => $data['is_e_copy'] ?? $product->is_e_copy,
                'publisher'         => $data['publisher'] ?? $product->publisher,
            ]);

            // 2. Replace images
            if (isset($data['images'])) {
                $product->images()->delete();

                foreach ($data['images'] as $image) {
                    $product->images()->create([
                        'image_path' => $image,
                    ]);
                }
            }

            // 3. Update or create detail (ONE TO ONE)
            if (isset($data['detail'])) {

                if ($product->detail) {
                    $product->detail->update([
                        'name_en'           => $data['detail']['name_en'],
                        'name_ar'           => $data['detail']['name_ar'],
                        'description'       => $data['detail']['description'] ?? null,
                        'author'            => $data['detail']['author'] ?? null,
                        'publisher'         => $data['detail']['publisher'] ?? null,
                        'language'          => $data['detail']['language'] ?? null,
                        'pages'             => $data['detail']['pages'] ?? null,
                        'isbn'              => $data['detail']['isbn'] ?? null,
                        'format'            => $data['detail']['format'] ?? null,
                        'publication_date'  => $data['detail']['publication_date'] ?? null,
                        'is_active'         => $data['detail']['is_active'] ?? true,
                        'updated_by'        => $data['updated_by'] ?? null,
                    ]);
                } else {
                    $product->detail()->create($data['detail']);
                }
            }

            return $product->load(['images', 'detail']);
        });
    }

    public function find(int $id): ?Product
    {
        return Product::with(['images', 'detail'])->find($id);
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product) {

            $product->images()->delete();
            $product->detail()?->delete();

            $product->delete();
        });
    }
}
