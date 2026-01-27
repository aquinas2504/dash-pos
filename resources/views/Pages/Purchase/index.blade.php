@extends('Component.main_admin')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card-header d-flex justify-content-between align-items-center mb-3">
        <div>
            <a href="{{ route('purchases.create') }}" class="btn btn-sm btn-primary mr-2">
                <i class="fa fa-plus-square"></i> Add P.O Order
            </a>
            <a href="{{ route('penerimaan.create.manual') }}" class="btn btn-sm btn-warning">
                <i class="fa fa-edit"></i> Create Manual Penerimaan
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="card border-primary" style="border-width: 2px;">
                <div class="card-body">

                    <form method="GET" action="{{ route('purchases.index') }}" class="mb-3">
                        <div class="row g-2">

                            {{-- Order Number --}}
                            <div class="col-md-3">
                                <input type="text" name="order_number" class="form-control" placeholder="Order Number"
                                    value="{{ request('order_number') }}">
                            </div>

                            {{-- Supplier --}}
                            <div class="col-md-3">
                                <input type="text" name="supplier" class="form-control" placeholder="Supplier Name"
                                    value="{{ request('supplier') }}">
                            </div>

                            <div class="col-md-4">
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
                            <div class="col-md-1 d-grid">
                                <button class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                            </div>

                            <div class="col-md-1 d-grid">
                                <a href="{{ route('purchases.index') }}" class="btn btn-secondary btn-sm mt-2">
                                    Reset
                                </a>
                            </div>
                        </div>

                        {{-- STATUS RADIO --}}
                        <div class="mt-3 d-flex gap-3">
                            @php
                                $status = request('status', 'All');
                            @endphp

                            @foreach (['All', 'Pending', 'Diterima Sebagian', 'Diterima Semua'] as $item)
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

                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
                        <strong>Total Sisa Harga</strong>
                        <span class="fw-bold text-danger fs-5">
                            Rp {{ number_format($totalSisaHarga) }}
                        </span>
                    </div>


                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th>Sisa</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($purchases as $purchase)
                                <tr>
                                    <td>{{ ($purchases->currentPage() - 1) * $purchases->perPage() + $loop->iteration }}
                                    </td>
                                    <td>{{ $purchase->order_number }}</td>
                                    <td>{{ $purchase->order_date }}</td>
                                    <td>{{ $purchase->supplier->supplier_name ?? '-' }}</td>
                                    <td>{{ $purchase->status }}</td>
                                    <td
                                        class="text-end fw-bold 
                                        {{ $purchase->sisa_harga > 0 ? 'text-danger' : 'text-success' }}">
                                        Rp. {{ number_format($purchase->sisa_harga) }}
                                    </td>
                                    <td>

                                        @php
                                            $hasSO = $purchase->purchaseDetail->contains(function ($detail) {
                                                return !is_null($detail->so_detail);
                                            });
                                        @endphp

                                        @if ($hasSO)
                                            <a href="{{ route('purchase-grouped.pdf', urlencode($purchase->order_number)) }}"
                                                class="btn btn-sm btn-danger" target="_blank" title="Print PDF Grouped">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                        @else
                                            <a href="{{ route('purchase.pdf', urlencode($purchase->order_number)) }}"
                                                class="btn btn-sm btn-danger" target="_blank" title="Print PDF">
                                                <i class="fas fa-file-pdf"></i> PDF
                                            </a>
                                        @endif

                                        <a href="{{ route('penerimaan.create.fromPO', urlencode($purchase->order_number)) }}"
                                            class="btn btn-sm btn-success" title="Terima">
                                            <i class="fas fa-truck-loading"></i> Terima
                                        </a>

                                        <form action="{{ route('purchases.delete', urlencode($purchase->order_number)) }}"
                                            method="POST" class="d-inline delete-po-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-po">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>


                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No data available</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $purchases->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-delete-po').forEach(button => {
                button.addEventListener('click', function() {
                    const form = this.closest('form');

                    Swal.fire({
                        title: 'Hapus Purchase Order?',
                        text: 'Data PO akan dihapus permanen!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Hapus',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: "{{ session('error') }}",
                confirmButtonText: 'OK'
            });
        </script>
    @endif
@endsection
