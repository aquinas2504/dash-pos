<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Retur Purchase {{ $retur->retur_number }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
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
            text-align: left;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h3>Retur Purchase</h3>
    </div>

    <p><strong>Retur Number:</strong> {{ $retur->retur_number }}</p>
    <p><strong>Date:</strong> {{ $retur->date }}</p>
    <p><strong>Supplier:</strong> {{ $retur->supplier->supplier_name ?? '-' }}</p>
    <p><strong>Note:</strong> {{ $retur->note ?? '-' }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Produk</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Discount</th>
                <th>Value</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($retur->details as $i => $detail)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $detail->product->product_name ?? '-' }}</td>
                    <td>{{ $detail->qty }} {{ $detail->unit }}</td>
                    <td class="text-right">{{ \App\Helpers\formatRp::rupiah($detail->price) }}</td>
                    <td class="text-right">{{ $detail->discount }}%</td>
                    <td class="text-right">{{ \App\Helpers\formatRp::rupiah($detail->value) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="5" class="text-right fw-bold">Total</td>
                <td class="text-right fw-bold">{{ \App\Helpers\formatRp::rupiah($totalValue) }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
