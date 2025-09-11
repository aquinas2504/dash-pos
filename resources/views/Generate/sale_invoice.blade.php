<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .no-border-table td {
            border: none;
        }

        .bordered th,
        .bordered td {
            border: 1px solid #000;
            padding: 5px;
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
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
    <table class="no-border-table">
        @if ($invoice->suratjalan->ppn_status === 'yes')
            <tr>
                <td width="30%">
                    <img src="/home/u836342820/domains/pos.dashmegah.my.id/public_html/img/logo-dmi.jpg" alt="Logo" style="height:75px;">
                </td>
                <td class="text-center" style="font-size: 11px; line-height: 1.4;">
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
                    <img src="/home/u836342820/domains/pos.dashmegah.my.id/public_html/img/logo-dmi.jpg" alt="Logo" style="height:75px;">
                </td>
            </tr>
        @endif
    </table>

    <hr>

    <!-- Informasi Umum -->
    <table class="no-border-table mt-20">
        <tr>
            <td width="50%">
                <strong>Customer</strong><br>
                {{ $invoice->suratjalan->customer->customer_name ?? '-' }}<br>
                {{ $invoice->suratjalan->customer->address ?? '-' }}
            </td>
            <td>
                <table class="no-border-table">
                    <tr>
                        <td><strong>No Invoice</strong></td>
                        <td>: {{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tanggal Invoice</strong></td>
                        <td>: {{ date('d-M-Y', strtotime($invoice->date)) }}</td>
                    </tr>
                    <tr>
                        <td><strong>No Surat Jalan</strong></td>
                        <td>: {{ $invoice->sj_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>No SO</strong></td>
                        <td>: {{ $invoice->suratjalan->SJdetails->first()->so_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>TOP</strong></td>
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
                    <td class="text-center">{{ $quantity }}</td>
                    <td class="text-right">{{ \App\Helpers\formatRp::rupiah($detail->price) }}</td>
                    <td class="text-right">{{ $discount }}%</td>
                    <td class="text-right">{{ \App\Helpers\formatRp::rupiah($detail->total) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Ringkasan Total -->
    <table class="no-border-table mt-20" style="width: 40%; float: right;">
        <tr>
            <td><strong>DPP</strong></td>
            <td class="text-right">
                {{ \App\Helpers\formatRp::rupiah($invoice->suratjalan->ppn_status == 'yes' ? $invoice->dpp : $invoice->grandtotal) }}
            </td>
        </tr>
        <tr>
            <td><strong>PPN</strong></td>
            <td class="text-right">
                {{ \App\Helpers\formatRp::rupiah($invoice->suratjalan->ppn_status == 'yes' ? $invoice->ppn : 0) }}
            </td>
        </tr>
        <tr>
            <td><strong>Potongan Retur</strong></td>
            <td class="text-right">
                {{ \App\Helpers\formatRp::rupiah($invoice->retur_used) }}
            </td>
        </tr>
        <tr>
            <td><strong>Grand Total</strong></td>
            <td class="text-right">
                {{ \App\Helpers\formatRp::rupiah($invoice->grandtotal) }}
            </td>
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
                        <strong>Admin</strong>
                    @endif
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
