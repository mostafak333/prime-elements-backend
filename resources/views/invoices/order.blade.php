@php
    $locale = app()->getLocale();
    $isArabic = $locale === 'ar';
    $currency = $isArabic ? 'ر.س' : 'SAR';

    $itemCount = $order->orderItems->count();
    $densityClass = match (true) {
        $itemCount > 18 => 'dense',
        $itemCount > 10 => 'ultra-compact',
        $itemCount > 6 => 'compact',
        default => '',
    };

    $localizedName = function ($model, string $attribute = 'name') use ($isArabic) {
        if (! $model) {
            return '';
        }

        $english = $model->{$attribute.'_en'} ?? null;
        $arabic = $model->{$attribute.'_ar'} ?? null;

        return $isArabic ? ($arabic ?: $english) : ($english ?: $arabic);
    };

    $address = $order->addressDetail;
    $customerName = $order->user_full_name ?: ($address->full_name ?? '');
    $cityLine = collect([
        $address->city ?? null,
        $address->state ?? null,
        $address->postal_code ?? null,
    ])->filter()->implode(', ');

    $orderDate = $order->created_at
        ? $order->created_at->copy()->locale($locale)->translatedFormat('d F Y, h:i A')
        : '';
    $estimatedDelivery = $order->estimated_delivery_date
        ? $order->estimated_delivery_date->copy()->locale($locale)->translatedFormat('d F Y')
        : '';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isArabic ? 'rtl' : 'ltr' }}" class="{{ $densityClass }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.invoice.title') }} - {{ $order->order_number ?? 'ORD-' }}</title>
    <style>
        /* Single-page page constraints for DomPDF */
        @page {
            size: A4 portrait;
            margin: {{ $itemCount > 18 ? '6mm' : '10mm' }};
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            color: #2c1654;
            font-size: 11px;
            line-height: 1.25;
            page-break-inside: avoid;
        }

        body.ar {
            font-family: 'DejaVu Sans', sans-serif;
        }

        .invoice-box {
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            background: #ffffff;
            padding: 0;
            page-break-inside: avoid;
            page-break-after: avoid;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #2c1654;
            margin-bottom: 10px;
        }

        .header-table td {
            vertical-align: top;
            padding: 0 0 8px;
        }

        .logo-area h2 {
            color: #2c1654;
            margin: 0;
            font-size: 21px;
            font-weight: 800;
        }

        .logo-area p {
            font-size: 10.5px;
            color: #666;
            margin: 2px 0 0;
        }

        .invoice-details {
            text-align: right;
        }

        body.ar .invoice-details {
            text-align: left;
        }

        .invoice-details h1 {
            color: #2c1654;
            font-size: 17px;
            margin: 0 0 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .invoice-badge {
            display: inline-block;
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .invoice-details p {
            font-size: 10.5px;
            color: #555;
            margin: 2px 0;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-cell {
            width: 50%;
            vertical-align: top;
        }

        .info-cell.info-left {
            padding-right: 4px;
        }

        .info-cell.info-right {
            padding-left: 4px;
        }

        .info-card {
            background: #fcfbf7;
            padding: 8px 10px;
            border-radius: 6px;
            border: 1px solid #f0efeb;
            page-break-inside: avoid;
        }

        .info-card h3 {
            color: #2c1654;
            font-size: 11px;
            margin: 0 0 5px;
            border-bottom: 1px solid #e2dfd5;
            padding-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-card p {
            font-size: 10.5px;
            color: #444;
            margin: 2px 0;
            line-height: 1.3;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }

        .items-table th {
            background-color: #2c1654;
            color: #ffffff;
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
            font-weight: 600;
        }

        .items-table th:last-child,
        .items-table td:last-child {
            text-align: right;
        }

        body.ar .items-table th,
        body.ar .items-table td {
            text-align: right;
        }

        body.ar .items-table th:last-child,
        body.ar .items-table td:last-child {
            text-align: left;
        }

        .items-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #f0efeb;
            font-size: 10.5px;
            color: #333;
        }

        .items-table tr {
            page-break-inside: avoid;
        }

        .item-discount {
            color: #2e7d32;
            font-size: 9px;
            margin-top: 1px;
        }

        .totals-table {
            width: 46%;
            margin-left: auto;
            border-collapse: collapse;
            font-size: 11px;
        }

        body.ar .totals-table {
            margin-left: 0;
            margin-right: auto;
        }

        .totals-table td {
            padding: 4px 0;
            color: #555;
            border-bottom: 1px solid #f0efeb;
        }

        .totals-table td.value {
            text-align: right;
        }

        body.ar .totals-table td.value {
            text-align: left;
        }

        .totals-table tr.final td {
            border-bottom: 2px solid #2c1654;
            border-top: 1px solid #2c1654;
            font-weight: bold;
            font-size: 14px;
            color: #2c1654;
            padding: 5px 0;
        }

        .currency {
            font-family: DejaVu Sans, sans-serif;
            font-weight: bold;
        }

        .invoice-footer {
            margin-top: 12px;
            text-align: center;
            font-size: 9.5px;
            color: #777;
            border-top: 1px solid #eae8df;
            padding-top: 8px;
            page-break-inside: avoid;
        }

        .invoice-footer p {
            margin: 2px 0;
        }

        /* Compact the layout when the order has many items to keep the invoice on one page. */
        html.compact body {
            font-size: 10.5px;
        }

        html.compact .items-table th,
        html.compact .items-table td {
            padding: 4px 6px;
            font-size: 9.5px;
        }

        html.compact .info-card {
            padding: 6px 8px;
        }

        html.compact .info-card p,
        html.compact .invoice-details p {
            font-size: 10px;
        }

        html.ultra-compact body {
            font-size: 10px;
        }

        html.ultra-compact .invoice-header,
        html.ultra-compact .header-table {
            margin-bottom: 7px;
        }

        html.ultra-compact .items-table th,
        html.ultra-compact .items-table td {
            padding: 3px 5px;
            font-size: 9px;
        }

        html.ultra-compact .info-card {
            padding: 5px 7px;
        }

        html.ultra-compact .info-card p,
        html.ultra-compact .invoice-details p {
            font-size: 9.5px;
        }

        html.ultra-compact .totals-table td {
            padding: 3px 0;
        }

        /* Dense mode keeps very large invoices on the same page. */
        html.dense body {
            font-size: 9px;
            line-height: 1.15;
        }

        html.dense .header-table {
            margin-bottom: 6px;
        }

        html.dense .logo-area h2 {
            font-size: 17px;
        }

        html.dense .invoice-details h1 {
            font-size: 14px;
        }

        html.dense .info-table {
            margin-bottom: 6px;
        }

        html.dense .info-card {
            padding: 4px 6px;
        }

        html.dense .info-card h3 {
            font-size: 9px;
            margin-bottom: 3px;
            padding-bottom: 2px;
        }

        html.dense .info-card p,
        html.dense .invoice-details p {
            font-size: 8.5px;
            margin: 1px 0;
            line-height: 1.15;
        }

        html.dense .items-table {
            margin-bottom: 6px;
        }

        html.dense .items-table th {
            padding: 3px 4px;
            font-size: 8.5px;
        }

        html.dense .items-table td {
            padding: 2px 4px;
            font-size: 8.5px;
            line-height: 1.15;
        }

        html.dense .item-discount {
            font-size: 7.5px;
            margin-top: 0;
        }

        html.dense .totals-table {
            font-size: 9px;
        }

        html.dense .totals-table td {
            padding: 2px 0;
        }

        html.dense .totals-table tr.final td {
            font-size: 11px;
            padding: 3px 0;
        }

        html.dense .invoice-footer {
            margin-top: 6px;
            padding-top: 5px;
            font-size: 8px;
        }
    </style>
</head>

<body class="{{ $isArabic ? 'ar' : '' }}">

    <div class="invoice-box">
        <table class="header-table">
            <tr>
                <td class="logo-area">
                    <h2>PrimeElements</h2>
                    <p>{{ __('messages.invoice.bookstore') }}</p>
                </td>
                <td class="invoice-details">
                    <h1>{{ __('messages.invoice.title') }}</h1>
                    <div class="invoice-badge">{{ __('messages.invoice.payment_confirmed') }}</div>
                    <p><strong>{{ __('messages.invoice.order_number') }}:</strong> {{ $order->order_number }}</p>
                    <p><strong>{{ __('messages.invoice.order_date') }}:</strong> {{ $orderDate }}</p>
                    <p><strong>{{ __('messages.invoice.payment_method') }}:</strong>
                        {{ $localizedName($order->paymentMethod) ?: $order->payment_method_id }}
                    </p>
                </td>
            </tr>
        </table>

        <table class="info-table">
            <tr>
                <td class="info-cell info-left">
                    <div class="info-card">
                        <h3>{{ __('messages.invoice.shipping_address') }}</h3>
                        <p><strong>{{ $customerName }}</strong></p>
                        @if(!empty($address->address_line1))
                            <p>{{ $address->address_line1 }}</p>
                        @endif
                        @if(!empty($address->address_line2))
                            <p>{{ $address->address_line2 }}</p>
                        @endif
                        @if($cityLine !== '')
                            <p>{{ $cityLine }}</p>
                        @endif
                        @if(!empty($address->country))
                            <p>{{ $address->country }}</p>
                        @endif
                        <p><strong>{{ __('messages.invoice.phone') }}:</strong>
                            {{ $order->phone_to_number ?: ($address->phone ?? '') }}</p>
                        @if(!empty($order->email))
                            <p><strong>{{ __('messages.invoice.email') }}:</strong> {{ $order->email }}</p>
                        @endif
                    </div>
                </td>
                <td class="info-cell info-right">
                    <div class="info-card">
                        <h3>{{ __('messages.invoice.shipping_information') }}</h3>
                        <p><strong>{{ __('messages.invoice.method') }}:</strong>
                            {{ $localizedName($order->deliveryMethod) }}</p>
                        <p><strong>{{ __('messages.invoice.shipping_fee') }}:</strong>
                            @if((float) $order->shipping == 0)
                                {{ __('messages.invoice.free') }}
                            @else
                                <span class="currency">{{ $currency }}</span>
                                {{ number_format((float) $order->shipping, 2) }}
                            @endif
                        </p>
                        <p><strong>{{ __('messages.invoice.tracking_number') }}:</strong>
                            {{ $order->tracking_number ?: '-' }}</p>
                        @if($estimatedDelivery !== '')
                            <p><strong>{{ __('messages.invoice.estimated_delivery') }}:</strong>
                                {{ $estimatedDelivery }}</p>
                        @endif
                    </div>
                </td>
            </tr>
        </table>

        <table class="items-table">
            <thead>
                <tr>
                    <th>{{ __('messages.invoice.product') }}</th>
                    <th>{{ __('messages.invoice.author_note') }}</th>
                    <th>{{ __('messages.invoice.qty') }}</th>
                    <th>{{ __('messages.invoice.price') }}</th>
                    <th>{{ __('messages.invoice.total') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                    <tr>
                        <td><strong>{{ $localizedName($item->product) }}</strong></td>
                        <td style="color: #666;">{{ $item->product?->detail?->author ?? '-' }}</td>
                        <td>x {{ $item->quantity }}</td>
                        <td>
                            <span class="currency">{{ $currency }}</span>
                            {{ number_format((float) $item->price, 2) }}
                            @if((float) $item->discount > 0)
                                <div class="item-discount">
                                    - <span class="currency">{{ $currency }}</span>
                                    {{ number_format((float) $item->discount, 2) }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="currency">{{ $currency }}</span>
                            {{ number_format(((float) $item->price - (float) $item->discount) * (int) $item->quantity, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td>{{ __('messages.invoice.subtotal') }}:</td>
                <td class="value"><span class="currency">{{ $currency }}</span>
                    {{ number_format((float) $order->subtotal, 2) }}</td>
            </tr>
            @if((float) $order->discount > 0)
                <tr>
                    <td>{{ __('messages.invoice.discount') }}:</td>
                    <td class="value">- <span class="currency">{{ $currency }}</span>
                        {{ number_format((float) $order->discount, 2) }}</td>
                </tr>
            @endif
            @if((float) $order->tax > 0)
                <tr>
                    <td>{{ __('messages.invoice.tax') }}:</td>
                    <td class="value"><span class="currency">{{ $currency }}</span>
                        {{ number_format((float) $order->tax, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td>{{ __('messages.invoice.shipping_fee') }}:</td>
                <td class="value">
                    @if((float) $order->shipping == 0)
                        {{ __('messages.invoice.free') }}
                    @else
                        <span class="currency">{{ $currency }}</span>
                        {{ number_format((float) $order->shipping, 2) }}
                    @endif
                </td>
            </tr>
            <tr class="final">
                <td>{{ __('messages.invoice.total_amount') }}:</td>
                <td class="value"><span class="currency">{{ $currency }}</span>
                    {{ number_format((float) $order->total, 2) }}</td>
            </tr>
        </table>

        <div class="invoice-footer">
            <p>{{ __('messages.invoice.footer') }}</p>
            <p>{{ __('messages.invoice.rights', ['year' => date('Y')]) }}</p>
        </div>
    </div>

</body>

</html>
