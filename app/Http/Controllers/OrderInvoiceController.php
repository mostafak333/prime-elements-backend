<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class OrderInvoiceController extends Controller
{
    public function download($orderId)
    {
        $order = $this->authorizedOrder($orderId);
        $isArabic = app()->getLocale() === 'ar';

        $tempDir = storage_path('app/mpdf');
        File::ensureDirectoryExists($tempDir);

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_left' => 11,
            'margin_right' => 11,
            'margin_top' => 11,
            'margin_bottom' => 17,
            'default_font' => $isArabic ? 'xbriyaz' : 'dejavusans',
            'default_font_size' => 9,
            'tempDir' => $tempDir,
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'biDirectional' => true,
        ]);

        $mpdf->SetDirectionality($isArabic ? 'rtl' : 'ltr');
        $mpdf->SetTitle(($isArabic ? 'فاتورة' : 'Invoice').' '.$order->order_number);
        $mpdf->SetAuthor('PrimeElements');
        $mpdf->SetCreator('PrimeElements');
        $mpdf->SetHTMLFooter(
            '<table style="width:100%;border-top:1px solid #e6e0f0;color:#7a7290;font-size:7pt;">'
            .'<tr>'
            .'<td style="padding-top:4px;text-align:'.($isArabic ? 'right' : 'left').';">PrimeElements</td>'
            .'<td style="padding-top:4px;text-align:'.($isArabic ? 'left' : 'right').';">'
            .($isArabic ? 'صفحة' : 'Page').' {PAGENO} / {nbpg}'
            .'</td>'
            .'</tr>'
            .'</table>'
        );

        $mpdf->WriteHTML(view('invoices.order', ['order' => $order])->render());

        $filename = 'invoice-'.$order->order_number.'.pdf';
        $pdf = $mpdf->Output($filename, Destination::STRING_RETURN);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Content-Length' => (string) strlen($pdf),
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ]);
    }

    public function view($orderId)
    {
        $order = $this->authorizedOrder($orderId);

        return view('invoices.order', ['order' => $order]);
    }

    private function authorizedOrder($orderId): Order
    {
        $order = Order::with(['orderItems.product.detail', 'paymentMethod', 'deliveryMethod', 'addressDetail', 'user'])
            ->findOrFail($orderId);

        if (Auth::guard('api-admin')->check()) {
            // allow admin
        } elseif (Auth::guard('api-user')->check()) {
            if ($order->user_id !== Auth::guard('api-user')->id()) {
                abort(403);
            }
        } else {
            abort(403);
        }

        if (! in_array($order->status, ['confirmed', 'out_for_delivery', 'delivered'], true)) {
            abort(403);
        }

        return $order;
    }
}
