@php
    $locale = app()->getLocale();
    $isArabic = $locale === 'ar';
    $direction = $isArabic ? 'rtl' : 'ltr';
    $currency = $isArabic ? 'ر.س' : 'SAR';
    $textStart = $isArabic ? 'right' : 'left';
    $textEnd = $isArabic ? 'left' : 'right';

    $localizedName = function ($model, string $attribute = 'name') use ($isArabic) {
        if (!$model) {
            return '';
        }

        $english = data_get($model, $attribute . '_en');
        $arabic = data_get($model, $attribute . '_ar');

        return $isArabic ? ($arabic ?: $english ?: '') : ($english ?: $arabic ?: '');
    };

    $labels = $isArabic ? [
        'invoice' => 'فاتورة',
        'brand_tagline' => 'متجر الكتب التعليمية والعامة',
        'payment_confirmed' => 'تم تأكيد الدفع',
        'payment_unpaid' => 'الدفع غير مكتمل',
        'payment_pending' => 'بانتظار الدفع',
        'payment_refunded' => 'تم استرداد المبلغ',
        'payment_failed' => 'فشل الدفع',
        'order_number' => 'رقم الطلب',
        'order_date' => 'تاريخ الطلب',
        'payment_method' => 'طريقة الدفع',
        'shipping_address' => 'عنوان الشحن',
        'shipping_information' => 'معلومات الشحن',
        'phone' => 'الهاتف',
        'email' => 'البريد الإلكتروني',
        'method' => 'طريقة الشحن',
        'shipping_fee' => 'رسوم الشحن',
        'tracking_number' => 'رقم التتبع',
        'estimated_delivery' => 'التوصيل المتوقع',
        'order_items' => 'تفاصيل الطلب',
        'items' => 'عناصر',
        'product' => 'المنتج',
        'author' => 'المؤلف',
        'unavailable' => 'المنتج غير متوفر',
        'quantity' => 'الكمية',
        'unit_price' => 'سعر الوحدة',
        'price' => 'السعر',
        'total' => 'الإجمالي',
        'subtotal' => 'المجموع الفرعي',
        'discount' => 'الخصم',
        'tax' => 'الضريبة',
        'total_amount' => 'الإجمالي النهائي',
        'no_items' => 'لا توجد عناصر في هذا الطلب.',
        'thank_you' => 'شكراً لتسوقكم معنا',
        'footer_1' => 'متجركم الموثوق للكتب التعليمية والعامة.',
        'footer_2' => '© ' . date('Y') . ' PrimeElements. جميع الحقوق محفوظة.',
    ] : [
        'invoice' => 'INVOICE',
        'brand_tagline' => 'Learning & Educational Bookstore',
        'payment_confirmed' => 'Payment Confirmed',
        'payment_unpaid' => 'Payment Unpaid',
        'payment_pending' => 'Payment Pending',
        'payment_refunded' => 'Payment Refunded',
        'payment_failed' => 'Payment Failed',
        'order_number' => 'Order Number',
        'order_date' => 'Order Date',
        'payment_method' => 'Payment Method',
        'shipping_address' => 'Shipping Address',
        'shipping_information' => 'Shipping Information',
        'phone' => 'Phone',
        'email' => 'Email',
        'method' => 'Method',
        'shipping_fee' => 'Shipping Fee',
        'tracking_number' => 'Tracking Number',
        'estimated_delivery' => 'Estimated Delivery',
        'order_items' => 'Order Items',
        'items' => 'items',
        'product' => 'Product',
        'author' => 'Author',
        'unavailable' => 'Product unavailable',
        'quantity' => 'Qty',
        'unit_price' => 'Unit Price',
        'price' => 'Price',
        'total' => 'Total',
        'subtotal' => 'Subtotal',
        'discount' => 'Discount',
        'tax' => 'Tax',
        'total_amount' => 'Total Amount',
        'no_items' => 'No items found for this order.',
        'thank_you' => 'Thank you for shopping with us',
        'footer_1' => 'Your trusted online bookstore for educational and general books.',
        'footer_2' => '© ' . date('Y') . ' PrimeElements. All rights reserved.',
    ];

    $address = $order->addressDetail;
    $customerName = $order->user_full_name ?: data_get($address, 'full_name', '');
    $cityLine = collect([
        data_get($address, 'city'),
        data_get($address, 'state'),
        data_get($address, 'postal_code'),
    ])->filter()->implode(', ');

    $orderDate = $order->created_at
        ? $order->created_at->copy()->locale($locale)->translatedFormat('d F Y, h:i A')
        : '';

    $estimatedDelivery = $order->estimated_delivery_date
        ? $order->estimated_delivery_date->copy()->locale($locale)->translatedFormat('d F Y')
        : '';

    $paymentMethodName = $localizedName($order->paymentMethod);
    $deliveryMethodName = $localizedName($order->deliveryMethod);

    $paymentStatus = strtolower((string) $order->payment_status);
    $paymentStatusLabel = match ($paymentStatus) {
        'paid' => $labels['payment_confirmed'],
        'unpaid' => $labels['payment_unpaid'],
        'refunded' => $labels['payment_refunded'],
        'failed' => $labels['payment_failed'],
        'pending' => $labels['payment_pending'],
        default => $order->payment_status ?: $labels['payment_pending'],
    };
    $paymentStatusClass = $paymentStatus === 'paid' ? 'status-paid' : 'status-pending';

    $itemsSubtotal = $order->orderItems->sum(function ($item) {
        $quantity = (float) ($item->quantity ?? 0);
        $unitPrice = (float) ($item->price ?? 0);

        return $quantity * $unitPrice;
    });

    $subtotal = (float) ($order->subtotal ?? $itemsSubtotal);
    $shipping = (float) ($order->shipping ?? 0);
    $discount = (float) ($order->discount ?? 0);
    $tax = (float) ($order->tax ?? 0);
    $grandTotal = (float) ($order->total ?? ($subtotal - $discount + $shipping + $tax));

    $phone = $order->phone_to_number ?: data_get($address, 'phone', '');
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $direction }}">

