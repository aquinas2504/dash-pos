<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Sale Order - {{ $sale->order_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0 20px;
        }

        .info-so {
            font-size: 13px;
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        h2 {
            margin: 0 0 -8 0;
            font-size: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #eee;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .footer-table td {
            border: none;
        }

        .sign-section {
            margin-top: 20px;
            width: 100%;
        }

        .signature-box {
            width: 200px;
            margin-left: auto;
            text-align: center;
        }

        .no-border-table,
        .no-border-table td,
        .no-border-table th {
            border: none !important;
        }

        .product-name {
            word-break: break-word;
            white-space: normal;
            text-align: left;
            max-width: 125px;
        }
    </style>
</head>

<body>

    <div style="margin-top: -40px">
        <table class="no-border-table">
            @if ($sale->ppn_status === 'yes')
                <tr>
                    <td width="30%">
                        {{-- <img src="/home/u836342820/domains/pos.dashmegah.my.id/public_html/img/logo-dmi.jpg" alt="Logo" style="height:75px; margin-top:10px;"> --}}
                        <img src="{{ public_path('img/logo-dmi.jpg') }}" alt="Logo" style="height: 75px; margin-top:10px;">
                    </td>
                    <td
                        style="text-align: center; font-size: 11px; line-height: 1.4;">
                        <u><h2>PT. DASH MEGAH INTERNASIONAL</h2></u><br>
                        JL. PERUM DELTA SARI BARU KOMPLEK DELTA ASRI NO 21,<br>
                        NGINGAS, WARU - SIDOARJO<br>
                        SURABAYA - JAWA TIMUR<br>
                        TLP.: 031-85530240 / 0818-0307-5728<br>
                        NPWP : 61-345-357-6-043-000 ;
                        <span style="color: blue;">Email: dashplastic@gmail.com</span>                 
                    </td>
                </tr>
            @else
                {{-- Versi tanpa alamat, logo di tengah --}}
                <tr>
                    <td style="text-align: center;">
                        {{-- <img src="/home/u836342820/domains/pos.dashmegah.my.id/public_html/img/logo-dmi.jpg" alt="Logo" style="height:75px;"> --}}
                        <img src="{{ public_path('img/logo-dmi.jpg') }}" alt="Logo" style="height: 75px;">
                    </td>
                </tr>
            @endif
        </table>
    </div>

    <hr>

    <h1 style="text-align: center;">SALE ORDER</h1>

    <hr>

    <table width="100%" style="font-size: 11px; margin: 10 0 10 0;">
        <tr>
            <td class="info-so" width="50%">
                <strong>Kepada Yth:</strong><br>
            </td>
            <td class="info-so" width="25%"><strong>No. SO:</strong></td>
            <td class="info-so" width="25%"><strong>Date:</strong></td>
        </tr>
        <tr>
            <td style="text-align: left"> 
                <strong>{{ $sale->customer->customer_name ?? '-' }}</strong><br>
                {{ $sale->customer->address ?? '-' }}
            </td>
            <td class="info-so" valign="top">{{ $sale->order_number }}</td>
            <td class="info-so" valign="top">{{ \Carbon\Carbon::parse($sale->order_date)->format('d/M/Y') }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Product Name</th>
                <th>Packing</th>
                <th>Quantity</th>
                <th>Price / Qty</th>
                <th>Discount</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sale->saleDetail as $index => $detail)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="product-name">{{ $detail->product->product_name ?? '-' }}</td>
                    <td class="text-right">{{ $detail->qty_packing }} {{ $detail->packing }}</td>
                    <td class="text-right">{{ $detail->quantity }} {{ $detail->unit }}</td>
                    <td class="text-left">{{ \App\Helpers\formatRp::rupiah($detail->price) }}</td>
                    <td class="text-center">{{ $detail->discount ?? '0%' }}</td>
                    <td class="text-left">{{ \App\Helpers\formatRp::rupiah($detail->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="footer-table" width="100%" style="margin-top: 10px;">
        <tr>
            <td width="60%" class="text-left" style="word-break: break-word; vertical-align: top;">
                <strong>Note:</strong><br>
                {{ $sale->note ?? '-' }}
            </td>
            <td width="40%" class="text-right" valign="top">
                <strong>Grand Total: {{ \App\Helpers\formatRp::rupiah($sale->grandtotal) }}</strong>
            </td>
        </tr>
    </table>

    <div class="sign-section">
        <div class="signature-box">
            <strong>Sales</strong><br><br><br><br>
            (____________________)<br>
            Bagian Penjualan
        </div>
    </div>

</body>

</html>
