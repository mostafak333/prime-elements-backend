<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Mail\EbookDeliveryMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class EbookDeliveryService
{
    public function deliverEbooks(Order $order): void
    {
        $ebookItems = $order->orderItems()->whereHas('product', function ($q) {
            $q->where('is_e_copy', true);
        })->get();

        if ($ebookItems->isEmpty()) {
            return;
        }

        foreach ($ebookItems as $item) {
            $this->attachDownload($item, $order->user);
        }

        $order->update(['status' => 'delivered']);
    }

    private function attachDownload(OrderItem $item, User $user): void
    {
        $token = Str::random(64);
        $expireAt = now()->addDays(7);

        $downloadUrl = URL::temporarySignedRoute(
            'ebook.download',
            $expireAt,
            ['token' => $token]
        );

        $item->update([
            'download_token' => $token,
            'download_url' => $downloadUrl,
            'download_expire_at' => $expireAt,
        ]);

        SendEmailJob::dispatch(
            $user->email,
            new EbookDeliveryMail(
                $user,
                $item->product->name_en,
                $downloadUrl,
                $expireAt->format('Y-m-d H:i:s')
            )
        );
    }
}
