<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return Product::with(['images', 'detail'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get all products for user with filters
     */
    public function getAllForUser(array $filters = []): LengthAwarePaginator
    {
        $query = Product::with(['images', 'detail'])
            ->active() // Only active products
            ->orderBy('name_en');

        // Apply filters
        $query = $this->applyFilters($query, $filters);

        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage);
    }

    /**
     * Apply all filters to the query
     */
    private function applyFilters(Builder $query, array $filters): Builder
    {
        // Category filter
        if (!empty($filters['categories'])) {
            $query->whereIn('category_id', $filters['categories']);
        }

        // Format filter (is_e_copy)
        if (!empty($filters['formats'])) {
            $query->where(function ($q) use ($filters) {
                if (in_array('printed', $filters['formats'])) {
                    $q->orWhere('is_e_copy', false);
                }
                if (in_array('ebook', $filters['formats'])) {
                    $q->orWhere('is_e_copy', true);
                }
                if (in_array('both', $filters['formats'])) {
                    // "Both" means show all products (no filter)
                    // or you can show products that have both formats
                    // For simplicity, we'll show all
                }
            });
        }

        // Price range filter
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Availability filter
        if (!empty($filters['availability'])) {
            if ($filters['availability'] === 'in_stock') {
                $query->where('stock', '>', 0);
            } elseif ($filters['availability'] === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            }
        }

        // Search filter
        if (!empty($filters['search'])) {
            $searchTerm = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name_en', 'LIKE', $searchTerm)
                    ->orWhere('name_ar', 'LIKE', $searchTerm)
                    ->orWhere('short_description', 'LIKE', $searchTerm)
                    ->orWhereHas('detail', function ($detailQuery) use ($searchTerm) {
                        $detailQuery->where('book_title', 'LIKE', $searchTerm)
                            ->orWhere('author', 'LIKE', $searchTerm)
                            ->orWhere('description', 'LIKE', $searchTerm);
                    });
            });
        }

        // Sorting
        if (!empty($filters['sort_by'])) {
            $query = $this->applySorting($query, $filters['sort_by']);
        }

        return $query;
    }

    /**
     * Apply sorting to the query
     */
    private function applySorting(Builder $query, string $sortBy): Builder
    {
        switch ($sortBy) {
            case 'price_asc':
                return $query->orderBy('price', 'asc');
            case 'price_desc':
                return $query->orderBy('price', 'desc');
            case 'name_asc':
                return $query->orderBy('name_en', 'asc');
            case 'name_desc':
                return $query->orderBy('name_en', 'desc');
            case 'newest':
                return $query->orderBy('created_at', 'desc');
            case 'best_seller':
                return $query->orderBy('is_best_seller', 'desc');
            default:
                return $query->orderBy('name_en', 'asc');
        }
    }

    /**
     * Get filter options for the UI (categories, formats, etc.)
     */
    public function getFilterOptions(): array
    {
        // Get all categories with product counts
        $categories = \App\Models\Category::withCount('products')
            ->whereHas('products', function ($query) {
                $query->active();
            })
            ->orderBy('name_en')
            ->get()
            ->map(fn($category) => [
                'id' => $category->id,
                'name_en' => $category->name_en,
                'name_ar' => $category->name_ar,
                'product_count' => $category->products_count,
            ]);

        // Get product counts for availability
        $inStockCount = Product::active()->where('stock', '>', 0)->count();
        $outOfStockCount = Product::active()->where('stock', '<=', 0)->count();

        // Get price range
        $minPrice = Product::active()->min('price') ?? 0;
        $maxPrice = Product::active()->max('price') ?? 100;

        return [
            'categories' => $categories,
            'formats' => [
                ['value' => 'printed', 'label_en' => 'Printed Books', 'label_ar' => 'الكتب المطبوعة'],
                ['value' => 'ebook', 'label_en' => 'E-Books', 'label_ar' => 'الكتب الإلكترونية'],
                ['value' => 'both', 'label_en' => 'Both', 'label_ar' => 'كلاهما'],
            ],
            'price_range' => [
                'min' => floor($minPrice),
                'max' => ceil($maxPrice),
            ],
            'availability' => [
                'in_stock' => [
                    'count' => $inStockCount,
                    'label_en' => 'In Stock',
                    'label_ar' => 'متوفر',
                ],
                'out_of_stock' => [
                    'count' => $outOfStockCount,
                    'label_en' => 'Out of Stock',
                    'label_ar' => 'غير متوفر',
                ],
            ],
            'sort_options' => [
                ['value' => 'name_asc', 'label_en' => 'Name A-Z', 'label_ar' => 'الاسم أ-ي'],
                ['value' => 'name_desc', 'label_en' => 'Name Z-A', 'label_ar' => 'الاسم ي-أ'],
                ['value' => 'price_asc', 'label_en' => 'Price: Low to High', 'label_ar' => 'السعر: من الأقل للأعلى'],
                ['value' => 'price_desc', 'label_en' => 'Price: High to Low', 'label_ar' => 'السعر: من الأعلى للأقل'],
                ['value' => 'newest', 'label_en' => 'Newest First', 'label_ar' => 'الأحدث أولاً'],
                ['value' => 'best_seller', 'label_en' => 'Best Sellers', 'label_ar' => 'الأكثر مبيعاً'],
            ],
        ];
    }


    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $adminId = auth()->guard('api-admin')->id() ?? null;

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
                'created_by'        =>  $adminId,
                'updated_by'        =>  $adminId,
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
                ]);
            }

            return $product->load(['images', 'detail']);
        });
    }

    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $adminId = auth()->guard('api-admin')->id() ?? null;

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
                'updated_by'        => $adminId,
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
