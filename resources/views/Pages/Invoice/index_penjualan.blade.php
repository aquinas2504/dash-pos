@extends('Component.main_admin')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col">
            <div class="card border-primary" style="border-width: 2px;">
                <div class="card-body">
                    <table class="table table-bordered mt-3">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Invoice Number</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Surat Jalan</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $invoice)
                                <tr>
                                    <td>{{ ($invoices->currentPage() - 1) * $invoices->perPage() + $loop->iteration }}</td>
                                    <td>{{ $invoice->invoice_number }}</td>
                                    <td>{{ \Carbon\Carbon::parse($invoice->date)->format('d/m/Y') }}</td>
                                    <td>{{ $invoice->suratJalan->customer->customer_name ?? '-' }}</td>
                                    <td>{{ $invoice->sj_number }}</td>
                                    <td>
                                        @php
                                            $invoice = \App\Models\SaleInvoice::where(
                                                'sj_number',
                                                $invoice->sj_number,
                                            )->first();
                                        @endphp
                                        @if ($invoice)
                                            <a href="{{ route('saleInvoice.Print', $invoice->invoice_number) }}"
                                                class="btn btn-sm btn-danger" target="_blank">
                                                <i class="fa fa-file-pdf"></i> Invoice
                                            </a>
                                        @endif

                                        <a href="{{ route('invoiceSJ.edit', $invoice->invoice_number) }}"
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
