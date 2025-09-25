@extends('Component.main_admin')


@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card-header d-flex" style="margin-bottom: 10px">
        <a href="{{ route('customers.create') }}" class="btn btn-sm btn-primary">
            <i class="fa fa-plus-square"></i> Add Customer
        </a>
    </div>

    <div class="row">
        <div class="col">
            <div class="card border-primary" style="border-width: 2px;">

                <div class="row mb-3">
                    <div class="col">
                        <form method="GET" action="{{ route('customers.index') }}" class="d-flex">

                            <div class="input-group w-50 mt-2 ml-2">
                                <input type="text" name="search" class="form-control border-secondary"
                                    style="border-radius: 10px" placeholder="Search..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary ml-3"
                                    style="border-radius: 10px">Search</button>
                                <a href="{{ route('customers.index') }}" class="btn btn-secondary ml-2"
                                    style="border-radius: 10px">Reset</a>
                            </div>

                        </form>
                    </div>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>PIC</th>
                                <th>NPWP</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($customers as $customer)
                                <tr>
                                    <td>{{ ($customers->currentPage() - 1) * $customers->perPage() + $loop->iteration }}</td>
                                    <td>{{ $customer->customer_name }}</td>
                                    <td>{{ $customer->customer_phone ?? "-" }}</td>
                                    <td>{{ $customer->pic ?? "-" }}</td>
                                    <td>{{ $customer->npwp ?? "-" }}</td>
                                    <td>
                                        <a href="{{ route('customers.edit', $customer->customer_code) }}" class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $customers->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
