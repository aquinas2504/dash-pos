@extends('Component.main_admin')

@section('content')
    <div class="container mt-4">
        <h4 class="mb-3">History Retur Pembelian</h4>

        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Tanggal</th>
                            <th>Invoice Asal</th>
                            <th>Produk</th>
                            <th>Qty Retur</th>
                            <th>Supplier</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $i => $h)
                            <tr>
                                <td>{{ $histories->firstItem() + $i }}</td>
                                <td>{{ $h->date }}</td>
                                <td>{{ $h->invoice_number }}</td>
                                <td>{{ $h->product->product_name ?? '-' }}</td>
                                <td>{{ $h->qty_retur }} {{ $h->unit }}</td>
                                <td>{{ $h->supplier->supplier_name ?? $h->supplier_code }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">Belum ada data retur</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-3">
                    {{ $histories->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
