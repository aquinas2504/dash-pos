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
            margin-bottom: 10px;
        }

        table td,
        table th {
            border: 1px solid black;
            padding: 5px;
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
        }

        .mt-3 {
            margin-top: 1rem;
        }

        .mt-4 {
            margin-top: 1.5rem;
        }

        .signature {
            width: 30%;
            text-align: center;
        }

        h2 {
            margin: 0 0 -8 0;
            font-size: 20px;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <div style="margin-top: -40px;">
        <table class="no-border-table">
            @if ($sj->ppn_status === 'yes')
                <tr>
                    <td width="30%">
                        <img src="/home/u836342820/domains/pos.dashmegah.my.id/public_html/img/logo-dmi.jpg" alt="Logo" style="height:75px; margin-top:20px;">
                        {{-- <img src="{{ public_path('img/logo-dmi.jpg') }}" alt="Logo" style="height: 75px; margin-top:20px;"> --}}
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

    <hr style="margin-top: 5px; margin-bottom: 10px;">

    <table style="width:100%; border-collapse: collapse; margin-bottom:10px;">
        <tr>
            {{-- Kolom Customer --}}
            <td width="35%" style="vertical-align: top;">
                <table style="width:100%; border-collapse: collapse; border: none;">
                    <tr>
                        <th style="text-align:left;">
                            Customer: {{ $sj->customer->customer_name ?? '-' }}
                        </th>
                    </tr>
                    <tr>
                        <td style="border: none;">
                            {{ $sj->customer->address ?? '-' }}
                        </td>
                    </tr>
                </table>
            </td>

            {{-- Kolom Info Surat Jalan / No SO --}}
            <td width="65%" style="vertical-align: top;">
                <table style="width:100%; border-collapse: collapse; border:1px solid #000;">
                    <tr>
                        <th style="border:1px solid #000; padding:5px;">Keterangan</th>
                        <th style="border:1px solid #000; padding:5px;">Detail</th>
                    </tr>
                    <tr>
                        <td style="border:1px solid #000; padding:5px;">No. Surat Jalan</td>
                        <td style="border:1px solid #000; padding:5px;">: {{ $sj->sj_number }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #000; padding:5px;">Tanggal</td>
                        <td style="border:1px solid #000; padding:5px;">
                            : {{ \Carbon\Carbon::parse($sj->ship_date)->format('d/M/Y') }}</td>
                    </tr>
                    <tr>
                        <td style="border:1px solid #000; padding:5px;">No. SO</td>
                        <td style="border:1px solid #000; padding:5px;">:
                            {{ $sj->SJdetails->first()->so_number ?? '-' }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Table 3: Info Ekspedisi --}}
    <table style="width:100%; border-collapse: collapse; margin-bottom:10px;" border="1">
        @if (!$sj->shipping1)
            {{-- Ekspedisi 1 kosong --}}
            <tr>
                <td colspan="4" style="text-align:center; font-weight:bold;">Tidak Menggunakan Ekspedisi</td>
            </tr>
        @elseif($sj->shipping1 && !$sj->shipping2)
            {{-- Hanya Ekspedisi 1 --}}
            <tr>
                <th>Ekspedisi 1</th>
                <th>Alamat Ekspedisi 1</th>
            </tr>
            <tr>
                <td>{{ $sj->shipping1->shipping_name }}</td>
                <td>{{ $sj->shipping1->address }}</td>
            </tr>
        @elseif($sj->shipping1 && $sj->shipping2)
            {{-- Ekspedisi 1 & 2 --}}
            <tr>
                <th>Ekspedisi 1</th>
                <th>Alamat Ekspedisi 1</th>
                <th>To:</th>
                <th>Ekspedisi 2</th>
                <th>Alamat Ekspedisi 2</th>
            </tr>
            <tr>
                <td>{{ $sj->shipping1->shipping_name }}</td>
                <td>{{ $sj->shipping1->address }}</td>
                <td style="text-align: center">></td>
                <td>{{ $sj->shipping2->shipping_name }}</td>
                <td>{{ $sj->shipping2->address }}</td>
            </tr>
        @endif
    </table>

    <p>* {{ $sj->note_shipping ?? 'Tidak ada catatan pengiriman' }}</p>

    <hr style="margin-top: 5px; margin-bottom: 10px;">

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
                    <td style="text-align: center">{{ $index + 1 }}</td>
                    <td>{{ $detail->product_name ?? '-' }}</td>
                    <td style="text-align: right">{{ $detail->qty_packing }} {{ $detail->packing }}</td>
                    <td style="text-align: right">
                        {{ rtrim(rtrim(number_format($detail->qty_unit, 2, '.', ''), '0'), '.') }} {{ $detail->unit }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Catatan --}}
    @if ($sj->note)
        <p class="mt-3"><strong>Catatan:</strong><br>{{ $sj->note }}</p>
    @endif

    {{-- Tanda Tangan --}}
    <div class="mt-4"></div>
    <table class="no-border-table">
        <tr>
            <td class="signature">Penerima<br><br><br><br>
                (____________________)<br>
            </td>
            <td class="signature">Pengantar<br><br><br><br>
                (____________________)<br>
            </td>
            <td class="signature">Pengirim<br><br><br><br>
                (____________________)<br>
            </td>
        </tr>
    </table>
</body>

</html>
