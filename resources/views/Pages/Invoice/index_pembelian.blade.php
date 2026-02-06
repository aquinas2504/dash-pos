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
                            <div class="col-md-2">
                                <label class="form-label">No. Invoice :</label>
                                <input type="text" name="invoice_number" class="form-control" placeholder="Invoice Number"
                                    value="{{ request('invoice_number') }}">
                            </div>

                            {{-- Penerimaan Number --}}
                            <div class="col-md-2">
                                <label class="form-label">No. Penerimaan :</label>
                                <input type="text" name="penerimaan_number" class="form-control" placeholder="Penerimaan Number"
                                    value="{{ request('penerimaan_number') }}">
                            </div>

                            {{-- Supplier --}}
                            <div class="col-md-2">
                                <label class="form-label">Supplier :</label>
                                <input type="text" name="supplier_name" class="form-control" placeholder="Supplier Name"
                                    value="{{ request('supplier_name') }}">
                            </div>

                            {{-- Filter Date --}}
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

                            {{-- Button --}}
                            <div class="col-md-2 mt-2">
                                <button class="btn btn-primary">
                                    <i class="fa fa-filter"></i> Filter
                                </button>

                                <a href="{{ route('purchaseInvoice.index') }}" class="btn btn-secondary">
                                    Reset
                                </a>
                            </div>

                        </div>
                    </form>

                    <table class="table table-bordered table-hover">
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
                                    <td colspan="6" class="text-center">Belum ada data invoice.</td>
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
