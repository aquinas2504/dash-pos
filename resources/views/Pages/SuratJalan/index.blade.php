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

                                        <a href="{{ route('SJ.Print', urlencode($sj->sj_number)) }}" class="btn btn-sm btn-primary"
                                            target="_blank">
                                            <i class="fa fa-print"></i> Surat Jalan
                                        </a>

                                        @if($sj->status == 'Pending')
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
