<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderInvoiceController extends Controller
{
    public function download($orderId)
    {
        $order = Order::with(['orderItems.product.detail', 'paymentMethod', 'deliveryMethod', 'addressDetail', 'user'])
            ->findOrFail($orderId);

        $user = Auth::guard('api-user')->user() ?? Auth::guard('api-admin')->user();
        if (Auth::guard('api-admin')->check()) {
            // allow admin
        } elseif (Auth::guard('api-user')->check()) {
            if ($order->user_id !== Auth::guard('api-user')->id()) {
                abort(403);
            }
        } else {
            abort(403);
        }

        if ($order->status !== 'confirmed' && $order->status !== 'out_for_delivery' && $order->status !== 'delivered') {
            abort(403);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.order', ['order' => $order]);
        return $pdf->download('invoice-'.$order->order_number.'.pdf');
    }

    public function view($orderId)
    {
        $order = Order::with(['orderItems.product.detail', 'paymentMethod', 'deliveryMethod', 'addressDetail', 'user'])
            ->findOrFail($orderId);

        if (Auth::guard('api-admin')->check()) {
            // allow
        } elseif (Auth::guard('api-user')->check()) {
            if ($order->user_id !== Auth::guard('api-user')->id()) {
                abort(403);
            }
        } else {
            abort(403);
        }

        if ($order->status !== 'confirmed' && $order->status !== 'out_for_delivery' && $order->status !== 'delivered') {
            abort(403);
        }

        return view('invoices.order', ['order' => $order]);
    }
}
