<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Invoice</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            color: #333;
            background-color: #f5f5f0;
            margin: 0;
            padding: 20px;
            text-transform: uppercase;
        }
        .header-table { width: 100%; margin-bottom: 25px; }
        .header-table td { vertical-align: top; }
        .logo { width: 300px; height: 60px; }
        .section-title {
            font-weight: 300;
            margin-bottom: 10px;
            margin-top: 20px;
            text-align: center;
            font-size: 40px;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            border-top: 1px solid #000000;
            border-bottom: 1px solid #000000;
            padding: 8px;
            background-color: #f5f5f0;
        }
        .summary-table th {
            border-top: none;
            border-bottom: 1px solid #000000;
        }
        .text-left { border-top: none; }
        th { font-weight: bold; }
        .summary-table {
            width: 45%;
            margin-top: 10px;
            float: right;
        }
        .summary-table th { text-align: left !important; }
        .summary-table td { padding: 6px 0; }
        .total-row { font-weight: bold; }
        .clearfix { clear: both; }
        .invoice-signatory {
            font-size: 14px;
            text-align: left;
            width: 100%;
            margin-top: 40px;
        }
        .text-gstt { border-bottom: none; }
        td.price-text { border-bottom: none; text-transform: capitalize; }
        th.price-text { border-bottom: none; }
    </style>
</head>
<body>

@php
    $imageUrl    = public_path('frontend/images/logo.png');
    $base64Image = base64_encode(file_get_contents($imageUrl));

    $pan_no = '';
    $gst_no =  '';

    $address = null;
    if (!empty($booking->address_json)) {
        $aj = $booking->address_json;
        $address = [
            'name'  => $aj['name']          ?? '',
            'text'  => ($aj['address']      ?? '') . ', ' .
                       ($aj['city']         ?? '') . ', ' .
                       ($aj['state']        ?? '') . ' - ' .
                       ($aj['pincode']      ?? ''),
            'phone' => $aj['phone_number']  ?? '',
        ];
    }
@endphp

{{-- ── HEADER ──────────────────────────────────────────────────────────── --}}
<table class="header-table" style="border:none">
    <tr style="border:none">
        <td style="border:none">
            <img src="data:image/png;base64,{{ $base64Image }}" class="logo">
        </td>
    </tr>
    <tr style="border:none">

        {{-- Left: provider + customer --}}
        <td style="border:none; text-align:left; width:60%">
            <strong>Service Provider:</strong><br>
            {{ $booking->provider->name ?? 'N/A' }}<br><br>

            @if($address)
                <strong>Customer:</strong><br>
                {{ ucfirst($address['name']) }}<br>
                {{ $address['text'] }}<br>
                @if($address['phone']) {{ $address['phone'] }} @endif
            @else
                <strong>Customer:</strong><br>
                {{ $booking->user->name ?? 'N/A' }}<br>
                {{ $booking->user->email ?? '' }}
            @endif
        </td>

        {{-- Right: booking meta --}}
        <td style="border:none; text-align:left; width:40%">
           

            <strong>Booking #:</strong><br>
            <strong>{{ $booking->booking_number }}</strong><br><br>

            <strong>Date:</strong> {{ $booking->created_at->format('d/m/Y') }}<br>

            @if($booking->slot_date)
                <strong>Slot Date:</strong> {{ $booking->slot_date->format('d/m/Y') }}<br>
            @elseif($booking->start_datetime)
                <strong>Start:</strong> {{ $booking->start_datetime->format('d/m/Y h:i A') }}<br>
            @endif

            @if($booking->slot_start_time)
                <strong>Slot Time:</strong>
                {{ $booking->slot_start_time }}
                @if($booking->slot_end_time) – {{ $booking->slot_end_time }} @endif
                <br>
            @endif

            @if($booking->total_hours)
                <strong>Total Hours:</strong> {{ $booking->total_hours }}<br>
            @endif

            <strong>Status:</strong> {{ ucwords(str_replace('_', ' ', $booking->status)) }}<br>
            <strong>Payment:</strong> {{ ucfirst($booking->payment_method ?? 'N/A') }}<br>

            @if($booking->is_recurring)
                <strong>Type:</strong> Recurring ({{ $booking->recurring_weeks }} weeks)<br>
            @else
                <strong>Type:</strong> {{ ucwords(str_replace('_', ' ', $booking->duration_type ?? 'N/A')) }}<br>
            @endif
        </td>

    </tr>
</table>

{{-- ── TITLE ────────────────────────────────────────────────────────────── --}}
<h3 class="section-title">INVOICE</h3>

{{-- ── ITEMS TABLE ──────────────────────────────────────────────────────── --}}
<table>
    <thead>
        <tr>
            <th width="5%">#</th>
            <th width="33%">Service Name</th>
            <th width="12%">Class</th>
            <th width="12%">Type</th>
            <th width="10%">Qty</th>
            <th width="14%">Unit Price</th>
            <th width="14%">Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($booking->items as $i => $item)
        <tr style="text-align:center">
            <td>{{ $i + 1 }}</td>
            <td>{{ $item->service_name }}</td>
            <td>{{ $item->class_name ?? '—' }}</td>
            <td>{{ ucfirst($item->type ?? '—') }}</td>
            <td>{{ $item->quantity }}</td>
            <td>INR {{ number_format($item->price, 2) }}</td>
            <td>INR {{ number_format($item->total_price, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align:center">No items found</td>
        </tr>
        @endforelse
    </tbody>
</table>

{{-- ── SUMMARY ──────────────────────────────────────────────────────────── --}}
<table class="summary-table">
    <tr>
        <th class="text-gstt">Subtotal</th>
        <td class="text-left">INR {{ number_format($booking->total_amount, 2) }}</td>
    </tr>

    @if($booking->discount > 0)
    <tr>
        <th>Discount</th>
        <td class="text-left">- INR {{ number_format($booking->discount, 2) }}</td>
    </tr>
    @endif

    @if($booking->tax > 0)
    <tr>
        <th class="text-gstt">Tax</th>
        <td class="text-left">INR {{ number_format($booking->tax, 2) }}</td>
    </tr>
    @endif

    <tr class="total-row">
        <th>Amount Paid</th>
        <td class="text-left">INR {{ number_format($booking->payable_amount, 2) }}</td>
    </tr>
    <tr>
        <th class="price-text"></th>
        <td class="price-text">Prices are inclusive of taxes</td>
    </tr>
</table>

<div class="clearfix"></div>

<div class="invoice-signatory">
    <span style="display:block; text-align:right; margin-top:20px;">
        This is a computer generated invoice.
    </span>
</div>

</body>
</html>