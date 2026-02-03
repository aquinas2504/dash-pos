@extends('Component.main_admin')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col">
            <div class="card border-primary" style="border-width: 2px;">
                <div class="card-body">

                    <form method="GET" action="{{ route('purchaseInvoice.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">

                            {{-- Invoice Number --}}
                            <div class="col-md-3">
                                <label class="form-label">Invoice Number</label>
                                <input type="text" name="invoice_number" class="form-control"
                                    value="{{ request('invoice_number') }}">
                            </div>

                            {{-- Penerimaan Number --}}
                            <div class="col-md-3">
                                <label class="form-label">No. Penerimaan</label>
                                <input type="text" name="penerimaan_number" class="form-control"
                                    value="{{ request('penerimaan_number') }}">
                            </div>

                            {{-- Supplier --}}
                            <div class="col-md-3">
                                <label class="form-label">Supplier</label>
                                <input type="text" name="supplier_name" class="form-control"
                                    value="{{ request('supplier_name') }}">
                            </div>

                            {{-- Date Range --}}
                            <div class="col-md-1">
                                <label class="form-label">From</label>
                                <input type="date" name="date_from" class="form-control"
                                    value="{{ request('date_from') }}">
                            </div>

                            <div class="col-md-1">
                                <label class="form-label">To</label>
                                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                            </div>

                            {{-- Button --}}
                            <div class="col-md-12 mt-2">
                                <button class="btn btn-primary btn-sm">
                                    <i class="fa fa-filter"></i> Filter
                                </button>

                                <a href="{{ route('purchaseInvoice.index') }}" class="btn btn-secondary btn-sm">
                                    Reset
                                </a>
                            </div>

                        </div>
                    </form>

                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Invoice Number</th>
                                <th>Date</th>
                                <th>Supplier</th>
                                <th>No. Penerimaan</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td>{{ ($invoices->currentPage() - 1) * $invoices->perPage() + $loop->iteration }}</td>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d/m/Y') }}</td>
                                    <td>{{ $invoice->penerimaan->supplier->supplier_name ?? '-' }}</td>
                                    <td>{{ $invoice->penerimaan_number }}</td>
                                    <td>
                                        <a href="{{ route('invoices.purchase.edit', $invoice->invoice_number) }}"
                                            class="btn btn-sm btn-warning">
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">Belum ada data invoice.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $invoices->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
