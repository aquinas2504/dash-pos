@extends('Component.main_admin')

@section('content')
    <div class="card-header d-flex mb-2">
        <a href="{{ route('SJ.CreateManual') }}" class="btn btn-sm btn-warning">
            <i class="fa fa-edit"></i> Create Manual Surat Jalan
        </a>
    </div>

    <div class="row">
        <div class="col">
            <div class="card border-primary" style="border-width: 2px;">
                <div class="card-body">

                    <form method="GET" action="{{ route('pengirimans.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">

                            {{-- SJ Number --}}
                            <div class="col-md-2">
                                <label class="form-label">No. Surat Jalan</label>
                                <input type="text" name="sj_number" class="form-control"
                                    value="{{ request('sj_number') }}">
                            </div>

                            {{-- SO Number --}}
                            <div class="col-md-2">
                                <label class="form-label">No. SO</label>
                                <input type="text" name="so_number" class="form-control"
                                    value="{{ request('so_number') }}">
                            </div>

                            {{-- Customer --}}
                            <div class="col-md-2">
                                <label class="form-label">Customer</label>
                                <input type="text" name="customer_name" class="form-control"
                                    value="{{ request('customer_name') }}">
                            </div>

                            {{-- Date Range --}}
                            <div class="col-md-2">
                                <label class="form-label">Ship Date From</label>
                                <input type="date" name="date_from" class="form-control"
                                    value="{{ request('date_from') }}">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Ship Date To</label>
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

                                <a href="{{ route('pengirimans.index') }}" class="btn btn-secondary btn-sm">
                                    Reset
                                </a>
                            </div>

                        </div>
                    </form>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>No. Surat Jalan</th>
                                <th>No. SO</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suratJalans as $sj)
                                <tr>
                                    <td>{{ $sj->ship_date }}</td>
                                    <td>{{ $sj->sj_number }}</td>
                                    <td>{{ $sj->SJdetails->first()->so_number ?? '-' }}</td>
                                    <td>{{ $sj->customer->customer_name }}</td>
                                    <td>{{ $sj->status }}</td>
                                    <td>

                                        <a href="{{ route('SJ.Print', urlencode($sj->sj_number)) }}"
                                            class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fa fa-print"></i> Surat Jalan
                                        </a>

                                        @if ($sj->status == 'Pending')
                                            <a href="{{ route('invoice.createSJ', urlencode($sj->sj_number)) }}"
                                                class="btn btn-sm btn-success">
                                                <i class="fa fa-file-invoice"></i> Create Invoice
                                            </a>
                                        @endif

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $suratJalans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