<head>
    <meta charset="UTF-8">
    <title>{{ $labels['invoice'] }} - {{ $order->order_number }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            color: #241b35;
            font-family:
                {!! $isArabic ? "'xbriyaz', 'dejavusans', sans-serif" : "'dejavusans', sans-serif" !!}
            ;
            font-size: 8.5pt;
            line-height: 1.42;
            text-align:
                {{ $textStart }}
            ;
            direction:
                {{ $direction }}
            ;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td {
            vertical-align: top;
        }

        /* Accent bar */
        .accent {
            margin: 0 0 9px;
        }

        .accent td {
            height: 4px;
            font-size: 1px;
            line-height: 4px;
        }

        .accent-main {
            width: 82%;
            background-color: #3f1d6b;
        }

        .accent-alt {
            width: 18%;
            background-color: #8b5cf6;
        }

        /* Header */
        .header {
            margin: 0 0 9px;
        }

        .brand-cell {
            width: 54%;
        }

        .brand {
            width: auto;
        }

        .brand-mark {
            width: 34px;
            height: 34px;
            padding: 0;
            background-color: #3f1d6b;
            color: #ffffff;
            font-size: 13pt;
            font-weight: bold;
            line-height: 34px;
            text-align: center;
            vertical-align: middle;
        }

        .brand-text {
            vertical-align: middle;
            text-align:
                {{ $textStart }}
            ;
        }

        .brand-name {
            margin: 0;
            color: #3f1d6b;
            font-size: 19pt;
            font-weight: bold;
            line-height: 1.08;
        }

        .brand-tagline {
            margin: 2px 0 0;
            color: #6b6480;
            font-size: 7.5pt;
        }

        .meta-cell {
            width: 46%;
        }

        .invoice-title {
            margin: 0 0 2px;
            color: #3f1d6b;
            font-size: 22pt;
            font-weight: bold;
            line-height: 1.02;
        }

        .status-text {
            margin: 0 0 5px;
            font-size: 7.5pt;
            font-weight: bold;
        }

        .status-paid {
            color: #1f7a4d;
        }

        .status-pending {
            color: #b45309;
        }

        .meta-line {
            margin: 1.5px 0;
            color: #5b5470;
            font-size: 7.8pt;
        }

        .meta-label {
            color: #6b6480;
        }

        .meta-value {
            color: #241b35;
            font-weight: bold;
        }

        /* Information cards */
        .card {
            margin: 0 0 7px;
            border: 1px solid #e6e0f0;
            background-color: #ffffff;
        }

        .card-head {
            padding: 5px 8px;
            border-bottom: 1px solid #e6e0f0;
            background-color: #f5f1fb;
        }

        .card-title {
            color: #3f1d6b;
            font-size: 7.6pt;
            font-weight: bold;
            letter-spacing: 0.7px;
            text-transform: uppercase;
        }

        .card-body {
            padding: 7px 8px;
        }

        .address-main {
            width: 60%;
            text-align:
                {{ $textStart }}
            ;
        }

        .address-contact {
            width: 40%;
            text-align:
                {{ $textEnd }}
            ;
        }

        .customer-name {
            margin: 0 0 2px;
            color: #241b35;
            font-size: 9pt;
            font-weight: bold;
        }

        .address-line {
            margin: 0;
            color: #4a435c;
            font-size: 8pt;
        }

        .contact-block {
            margin: 0 0 4px;
        }

        .field-label {
            color: #7a7290;
            font-size: 6.8pt;
            font-weight: bold;
            letter-spacing: 0.4px;
            text-transform: uppercase;
        }

        .field-value {
            margin: 1px 0 0;
            color: #241b35;
            font-size: 8.2pt;
            font-weight: bold;
        }

        .fields td {
            width: 25%;
            padding: 0 6px;
            text-align:
                {{ $textStart }}
            ;
        }

        /* Items */
        .section-head {
            margin: 10px 0 4px;
        }

        .section-title {
            color: #3f1d6b;
            font-size: 8.6pt;
            font-weight: bold;
            text-align:
                {{ $textStart }}
            ;
        }

        .section-count {
            color: #7a7290;
            font-size: 7.5pt;
            text-align:
                {{ $textEnd }}
            ;
        }

        .items {
            margin: 0 0 8px;
            table-layout: fixed;
        }

        .items thead {
            display: table-header-group;
        }

        .items th {
            padding: 6px;
            border: 0;
            background-color: #3f1d6b;
            color: #ffffff;
            font-size: 7.6pt;
            font-weight: bold;
        }

        .items td {
            padding: 5px 6px;
            border-bottom: 1px solid #ede9f5;
            color: #241b35;
            font-size: 8pt;
            vertical-align: top;
            word-wrap: break-word;
        }

        .items tr {
            page-break-inside: avoid;
        }

        .items .alt td {
            background-color: #faf8fd;
        }

        .col-index {
            width: 6%;
            text-align: center;
        }

        .col-product {
            width: 46%;
            text-align:
                {{ $textStart }}
            ;
        }

        .col-qty {
            width: 9%;
            text-align: center;
        }

        .col-price {
            width: 19%;
            text-align:
                {{ $textEnd }}
            ;
        }

        .col-total {
            width: 20%;
            text-align:
                {{ $textEnd }}
            ;
        }

        .cell-index {
            color: #7a7290;
            text-align: center;
        }

        .cell-product {
            text-align:
                {{ $textStart }}
            ;
        }

        .cell-qty {
            text-align: center;
        }

        .cell-amount {
            text-align:
                {{ $textEnd }}
            ;
        }

        .item-name {
            color: #241b35;
            font-weight: bold;
        }

        .item-meta {
            margin-top: 2px;
            color: #7a7290;
            font-size: 7.2pt;
        }

        .currency {
            color: #7a7290;
            font-size: 7pt;
        }

        .empty-row td {
            padding: 12px 6px;
            color: #7a7290;
            text-align: center;
        }

        /* Totals */
        .totals-wrap {
            margin: 2px 0 0;
            page-break-inside: avoid;
        }

        .totals-spacer {
            width: 50%;
        }

        .totals-box {
            width: 50%;
            padding: 0;
        }

        .totals {
            border: 1px solid #e6e0f0;
        }

        .totals td {
            padding: 4.5px 7px;
            border-bottom: 1px solid #ede9f5;
            font-size: 8.2pt;
        }

        .totals .t-label {
            color: #5b5470;
            text-align:
                {{ $textStart }}
            ;
        }

        .totals .t-value {
            color: #241b35;
            font-weight: bold;
            text-align:
                {{ $textEnd }}
            ;
        }

        .totals .grand td {
            padding: 7px;
            border: 0;
            background-color: #3f1d6b;
            color: #ffffff;
            font-size: 10.5pt;
            font-weight: bold;
        }

        .totals .grand .t-label,
        .totals .grand .t-value {
            color: #ffffff;
        }

        /* Closing note */
        .thanks {
            margin-top: 10px;
            padding-top: 7px;
            border-top: 1px solid #e6e0f0;
            page-break-inside: avoid;
            text-align: center;
        }

        .thanks-title {
            margin: 0 0 2px;
            color: #3f1d6b;
            font-size: 9pt;
            font-weight: bold;
        }

        .thanks-text {
            margin: 0;
            color: #6b6480;
            font-size: 7.5pt;
        }

        .legal {
            margin: 3px 0 0;
            color: #9a93ab;
            font-size: 6.8pt;
        }

        body.ar .card-title,
        body.ar .field-label {
            letter-spacing: 0;
            text-transform: none;
        }
    </style>
</head>

<body class="{{ $isArabic ? 'ar' : 'en' }}">
    <table class="accent">
        <tr>
            <td class="accent-main">&nbsp;</td>
            <td class="accent-alt">&nbsp;</td>
        </tr>
    </table>

    <table class="header">
        <tr>
            <td class="meta-cell" style="text-align: {{ $isArabic ? 'right' : 'left' }}; direction: {{ $direction }};">
                <div class="invoice-title">{{ $labels['invoice'] }}</div>
                <div class="status-text {{ $paymentStatusClass }}">{{ $paymentStatusLabel }}</div>

                <div class="meta-line">
                    <span class="meta-label">{{ $labels['order_number'] }}:</span>
                    <span class="meta-value">{{ $order->order_number }}</span>
                </div>

                <div class="meta-line">
                    <span class="meta-label">{{ $labels['order_date'] }}:</span>
                    <span class="meta-value">{{ $orderDate }}</span>
                </div>

                @if($paymentMethodName !== '')
                    <div class="meta-line">
                        <span class="meta-label">{{ $labels['payment_method'] }}:</span>
                        <span class="meta-value">{{ $paymentMethodName }}</span>
                    </div>
                @endif
            </td>

            <td class="brand-cell"
                style="text-align: {{ $isArabic ? 'left' : 'right' }}; direction: {{ $isArabic ? 'ltr' : 'rtl' }};">
                <table class="brand" style="margin-{{ $isArabic ? 'left' : 'right' }}: 0;">
                    <tr>
                        <td class="brand-text" style="padding: 0; text-align: {{ $isArabic ? 'left' : 'right' }};">
                            <div class="brand-name">PrimeElements</div>
                            <div class="brand-tagline">{{ $labels['brand_tagline'] }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    {{-- Shipping address and shipping information stay stacked for reliable PDF rendering. --}}
    <table class="card">
        <tr>
            <td class="card-head">
                <div class="card-title">{{ $labels['shipping_address'] }}</div>
            </td>
        </tr>
        <tr>
            <td class="card-body">
                <table>
                    <tr>
                        <td class="address-main">
                            @if($customerName !== '')
                                <div class="customer-name">{{ $customerName }}</div>
                            @endif
                            @if(data_get($address, 'address_line1'))
                                <div class="address-line">{{ data_get($address, 'address_line1') }}</div>
                            @endif
                            @if(data_get($address, 'address_line2'))
                                <div class="address-line">{{ data_get($address, 'address_line2') }}</div>
                            @endif
                            @if($cityLine !== '')
                                <div class="address-line">{{ $cityLine }}</div>
                            @endif
                            @if(data_get($address, 'country'))
                                <div class="address-line">{{ data_get($address, 'country') }}</div>
                            @endif
                        </td>
                        <td class="address-contact">
                            @if($phone)
                                <div class="contact-block">
                                    <div class="field-label">{{ $labels['phone'] }}</div>
                                    <div class="field-value">{{ $phone }}</div>
                                </div>
                            @endif
                            @if($order->email)
                                <div class="contact-block">
                                    <div class="field-label">{{ $labels['email'] }}</div>
                                    <div class="field-value">{{ $order->email }}</div>
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="card">
        <tr>
            <td class="card-head">
                <div class="card-title">{{ $labels['shipping_information'] }}</div>
            </td>
        </tr>
        <tr>
            <td class="card-body">
                <table class="fields">
                    <tr>
                        <td>
                            <div class="field-label">{{ $labels['method'] }}</div>
                            <div class="field-value">{{ $deliveryMethodName ?: '-' }}</div>
                        </td>
                        <td>
                            <div class="field-label">{{ $labels['shipping_fee'] }}</div>
                            <div class="field-value">{{ number_format($shipping, 2) }} {{ $currency }}</div>
                        </td>
                        <td>
                            <div class="field-label">{{ $labels['tracking_number'] }}</div>
                            <div class="field-value">{{ $order->tracking_number ?: '-' }}</div>
                        </td>
                        <td>
                            <div class="field-label">{{ $labels['estimated_delivery'] }}</div>
                            <div class="field-value">{{ $estimatedDelivery ?: '-' }}</div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="section-head">
        <tr>
            <td class="section-title">{{ $labels['order_items'] }}</td>
            <td class="section-count">{{ $order->orderItems->count() }} {{ $labels['items'] }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th class="col-index">#</th>
                <th class="col-product">{{ $labels['product'] }}</th>
                <th class="col-qty">{{ $labels['quantity'] }}</th>
                <th class="col-price">{{ $labels['unit_price'] }}</th>
                <th class="col-total">{{ $labels['total'] }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->orderItems as $item)
                @php
                    $product = $item->product;
                    $productName = $product ? $localizedName($product) : '';
                    $author = data_get($product, 'detail.author');
                    $quantity = (float) ($item->quantity ?? 0);
                    $unitPrice = (float) ($item->price ?? 0);
                    $lineTotal = $quantity * $unitPrice;
                    $quantityLabel = rtrim(rtrim(number_format($quantity, 2, '.', ''), '0'), '.');
                @endphp
                <tr class="{{ $loop->even ? 'alt' : '' }}">
                    <td class="cell-index">{{ $loop->iteration }}</td>
                    <td class="cell-product">
                        <div class="item-name">{{ $productName ?: $labels['unavailable'] }}</div>
                        @if($author)
                            <div class="item-meta">{{ $labels['author'] }}: {{ $author }}</div>
                        @endif
                    </td>
                    <td class="cell-qty">{{ $quantityLabel }}</td>
                    <td class="cell-amount">{{ number_format($unitPrice, 2) }} <span class="currency">{{ $currency }}</span>
                    </td>
                    <td class="cell-amount">{{ number_format($lineTotal, 2) }} <span class="currency">{{ $currency }}</span>
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="5">{{ $labels['no_items'] }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals-wrap">
        <tr>
            <td class="totals-spacer"></td>
            <td class="totals-box">
                <table class="totals">
                    <tr>
                        <td class="t-label">{{ $labels['subtotal'] }}</td>
                        <td class="t-value">{{ number_format($subtotal, 2) }} {{ $currency }}</td>
                    </tr>
                    @if($discount > 0)
                        <tr>
                            <td class="t-label">{{ $labels['discount'] }}</td>
                            <td class="t-value">-{{ number_format($discount, 2) }} {{ $currency }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="t-label">{{ $labels['shipping_fee'] }}</td>
                        <td class="t-value">{{ number_format($shipping, 2) }} {{ $currency }}</td>
                    </tr>
                    @if($tax > 0)
                        <tr>
                            <td class="t-label">{{ $labels['tax'] }}</td>
                            <td class="t-value">{{ number_format($tax, 2) }} {{ $currency }}</td>
                        </tr>
                    @endif
                    <tr class="grand">
                        <td class="t-label">{{ $labels['total_amount'] }}</td>
                        <td class="t-value">{{ number_format($grandTotal, 2) }} {{ $currency }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="thanks">
        <tr>
            <td>
                <div class="thanks-title">{{ $labels['thank_you'] }}</div>
                <div class="thanks-text">{{ $labels['footer_1'] }}</div>
                <div class="legal">{{ $labels['footer_2'] }}</div>
            </td>
        </tr>
    </table>
</body>

</html>