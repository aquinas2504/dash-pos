<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $purchase->order_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0 20px;
        }

        .info-po {
            font-size: 13px;
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
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
            @if ($purchase->ppn_status === 'yes')
                <tr>
                    <td width="30%">
                        <img src="/home/u836342820/domains/pos.dashmegah.my.id/public_html/img/logo-dmi.jpg" alt="Logo" style="height:75px; margin-top:10px;">
                        {{-- <img src="{{ public_path('img/logo-dmi.jpg') }}" alt="Logo" style="height: 75px; margin-top:10px;"> --}}
                    </td>
                    <td style="text-align: center; font-size: 11px; line-height: 1.4;">
                        <u>
                            <h2>PT. DASH MEGAH INTERNASIONAL</h2>
                        </u><br>
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
                        <img src="/home/u836342820/domains/pos.dashmegah.my.id/public_html/img/logo-dmi.jpg" alt="Logo" style="height:75px;">
                        {{-- <img src="{{ public_path('img/logo-dmi.jpg') }}" alt="Logo" style="height: 75px;"> --}}
                    </td>
                </tr>
            @endif
        </table>
    </div>


    <hr>

    <h1 style="text-align: center;">PURCHASE ORDER</h1>

    <hr>

    <table width="100%" style="border-collapse: collapse; margin-top:10px;">
        <tr>
            {{-- Kiri: Supplier --}}
            <td width="50%" style="vertical-align: top;">
                <table style="width:100%; border-collapse: collapse; border: none;">
                    <tr>
                        <th style="text-align:left;">Kepada Yth:
                            {{ $purchase->supplier->supplier_name ?? '-' }}</th>
                    </tr>
                    <tr>
                        <td style="border: none; text-align: left">{{ $purchase->supplier->address ?? '-' }}</td>
                    </tr>
                </table>
            </td>

            {{-- Kanan: Keterangan PO --}}
            <td width="50%" style="vertical-align: top;">
                <table style="width:100%; border-collapse: collapse;">
                    <tr>
                        <th style="border: 1px solid #000; text-align:left;">Keterangan</th>
                        <th style="border: 1px solid #000; text-align:left;">Detail</th>
                    </tr>
                    <tr>
                        <td style="text-align: left">No. PO</td>
                        <td style="text-align: left">: {{ $purchase->order_number }}</td>
                    </tr>
                    <tr>
                        <td style="text-align: left">Tanggal</td>
                        <td style="text-align: left">:
                            {{ \Carbon\Carbon::parse($purchase->order_date)->format('d/M/Y') }}</td>
                    </tr>
                </table>
            </td>
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
            @foreach ($purchase->purchaseDetail as $index => $detail)
                @php
                    $productName =
                        $detail->saleDetail->product->product_name ?? ($detail->product->product_name ?? '-');
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="product-name">{{ $productName }}</td>
                    <td class="text-right">{{ $detail->qty_packing }} {{ $detail->packing }}</td>
                    <td class="text-right">{{ $detail->qty_unit }} {{ $detail->unit }}</td>
                    <td class="text-left">{{ \App\Helpers\formatRp::rupiah($detail->price) }}</td>
                    <td class="text-center">{{ $detail->discount ?? '0' }} %</td>
                    <td class="text-left">{{ \App\Helpers\formatRp::rupiah($detail->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table width="100%" style="margin-top: 10px; border-collapse: collapse;">
        <tr>
            <!-- Kolom Note -->
            <td width="60%" style="border: none; vertical-align: top; padding-right: 20px; text-align: left;">
                <strong>Note:</strong><br>
                {{ $purchase->note ?? '-' }}
            </td>

            <!-- Kolom Grand Total -->
            <td width="40%" style="border: none; vertical-align: top;">
                <!-- Table kecil hanya untuk Grand Total dengan border -->
                <table style="width: 100%; border: 1px solid #000; border-collapse: collapse;">
                    <tr>
                        <td style="border: 1px solid #000; padding: 5px;"><strong>Grand Total</strong></td>
                        <td style="border: 1px solid #000; padding: 5px; text-align: left;">
                            <strong>{{ \App\Helpers\formatRp::rupiah($purchase->grandtotal) }}</strong>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <div class="sign-section">
        <table class="no-border-table" width="100%" style="margin-top: 0px; text-align: center;">
            <tr>
                <td width="50%">
                    <strong>Supplier</strong><br><br><br><br>
                    (____________________)<br>
                    .
                </td>
                <td width="50%">
                    <strong>Pembeli</strong><br><br><br><br>
                    (____________________)<br>
                    Bagian Pembelian
                </td>
            </tr>
        </table>
    </div>



</body>

</html>
