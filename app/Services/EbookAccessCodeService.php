<?php

namespace App\Services;

use App\Models\EbookAccessCode;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class EbookAccessCodeService
{
    public function getCodesForProduct(Product $product): Collection
    {
        return $product->ebookAccessCodes()->with(['order', 'orderItem'])->latest()->get();
    }

    public function addCodes(Product $product, array $codes): array
    {
        return DB::transaction(function () use ($product, $codes) {
            $added = [];
            foreach ($codes as $code) {
                $codeTrimmed = trim((string) $code);
                if ($codeTrimmed === '') {
                    continue;
                }
                if (EbookAccessCode::where('code', $codeTrimmed)->exists()) {
                    throw ValidationException::withMessages([
                        'codes' => [__('messages.ebook.code_exists')],
                    ]);
                }
                $added[] = EbookAccessCode::create([
                    'product_id' => $product->id,
                    'code' => $codeTrimmed,
                ]);
            }
            return $added;
        });
    }

    public function deleteCode(EbookAccessCode $ebookAccessCode): void
    {
        if ($ebookAccessCode->used_at) {
            throw ValidationException::withMessages([
                'code' => [__('messages.ebook.cannot_delete_used')],
            ]);
        }
        $ebookAccessCode->delete();
    }

    public function getAvailableCount(Product $product): int
    {
        return EbookAccessCode::where('product_id', $product->id)
            ->whereNull('used_at')
            ->count();
    }

    public function reserveCodes(Product $product, int $quantity, int $orderId): array
    {
        return DB::transaction(function () use ($product, $quantity, $orderId) {
            $available = EbookAccessCode::where('product_id', $product->id)
                ->whereNull('used_at')
                ->lockForUpdate()
                ->limit($quantity)
                ->get();

            if ($available->count() < $quantity) {
                throw ValidationException::withMessages([
                    'cart' => [__('messages.ebook.insufficient_codes')],
                ]);
            }

            $reserved = [];
            foreach ($available as $code) {
                $code->update([
                    'used_at' => now(),
                    'order_id' => $orderId,
                ]);
                $reserved[] = $code;
            }

            // Keep product stock in sync for compatibility
            $product->stock = $this->getAvailableCount($product);
            $product->save();

            return $reserved;
        });
    }

    public function releaseCodesForOrder(int $orderId): void
    {
        DB::transaction(function () use ($orderId) {
            $codes = EbookAccessCode::where('order_id', $orderId)->lockForUpdate()->get();
            $productId = null;
            foreach ($codes as $code) {
                $productId = $productId ?? $code->product_id;
                $code->update([
                    'used_at' => null,
                    'order_id' => null,
                    'order_item_id' => null,
                ]);
            }
            if (! empty($productId)) {
                $product = Product::find($productId);
                if ($product) {
                    $product->stock = $this->getAvailableCount($product);
                    $product->save();
                }
            }
        });
    }

    public function attachCodesToOrderItem($order, $orderItem, array $codes): void
    {
        foreach ($codes as $code) {
            $code->update([
                'order_item_id' => $orderItem->id,
            ]);
            \App\Models\OrderEbookCode::create([
                'order_id' => $order->id,
                'order_item_id' => $orderItem->id,
                'ebook_access_code_id' => $code->id,
            ]);
        }
    }
}
