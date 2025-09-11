<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Surat Jalan - {{ $sj->sj_number }}</title>
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

        .no-border-table td {
            border: none;
            padding: 2px;
            vertical-align: top;
        }

        .detail-table th,
        .detail-table td {
            border: 1px solid black;
            padding: 5px;
            text-align: center;
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        .mt-5 {
            margin-top: 8rem;
        }

        .signature {
            width: 30%;
            text-align: center;
        }

        h2 {
            margin: 0;
            font-size: 14px;
        }

        .space-lg {
            height: 100px;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <table class="no-border-table">
        @if ($sj->ppn_status === 'yes')
            <tr>
                <td width="30%">
                    <img src="/home/u836342820/domains/pos.dashmegah.my.id/public_html/img/logo-dmi.jpg" alt="Logo" style="height:75px;">
                </td>
                @if ($sj->ppn_status === 'yes')
                    <td style="text-align: center; font-size: 11px; line-height: 1.4;">
                        <h2>PT. DASH MEGAH INTERNASIONAL</h2>
                        JL. PERUM DELTA SARI BARU KOMPLEK DELTA ASRI NO 21,<br>
                        NGINGAS, WARU - SIDOARJO<br>
                        TLP.: 031-85530240 / 0818-0307-5728<br>
                        NPWP : 61-345-357-6-043-000<br>
                        SURABAYA - JAWA TIMUR
                    </td>
                @endif
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

    <hr style="margin-top: 5px; margin-bottom: 5px;">

    {{-- Info Surat Jalan dan Ekspedisi --}}
    <table class="no-border-table mt-3">
        <tr>
            {{-- Kiri: Info SJ dan Customer --}}
            <td width="50%">
                <table class="no-border-table">
                    <tr>
                        <td width="40%">No. Surat Jalan</td>
                        <td>: {{ $sj->sj_number }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: {{ \Carbon\Carbon::parse($sj->ship_date)->format('d/M/Y') }}</td>
                    </tr>
                    <tr>
                        <td>No. SO</td>
                        <td>: {{ $sj->SJdetails->first()->so_number ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Customer</td>
                        <td>: {{ $sj->customer->customer_name }}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: {{ $sj->customer->address }}</td>
                    </tr>
                </table>
            </td>

            {{-- Kanan: Info Ekspedisi --}}
            <td width="50%">
                <table class="no-border-table">
                    <tr>
                        <td width="40%">Ekspedisi 1</td>
                        <td>: {{ $sj->ship_1 ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Alamat Ekspedisi 1</td>
                        <td>: {{ $sj->shipping1->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Ekspedisi 2</td>
                        <td>: {{ $sj->ship_2 ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Alamat Ekspedisi 2</td>
                        <td>: {{ $sj->shipping2->address ?? '-' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Tabel Barang --}}
    <table class="detail-table mt-4">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Packing</th>
                <th>Unit</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sj->SJdetails as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->product?->product_name ?? '-' }}</td>
                    <td>{{ $detail->qty_packing }} {{ $detail->packing }}</td>
                    <td>{{ $detail->qty_unit }} {{ $detail->unit }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Catatan --}}
    @if ($sj->note)
        <p class="mt-3"><strong>Catatan:</strong><br>{{ $sj->note }}</p>
    @endif

    {{-- Tanda Tangan --}}
    <div class="mt-5"></div>
    <table class="no-border-table">
        <tr>
            <td class="signature">Penerima<br><br><br><br><br><br>
            (____________________)<br>
            Tanda Tangan</td>
            <td class="signature">Pengantar<br><br><br><br><br><br>
            (____________________)<br>
            Tanda Tangan</td>
            <td class="signature">Pengirim<br><br><br><br><br><br>
            (____________________)<br>
            Tanda Tangan</td>
        </tr>
        <tr>
            <td colspan="3" class="space-lg"></td>
        </tr>
    </table>
</body>

</html>
