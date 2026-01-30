@extends('Component.main_admin')

@section('content')
    <div class="container-fluid">

        <div class="card-header d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('SJ.CreateManual') }}" class="btn btn-sm btn-warning">
                <i class="fa fa-edit"></i> Create Manual Surat Jalan
            </a>
        </div>

        <div class="card border-primary" style="border-width: 2px;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Ordered Sales List</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">

                    <form method="GET" class="row g-2 mb-3">

                        <div class="col-md-3">
                            <input type="text" name="order_number" class="form-control" placeholder="Order Number"
                                value="{{ request('order_number') }}">
                        </div>

                        <div class="col-md-3">
                            <input type="text" name="customer_name" class="form-control" placeholder="Customer Name"
                                value="{{ request('customer_name') }}">
                        </div>

                        <div class="col-md-4">
                            <div class="input-group">
                                <span class="input-group-text">From</span>
                                <input type="date" name="date_from" class="form-control"
                                    value="{{ request('date_from') }}">

                                <span class="input-group-text">To</span>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="col-md-1 d-grid">
                            <button class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>

                        <div class="col-md-1 d-grid">
                            <a href="{{ route('sales.ordered') }}" class="btn btn-secondary btn-sm mt-2">
                                Reset
                            </a>
                        </div>

                        <div class="col-12 mt-2">
                            @foreach (['All', 'Pending', 'Sebagian Terproses', 'Closed'] as $status)
                                <label class="me-3">
                                    <input type="radio" name="status" value="{{ $status }}"
                                        {{ request('status', 'All') === $status ? 'checked' : '' }}>
                                    {{ $status }}
                                </label>
                            @endforeach
                        </div>

                    </form>

                    <div class="alert alert-info d-flex justify-content-between align-items-center">
                        <strong>Total Sisa :</strong>
                        <span class="fw-bold text-danger">
                            Rp {{ number_format($totalSisa) }}
                        </span>
                    </div>


                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Order Number</th>
                                <th>Order Date</th>
                                <th>Customer</th>
                                <th>Status Pesanan</th>
                                <th>Status Pengiriman</th>
                                <th>Sisa</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orderedSalesPagination as $sale)
                                @php
                                    $saleModel = $sale['sale_model'];
                                @endphp
                                <tr>
                                    <td>{{ ($orderedSalesPagination->currentPage() - 1) * $orderedSalesPagination->perPage() + $loop->iteration }}
                                    </td>
                                    <td>{{ $sale['order_number'] }}</td>
                                    <td>{{ $sale['order_date'] }}</td>
                                    <td>{{ $sale['customer_name'] }}</td>
                                    <td>{!! $sale['status_pesanan'] !!}</td>
                                    <td>{{ $sale['status_pengiriman'] }}</td>
                                    <td
                                        class="text-end fw-bold {{ $saleModel->sisa_harga > 0 ? 'text-danger' : 'text-success' }}">
                                        Rp {{ number_format($saleModel->sisa_harga) }}
                                    </td>
                                    <td>
                                        <a href="{{ route('sale.pdf', $sale['order_number']) }}"
                                            class="btn btn-sm btn-danger" target="_blank" title="Print Sale Order">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="{{ route('sales.details', $sale['order_number']) }}"
                                            class="btn btn-sm btn-primary" title="Create Surat Jalan">
                                            <i class="fa fa-truck"></i>
                                        </a>
                                        <a href="{{ route('sales.edit', $sale['order_number']) }}"
                                            class="btn btn-sm btn-warning" title="Edit Sales Order">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <!-- Tombol delete (TERLIHAT) -->
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDelete('{{ $sale['order_number'] }}')">
                                            <i class="fa fa-trash"></i>
                                        </button>

                                        <!-- Form hidden (TIDAK ADA TOMBOL DI DALAMNYA) -->
                                        <form id="delete-form-{{ $sale['order_number'] }}"
                                            action="{{ route('sales.delete', $sale['order_number']) }}" method="POST"
                                            style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $orderedSalesPagination->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmDelete(orderNumber) {
            Swal.fire({
                title: 'Hapus Sales Order?',
                text: "Data ini akan hilang permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + orderNumber).submit();
                }
            });
        }
    </script>

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Gagal!',
                    text: "{{ session('error') }}",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif
@endsection
