@extends('Component.main_admin')

@section('content')
    <div class="container-fluid">

        <div class="card-header d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('SJ.CreateManual') }}" class="btn btn-sm btn-warning">
                <i class="fa fa-edit"></i> Create Manual Surat Jalan
            </a>
        </div>

        <div class="row">
            <div class="col">
                <div class="card border-primary" style="border-width: 2px;">
                    <div class="card-body">

                        <form method="GET" action="{{ route('sales.ordered') }}" class="mb-3">
                            <div class="row g-2 align-items-end">

                                {{-- Order Number --}}
                                <div class="col-md-3">
                                    <label class="form-label">No. SO :</label>
                                    <input type="text" name="order_number" class="form-control" placeholder="SO Number"
                                        value="{{ request('order_number') }}">
                                </div>

                                {{-- Customer --}}
                                <div class="col-md-3">
                                    <label class="form-label">Customer :</label>
                                    <input type="text" name="customer_name" class="form-control"
                                        placeholder="Customer Name" value="{{ request('customer_name') }}">
                                </div>

                                {{-- Date --}}
                                <div class="col-md-4">
                                    <label class="form-label">Date :</label>
                                    <div class="input-group">
                                        <span class="input-group-text">From</span>
                                        <input type="date" name="date_from" class="form-control"
                                            value="{{ request('date_from') }}">

                                        <span class="input-group-text">To</span>
                                        <input type="date" name="date_to" class="form-control"
                                            value="{{ request('date_to') }}">
                                    </div>
                                </div>

                                {{-- Submit --}}
                                <div class="col-md-2 mt-2">
                                    <button class="btn btn-primary">
                                        <i class="fas fa-filter"></i> Filter
                                    </button>

                                    <a href="{{ route('sales.ordered') }}" class="btn btn-secondary">
                                        Reset
                                    </a>
                                </div>
                            </div>


                            {{-- STATUS RADIO --}}
                            <div class="mt-3 d-flex gap-3">
                                @php
                                    $status = request('status', 'All');
                                @endphp

                                @foreach (['All', 'Pending', 'Sebagian Terproses', 'Closed'] as $item)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status"
                                            value="{{ $item }}" id="status_{{ $item }}"
                                            {{ $status === $item ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_{{ $item }}">
                                            {{ $item }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>


                        </form>

                        <div class="alert alert-info d-flex justify-content-between align-items-center">
                            <strong>Total Sisa :</strong>
                            <span class="fw-bold text-danger">
                                Rp {{ number_format($totalSisa) }}
                            </span>
                        </div>


                        <table class="table table-bordered table-hover">
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
