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
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Code</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>Status</th>
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
                                    <td>
                                        <a href="{{ route('purchase.pdf', $purchase->order_number) }}"
                                            class="btn btn-sm btn-danger" target="_blank" title="Print PDF">
                                            <i class="fas fa-file-pdf"></i> PDF
                                        </a>

                                        @php
                                            $hasSO = $purchase->purchaseDetail->contains(function ($detail) {
                                                return !is_null($detail->so_detail);
                                            });
                                        @endphp

                                        @if ($hasSO)
                                            <a href="{{ route('purchase-grouped.pdf', $purchase->order_number) }}"
                                                class="btn btn-sm btn-danger" target="_blank" title="Print PDF Grouped">
                                                <i class="fas fa-file-pdf"></i> PDF Grouped
                                            </a>
                                        @endif

                                        <a href="{{ route('penerimaan.create.fromPO', $purchase->order_number) }}"
                                            class="btn btn-sm btn-success" title="Terima">
                                            <i class="fas fa-truck-loading"></i> Terima
                                        </a>
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
@endsection
