@extends('Component.main_admin')

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card-header d-flex" style="margin-bottom: 10px">
        <a href="{{ route('penerimaan.create.manual') }}" class="btn btn-sm btn-warning">
            <i class="fa fa-edit"></i> Create Manual Penerimaan
        </a>
    </div>

    <div class="row">
        <div class="col">
            <div class="card border-primary" style="border-width: 2px;">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>No. Penerimaan</th>
                                <th>No. PO</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($penerimaans as $penerimaan)
                                @php
                                    $firstDetail = $penerimaan->details->first();
                                    $poNumber = $firstDetail?->po_number ?? '-';
                                    $supplierName = $penerimaan->supplier?->supplier_name ?? '-';
                                @endphp

                                <tr>
                                    <td>{{ ($penerimaans->currentPage() - 1) * $penerimaans->perPage() + $loop->iteration }}</td>
                                    <td>{{ $penerimaan->date }}</td>
                                    <td>{{ $penerimaan->penerimaan_number }}</td>
                                    <td>{{ $poNumber }}</td>
                                    <td>{{ $supplierName }}</td>
                                    <td>{{ $penerimaan->status }}</td>
                                    <td>
                                        @if ($penerimaan->status !== 'Difaktur')
                                            <a href="{{ route('invoice.create', $penerimaan->penerimaan_number) }}"
                                                class="btn btn-sm btn-success">
                                                <i class="fa fa-file-invoice"></i> Invoice
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $penerimaans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
