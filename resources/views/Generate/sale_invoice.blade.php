<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }

        h2 {
            margin: 0 0 -8 0;
            font-size: 20px;
        }

        .no-border-table td {
            border: none;
        }

        .bordered th {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .mt-20 {
            margin-top: 20px;
        }

        .mt-40 {
            margin-top: 40px;
        }
    </style>
</head>

<body>

    <!-- Header PT -->
    <div style="margin-top: -40px">
        <table class="no-border-table">
            @if ($invoice->suratjalan->ppn_status === 'yes')
                <tr>
                    <td width="30%">
                        {{-- <img src="/home/u836342820/domains/pos.dashmegah.my.id/public_html/img/logo-dmi.jpg" alt="Logo" style="height:75px; margin-top:10px;"> --}}
                        <img src="{{ public_path('img/logo-dmi.jpg') }}" alt="Logo"
                            style="height: 75px; margin-top:10px;">
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
                        {{-- <img src="/home/u836342820/domains/pos.dashmegah.my.id/public_html/img/logo-dmi.jpg" alt="Logo" style="height:75px;"> --}}
                        <img src="{{ public_path('img/logo-dmi.jpg') }}" alt="Logo" style="height: 75px;">
                    </td>
                </tr>
            @endif
        </table>
    </div>

    <hr>

    {{-- Tabel Customer & Invoice --}}
    <table>
        <tr>
            {{-- Kolom Customer --}}
            <td width="35%">
                <table style="width:100%; border-collapse: collapse; border: none;">
                    <tr>
                        <th style="text-align:left;" colspan="2">Customer:
                            {{ $invoice->suratjalan->customer->customer_name ?? '-' }}</th>
                    </tr>
                    <tr>
                        <td style="border: none;" colspan="2">{{ $invoice->suratjalan->customer->address ?? '-' }}
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Kolom Info Invoice --}}
            <td width="65%">
                <table style="width:100%; border-collapse: collapse;">
                    <tr>
                        <th class="header-cell">Keterangan</th>
                        <th class="header-cell">Detail</th>
                    </tr>
                    <tr>
                        <td>No Invoice</td>
                        <td>: {{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal Invoice</td>
                        <td>: {{ date('d-M-Y', strtotime($invoice->date)) }}</td>
                    </tr>
                    <tr>
                        <td>No Surat Jalan</td>
                        <td>: {{ $invoice->sj_number }}</td>
                    </tr>
                    <tr>
                        <td>No SO</td>
                        <td>: {{ $invoice->suratjalan->SJdetails->first()->so_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>TOP</td>
                        <td>: {{ $invoice->suratjalan->top ?: 0 }} Days</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Tabel Detail Barang -->
    <table class="bordered mt-20">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Quantity</th>
                <th>Harga / Unit</th>
                <th>Discount</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->details as $index => $detail)
                @php
                    $sjDetail = $detail->SJDetail;
                    $productName = $sjDetail->product->product_name ?? '-';
                    $quantity = ($sjDetail->qty_unit ?? 0) . ' ' . ($sjDetail->unit ?? '');
                    $discount = $detail->discount ?: 0;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $productName }}</td>
                    <td class="text-right">{{ $quantity }}</td>
                    <td class="text-left">{{ \App\Helpers\formatRp::rupiah($detail->price) }}</td>
                    <td class="text-center">{{ $discount }}%</td>
                    <td class="text-left">{{ \App\Helpers\formatRp::rupiah($detail->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Ringkasan Total -->
    <table class="bordered mt-20" style="width: 40%; float: right;">
        @if ($invoice->suratjalan->ppn_status === 'yes')
            <tr>
                <td><strong>DPP</strong></td>
                <td class="text-left">: <strong>{{ \App\Helpers\formatRp::rupiah($invoice->dpp) }}</strong></td>
            </tr>
            <tr>
                <td><strong>PPN</strong></td>
                <td class="text-left">: {{ \App\Helpers\formatRp::rupiah($invoice->ppn) }}</td>
            </tr>
        @endif
        <tr>
            <td><strong>Grand Total</strong></td>
            <td class="text-left">: <strong>{{ \App\Helpers\formatRp::rupiah($invoice->grandtotal) }}</strong></td>
        </tr>
    </table>

    <!-- Informasi Pembayaran -->
    <table class="no-border-table mt-20">
        <tr>
            <td width="50%">
                <strong>Informasi Pembayaran</strong><br>
                Bank: {{ $invoice->payment->bank_name ?? '-' }}<br>
                No Rekening: {{ $invoice->payment->rekening_number ?? '-' }}<br>
                Atas Nama: {{ $invoice->payment->rekening_name ?? '-' }}
            </td>
        </tr>
    </table>


    <!-- Tanda Tangan -->
    <div class="mt-40" style="clear: both;">
        <table class="no-border-table" width="100%">
            <tr>
                <td width="60%"></td>
                <td class="text-center">
                    Hormat Kami,<br><br><br><br><br>
                    @if ($invoice->suratjalan->ppn_status === 'yes')
                        <strong>PT. DASH MEGAH INTERNASIONAL</strong>
                    @else
                        <strong>Finance</strong>
                    @endif
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
