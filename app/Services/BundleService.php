<?php

namespace App\Services;

use App\Models\Bundle;
use App\Models\BundleItem;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BundleService
{
    public function __construct(
        protected CartService $cartService,
        protected EbookAccessCodeService $ebookAccessCodeService
    ) {}

    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Bundle::with(['items.product.images', 'items.product.detail'])
            ->withCount('items')
            ->latest();

        if (array_key_exists('status', $filters) && $filters['status'] !== null) {
            $query->where('status', (bool) $filters['status']);
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name_en', 'like', "%{$search}%")
                    ->orWhere('name_ar', 'like', "%{$search}%")
                    ->orWhere('token', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Bundle
    {
        $this->validateItemsAvailability($data['items']);

        return DB::transaction(function () use ($data) {
            $adminId = auth()->guard('api-admin')->id();

            $bundle = Bundle::create([
                'name_en' => $data['name_en'],
                'name_ar' => $data['name_ar'],
                'description_en' => $data['description_en'] ?? null,
                'description_ar' => $data['description_ar'] ?? null,
                'token' => $this->generateToken(),
                'status' => $data['status'] ?? true,
                'created_by' => $adminId,
                'updated_by' => $adminId,
            ]);

            $this->syncItems($bundle, $data['items'], $adminId);

            return $this->loadBundle($bundle);
        });
    }

    public function update(Bundle $bundle, array $data): Bundle
    {
        if (array_key_exists('items', $data)) {
            $this->validateItemsAvailability($data['items']);
        }

        return DB::transaction(function () use ($bundle, $data) {
            $adminId = auth()->guard('api-admin')->id();
            $attributes = [];

            foreach (['name_en', 'name_ar', 'description_en', 'description_ar', 'status'] as $field) {
                if (array_key_exists($field, $data)) {
                    $attributes[$field] = $data[$field];
                }
            }

            $attributes['updated_by'] = $adminId;

            // The token is intentionally never changed so existing links keep working.
            $bundle->update($attributes);

            if (array_key_exists('items', $data)) {
                $this->syncItems($bundle, $data['items'], $adminId);
            }

            return $this->loadBundle($bundle);
        });
    }

    public function delete(Bundle $bundle): void
    {
        DB::transaction(function () use ($bundle) {
            $bundle->items()->delete();
            $bundle->delete();
        });
    }

    public function updateStatus(Bundle $bundle): Bundle
    {
        return $this->update($bundle, ['status' => ! $bundle->status]);
    }

    public function findByToken(string $token): ?Bundle
    {
        return Bundle::with(['items.product.images', 'items.product.detail'])
            ->where('token', $token)
            ->first();
    }

    /**
     * Add every product in the bundle to the authenticated user's cart.
     *
     * Availability is validated for the whole bundle before anything is written,
     * and the writes themselves are wrapped in a transaction so a failure never
     * leaves a partially applied bundle.
     *
     * @return array{bundle: Bundle, items: Collection, summary: array<string, int|float>}
     */
    public function addToCart(string $token): array
    {
        $userId = auth()->guard('api-user')->id();

        return DB::transaction(function () use ($token, $userId) {
            $bundle = Bundle::with('items.product')
                ->where('token', $token)
                ->first();

            if (! $bundle || ! $bundle->status) {
                throw ValidationException::withMessages([
                    'bundle' => [__('messages.bundle.not_found')],
                ]);
            }

            if ($bundle->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'bundle' => [__('messages.bundle.empty')],
                ]);
            }

            $this->validateAvailability($bundle->items, $userId);

            foreach ($bundle->items as $item) {
                $this->cartService->add([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                ]);
            }

            $cartItems = $this->cartService->getUserCart();

            return [
                'bundle' => $this->loadBundle($bundle),
                'items' => $cartItems,
                'summary' => $this->cartService->getCartSummary($cartItems),
            ];
        });
    }

    /**
     * Ensure every bundle item can currently be fulfilled from stock or unused
     * e-book access codes. This blocks admins from saving bundles that cannot
     * be added to a cart right away.
     */
    private function validateItemsAvailability(array $items): void
    {
        foreach ($items as $index => $row) {
            $product = Product::find($row['product_id']);

            if (! $product) {
                throw ValidationException::withMessages([
                    "items.{$index}.product_id" => [__('messages.product.not_found')],
                ]);
            }

            $quantity = (int) $row['quantity'];

            if ($product->is_e_copy) {
                $available = $this->ebookAccessCodeService->getAvailableCount($product);

                if ($quantity > $available) {
                    throw ValidationException::withMessages([
                        "items.{$index}.quantity" => [__('messages.bundle.insufficient_codes', [
                            'product' => $product->name_en,
                        ])],
                    ]);
                }

                continue;
            }

            if ($quantity > $product->stock) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => [__('messages.bundle.insufficient_stock', [
                        'product' => $product->name_en,
                        'available' => $product->stock,
                        'requested' => $quantity,
                    ])],
                ]);
            }
        }
    }

    private function validateAvailability(Collection $items, int $userId): void
    {
        foreach ($items as $item) {
            $product = $item->product;

            if (! $product || ! $product->status) {
                throw ValidationException::withMessages([
                    'bundle' => [__('messages.bundle.product_unavailable', [
                        'product' => $product?->name_en ?? '',
                    ])],
                ]);
            }

            $existingQuantity = CartItem::where('user_id', $userId)
                ->where('product_id', $product->id)
                ->sum('quantity');
            $requestedQuantity = $existingQuantity + $item->quantity;

            if ($product->is_e_copy) {
                $available = $this->ebookAccessCodeService->getAvailableCount($product);

                if ($requestedQuantity > $available) {
                    throw ValidationException::withMessages([
                        'bundle' => [__('messages.bundle.insufficient_codes', [
                            'product' => $product->name_en,
                        ])],
                    ]);
                }

                continue;
            }

            if ($product->stock < $requestedQuantity) {
                throw ValidationException::withMessages([
                    'bundle' => [__('messages.bundle.insufficient_stock', [
                        'product' => $product->name_en,
                        'available' => $product->stock,
                        'requested' => $requestedQuantity,
                    ])],
                ]);
            }
        }
    }

    private function syncItems(Bundle $bundle, array $items, ?int $adminId): void
    {
        $incoming = collect($items)->keyBy('product_id');
        $existing = $bundle->items()->get()->keyBy('product_id');

        foreach ($existing as $productId => $item) {
            if (! $incoming->has($productId)) {
                $item->forceDelete();
            }
        }

        foreach ($incoming as $productId => $row) {
            if ($existing->has($productId)) {
                $existing->get($productId)->update([
                    'quantity' => $row['quantity'],
                    'updated_by' => $adminId,
                ]);

                continue;
            }

            BundleItem::create([
                'bundle_id' => $bundle->id,
                'product_id' => $productId,
                'quantity' => $row['quantity'],
                'created_by' => $adminId,
                'updated_by' => $adminId,
            ]);
        }
    }

    private function generateToken(): string
    {
        do {
            $token = Str::random(64);
        } while (Bundle::withTrashed()->where('token', $token)->exists());

        return $token;
    }

    private function loadBundle(Bundle $bundle): Bundle
    {
        return $bundle->fresh()->load([
            'items.product.images',
            'items.product.detail',
        ])->loadCount('items');
    }
}
