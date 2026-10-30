<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use App\Mail\EbookDeliveryMail;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;

class EbookDeliveryService
{
    public function deliverEbooks(Order $order): void
    {
        $ebookItems = $order->orderItems()->with('product')->whereHas('product', function ($q) {
            $q->where('is_e_copy', true);
        })->get();

        if ($ebookItems->isEmpty()) {
            return;
        }

        $order->load('user', 'orderEbookCodes.ebookAccessCode');

        foreach ($ebookItems as $item) {
            $this->sendDeliveryEmail($item, $order->user, $order);
        }

        // Do not mark as delivered just because email sent per requirement; status remains as set by admin confirmation flow
    }

    private function sendDeliveryEmail(OrderItem $item, User $user, Order $order): void
    {
        $codes = [];
        foreach ($order->orderEbookCodes as $oec) {
            if ($oec->order_item_id == $item->id && $oec->ebookAccessCode) {
                $codes[] = $oec->ebookAccessCode->code;
            }
        }
        if (empty($codes)) {
            // Fallback: get codes assigned to this order item
            $codes = \App\Models\EbookAccessCode::where('order_id', $order->id)
                ->where('order_item_id', $item->id)
                ->pluck('code')
                ->toArray();
        }

        $ebookUrl = $item->product->ebook_url;

        SendEmailJob::dispatch(
            $user->email,
            new EbookDeliveryMail(
                $user,
                $item->product->name_en,
                $ebookUrl,
                $codes
            )
        );
    }
}
