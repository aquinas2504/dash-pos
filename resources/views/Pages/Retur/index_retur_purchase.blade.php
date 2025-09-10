@extends('Component.main_admin')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Daftar Retur Purchase</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Retur Number</th>
                        <th>Supplier</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($returs as $i => $retur)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $retur->date }}</td>
                            <td>{{ $retur->retur_number }}</td>
                            <td>{{ $retur->supplier->supplier_name ?? '-' }}</td>
                            <td>
                                <a href="{{ route('retur-purchase.pdf', $retur->retur_number) }}" target="_blank" class="btn btn-sm btn-danger">
                                    Generate PDF
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    @if($returs->isEmpty())
                        <tr>
                            <td colspan="5" class="text-center">Belum ada retur purchase</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
