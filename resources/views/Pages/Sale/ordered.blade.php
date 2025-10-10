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
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Order Number</th>
                                <th>Order Date</th>
                                <th>Customer</th>
                                <th>Status Pesanan</th>
                                <th>Status Pengiriman</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orderedSalesPagination as $sale)
                                <tr>
                                    <td>{{ ($orderedSalesPagination->currentPage() - 1) * $orderedSalesPagination->perPage() + $loop->iteration }}
                                    </td>
                                    <td>{{ $sale['order_number'] }}</td>
                                    <td>{{ $sale['order_date'] }}</td>
                                    <td>{{ $sale['customer_name'] }}</td>
                                    <td>{!! $sale['status_pesanan'] !!}</td>
                                    <td>{{ $sale['status_pengiriman'] }}</td>
                                    <td>
                                        <a href="{{ route('sale.pdf', $sale['order_number']) }}"
                                            class="btn btn-sm btn-danger" target="_blank" title="Print Sale Order">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <a href="{{ route('sales.details', $sale['order_number']) }}"
                                            class="btn btn-sm btn-primary" title="Create Surat Jalan">
                                            <i class="fa fa-truck"></i>
                                        </a>
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
@endsection
