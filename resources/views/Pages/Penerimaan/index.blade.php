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

                    <form method="GET" action="{{ route('penerimaans.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">

                            {{-- Search Penerimaan Number --}}
                            <div class="col-md-2">
                                <label class="form-label">No. Penerimaan</label>
                                <input type="text" name="penerimaan_number" class="form-control"
                                    value="{{ request('penerimaan_number') }}">
                            </div>

                            {{-- Search PO Number --}}
                            <div class="col-md-2">
                                <label class="form-label">No. PO</label>
                                <input type="text" name="po_number" class="form-control"
                                    value="{{ request('po_number') }}">
                            </div>

                            {{-- Search Supplier --}}
                            <div class="col-md-2">
                                <label class="form-label">Supplier</label>
                                <input type="text" name="supplier_name" class="form-control"
                                    value="{{ request('supplier_name') }}">
                            </div>

                            {{-- Date Range --}}
                            <div class="col-md-2">
                                <label class="form-label">Date From</label>
                                <input type="date" name="date_from" class="form-control"
                                    value="{{ request('date_from') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Date To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>

                            {{-- Status --}}
                            <div class="col-md-2">
                                <label class="form-label d-block">Status</label>

                                @php $status = request('status', 'All'); @endphp

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" value="All"
                                        {{ $status === 'All' ? 'checked' : '' }}>
                                    <label class="form-check-label">All</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" value="Pending"
                                        {{ $status === 'Pending' ? 'checked' : '' }}>
                                    <label class="form-check-label">Pending</label>
                                </div>

                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" value="Difaktur"
                                        {{ $status === 'Difaktur' ? 'checked' : '' }}>
                                    <label class="form-check-label">Difaktur</label>
                                </div>
                            </div>

                            {{-- Button --}}
                            <div class="col-md-12 mt-2">
                                <button class="btn btn-primary btn-sm">
                                    <i class="fa fa-filter"></i> Filter
                                </button>

                                <a href="{{ route('penerimaans.index') }}" class="btn btn-secondary btn-sm">
                                    Reset
                                </a>
                            </div>

                        </div>
                    </form>

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
                                    <td>{{ ($penerimaans->currentPage() - 1) * $penerimaans->perPage() + $loop->iteration }}
                                    </td>
                                    <td>{{ $penerimaan->date }}</td>
                                    <td>{{ $penerimaan->penerimaan_number }}</td>
                                    <td>{{ $poNumber }}</td>
                                    <td>{{ $supplierName }}</td>
                                    <td>{{ $penerimaan->status }}</td>
                                    <td>
                                        @if ($penerimaan->status !== 'Difaktur')
                                            <a href="{{ route('invoice.create', urlencode($penerimaan->penerimaan_number)) }}"
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
