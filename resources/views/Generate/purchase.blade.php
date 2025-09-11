<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $purchase->order_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 13px;
        }

        .info-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 11px;
        }

        .info-po {
            font-size: 13px;
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
            margin-top: 50px;
            width: 100%;
        }

        .signature-box {
            width: 200px;
            /* atur sesuai kebutuhan */
            margin-left: auto;
            /* dorong ke kanan */
            text-align: center;
            /* isi teks rata tengah */
        }


        .no-border-table,
        .no-border-table td,
        .no-border-table th {
            border: none !important;
        }

        .product-name {
            word-break: break-word;
            white-space: normal;
            text-align: center;
            max-width: 125px;
        }
    </style>
</head>

<body>

    <h1 style="text-align: center; margin-top: 0px;">PURCHASE ORDER</h1>

    <hr>

    <table class="no-border-table" width="100%">
        @if ($purchase->ppn_status === 'yes')
            {{-- Versi dengan alamat, logo kiri + alamat kanan --}}
            <tr>
                <td width="30%">
                    <img src="{{ asset('img/logo-dmi.jpg') }}" alt="Logo" style="height: 75px;">
                </td>
                <td style="text-align: center; font-size: 11px; line-height: 1.4;">
                    <h2 style="margin: 0 0 5px; font-size: 14px;">PT. DASH MEGAH INTERNASIONAL</h2>
                    JL. PERUM DELTA SARI BARU KOMPLEK DELTA ASRI NO 21, <br>
                    NGINGAS, WARU - SIDOARJO<br>
                    TLP.: 031-85530240 / 0818-0307-5728<br>
                    NPWP : 61-345-357-6-043-000<br>
                    SURABAYA - JAWA TIMUR
                </td>
            </tr>
        @else
            {{-- Versi tanpa alamat, logo di tengah --}}
            <tr>
                <td style="text-align: center;">
                    <img src="{{ asset('img/logo-dmi.jpg') }}" alt="Logo" style="height: 75px;">
                </td>
            </tr>
        @endif
    </table>


    <hr>

    <table class="no-border-table" width="100%" style="font-size: 11px; margin-bottom: 20px;">
        <tr>
            <td class="info-po" width="75%" valign="top">
                <strong>Kepada Yth:</strong><br>
                {{ $purchase->supplier->supplier_name ?? '-' }}<br>
                {{ $purchase->supplier->address ?? '-' }}
            </td>
            <td class="info-po" width="25%" valign="top">
                <strong>Number</strong>: {{ $purchase->order_number }} <br>
                <strong>Tanggal</strong>: {{ \Carbon\Carbon::parse($purchase->order_date)->format('d/M/Y') }}
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
                    <td>{{ $detail->qty_packing }} {{ $detail->packing }}</td>
                    <td>{{ $detail->qty_unit }} {{ $detail->unit }}</td>
                    <td>{{ \App\Helpers\formatRp::rupiah($detail->price) }}</td>
                    <td>{{ $detail->discount ?? '0' }} %</td>
                    <td>{{ \App\Helpers\formatRp::rupiah($detail->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="footer-table" width="100%" style="margin-top: 15px;">
        <tr>
            <td width="60%" class="text-left" style="word-break: break-word; vertical-align: top;">
                <strong>Note:</strong><br>
                {{ $purchase->note ?? '-' }}
            </td>
            <td width="40%" class="text-right" valign="top">
                <strong>Grand Total:</strong> {{ \App\Helpers\formatRp::rupiah($purchase->grandtotal) }}
            </td>
        </tr>
    </table>

    <div class="sign-section">
        <table class="no-border-table"  width="100%" style="margin-top: 50px; text-align: center;">
            <tr>
                <td width="50%">
                    <strong>Supplier</strong><br><br><br><br>
                    (____________________)<br>
                    Cap & Tanda Tangan
                </td>
                <td width="50%">
                    <strong>Pemohon</strong><br><br><br><br>
                    (____________________)<br>
                    Cap & Tanda Tangan
                </td>
            </tr>
        </table>
    </div>



</body>

</html>
