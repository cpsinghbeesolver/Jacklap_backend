<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>@yield('title', 'Notification') — {{ config('app.name') }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background-color: #f2f4f8;
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #333333;
            padding: 24px 16px;
        }

        .wrapper {
            width: 100%;
            max-width: 620px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 16px rgba(0,0,0,0.08);
        }

        /* ── HEADER ── */
        .header {
            background: linear-gradient(135deg, #1B5E20 0%, #0a3d10 100%);
            padding: 28px 36px;
            text-align: center;
        }
        .header img.logo {
            height: 52px;
            margin-bottom: 12px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .header .company-name {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .header .tagline {
            color: #a5d6a7;
            font-size: 12px;
            letter-spacing: 0.3px;
        }

        /* ── CONTENT ── */
        .content {
            padding: 36px;
        }
        .content h2 {
            font-size: 20px;
            color: #1B5E20;
            margin-bottom: 12px;
        }
        .content p {
            font-size: 14px;
            line-height: 1.7;
            color: #444444;
            margin-bottom: 12px;
        }
        .content p:last-child {
            margin-bottom: 0;
        }

        /* ── DETAIL TABLE ── */
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin: 20px 0;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e0e6f0;
        }
        .detail-table tr:nth-child(odd) td {
            background-color: #f7f9fc;
        }
        .detail-table td {
            padding: 11px 14px;
            border-bottom: 1px solid #e0e6f0;
            vertical-align: top;
        }
        .detail-table td:first-child {
            font-weight: 600;
            color: #555555;
            white-space: nowrap;
            width: 38%;
        }
        .detail-table td:last-child {
            color: #222222;
        }
        .detail-table tr:last-child td {
            border-bottom: none;
        }

        /* ── BADGE ── */
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .badge-pending    { background:#fff3cd; color:#856404; }
        .badge-confirmed  { background:#d1ecf1; color:#0c5460; }
        .badge-in_progress{ background:#cce5ff; color:#004085; }
        .badge-start_journey{ background:#d4edda; color:#155724; }
        .badge-completed  { background:#d4edda; color:#155724; }
        .badge-cancelled  { background:#f8d7da; color:#721c24; }

        /* ── OTP BOX ── */
        .otp-box {
            text-align: center;
            margin: 28px 0;
            padding: 20px;
            background: #f1f8f1;
            border: 2px dashed #1B5E20;
            border-radius: 8px;
        }
        .otp-box .otp-code {
            font-size: 40px;
            font-weight: 800;
            letter-spacing: 14px;
            color: #1B5E20;
            line-height: 1.2;
        }
        .otp-box .otp-note {
            font-size: 12px;
            color: #888;
            margin-top: 8px;
        }

        /* ── ITEMS TABLE ── */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
            margin: 16px 0;
        }
        .items-table thead tr {
            background-color: #1B5E20;
            color: #ffffff;
        }
        .items-table thead td {
            padding: 10px 12px;
            font-weight: 600;
        }
        .items-table tbody tr:nth-child(even) td {
            background-color: #f7f9fc;
        }
        .items-table tbody td {
            padding: 9px 12px;
            border-bottom: 1px solid #e0e6f0;
            color: #333;
        }
        .items-table tfoot td {
            padding: 10px 12px;
            font-weight: 700;
            border-top: 2px solid #1B5E20;
        }
        .text-right { text-align: right; }

        /* ── ALERT BOX ── */
        .alert {
            padding: 14px 16px;
            border-radius: 6px;
            font-size: 13px;
            margin: 20px 0;
            line-height: 1.6;
        }
        .alert-warning { background:#fff3cd; border-left:4px solid #ffc107; color:#856404; }
        .alert-danger  { background:#f8d7da; border-left:4px solid #dc3545; color:#721c24; }
        .alert-info    { background:#d1ecf1; border-left:4px solid #17a2b8; color:#0c5460; }
        .alert-success { background:#d4edda; border-left:4px solid #28a745; color:#155724; }

        /* ── DIVIDER ── */
        .divider {
            border: none;
            border-top: 1px solid #e8ecf4;
            margin: 24px 0;
        }

        /* ── FOOTER ── */
        .footer {
            background-color: #f7f9fc;
            border-top: 1px solid #e0e6f0;
            padding: 24px 36px;
            text-align: center;
            font-size: 12px;
            color: #888888;
        }
        .footer .contact-row {
            margin-bottom: 8px;
            line-height: 2;
        }
        .footer a {
            color: #1B5E20;
            text-decoration: none;
        }
        .footer .socials {
            margin: 10px 0;
        }
        .footer .socials a {
            display: inline-block;
            margin: 0 6px;
            padding: 4px 10px;
            border: 1px solid #cccccc;
            border-radius: 4px;
            color: #555555;
            font-size: 11px;
        }
        .footer .legal {
            margin-top: 10px;
            font-size: 11px;
            color: #aaaaaa;
        }
        .footer .legal a {
            color: #aaaaaa;
        }
    </style>
</head>
<body>
<div class="wrapper">

    {{-- ══════════════════ HEADER ══════════════════ --}}
    <div class="header">
        {{-- Swap the src with your actual logo path --}}
        <img class="logo"
             src="{{ isset($message) ? $message->embed(public_path('assets/img/logo.png')) : asset('assets/img/logo.png') }}"
             alt="{{ config('app.name') }}">
        {{-- <div class="company-name">{{ config('app.name') }}</div> --}}
        <div class="tagline">Your trusted booking partner</div>
    </div>

    {{-- ══════════════════ BODY ══════════════════ --}}
    <div class="content">
        @yield('content')
    </div>

    {{-- ══════════════════ FOOTER ══════════════════ --}}
    <div class="footer">
        <div class="contact-row">
            📍 123 Business Street, City, Country
            &nbsp;·&nbsp;
            📞 <a href="tel:+1234567890">+1 234 567 890</a>
            &nbsp;·&nbsp;
            ✉️ <a href="mailto:admin@jacklap.ca">admin@jacklap.ca</a>
        </div>

        <div class="socials">
            <a href="#">Facebook</a>
            <a href="#">Twitter</a>
            <a href="#">Instagram</a>
        </div>

        <div class="legal">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            &nbsp;|&nbsp;
            <a href="#">Privacy Policy</a>
            &nbsp;|&nbsp;
            <a href="#">Terms of Service</a>
            {{-- &nbsp;|&nbsp;
            <a href="#">Unsubscribe</a> --}}
        </div>
    </div>

</div>
</body>
</html>