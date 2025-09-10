@extends('Component.main_admin')

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card-header d-flex justify-content-between align-items-center"
        style="position: relative; margin-bottom: 10px;">
        <div>
            <a href="{{ route('payment.create') }}" class="btn btn-sm btn-primary mr-2">
                <i class="fa fa-plus-square"></i> Add Payment
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col">
            <div class="card border-primary" style="border-width: 2px;">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Rekening</th>
                                <th>No. Rekening</th>
                                <th>Bank</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($paymentspagination as $pay)
                                <tr>
                                    <td>{{ ($paymentspagination->currentPage() - 1) * $paymentspagination->perPage() + $loop->iteration }}</td>
                                    <td>{{ $pay->rekening_name }}</td>
                                    <td>{{ $pay->rekening_number }}</td>
                                    <td>{{ $pay->bank_name }}</td>
                                    <td>
                                        <a href="{{ route('payment.edit', $pay->id) }}"
                                            class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $paymentspagination->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
