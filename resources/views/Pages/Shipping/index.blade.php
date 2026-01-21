@extends('Component.main_admin')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card-header d-flex align-items-center mb-3">

        {{-- Add Shipping --}}
        <a href="{{ route('shippings.create') }}" class="btn btn-sm btn-primary mr-3">
            <i class="fa fa-plus-square"></i> Add Shipping
        </a>

        {{-- Search --}}
        <form action="{{ route('shippings.index') }}" method="GET" class="form-inline">
            <div class="input-group" style="width: 280px;">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Cari nama / alamat..." value="{{ request('search') }}">

                <div class="input-group-append">
                    <button class="btn btn-sm btn-primary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            @if (request('search'))
                <a href="{{ route('shippings.index') }}" class="btn btn-sm btn-secondary ml-2">
                    Reset
                </a>
            @endif
        </form>

    </div>




    <div class="row">
        <div class="col">
            <div class="card border-primary" style="border-width: 2px;">
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Pengiriman</th>
                                <th>Alamat</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($shippingspagination as $shipping)
                                <tr>
                                    <td>{{ ($shippingspagination->currentPage() - 1) * $shippingspagination->perPage() + $loop->iteration }}
                                    </td>
                                    <td>{{ $shipping->shipping_name }}</td>
                                    <td>{{ $shipping->address }}</td>
                                    <td>
                                        <a href="{{ route('shippings.edit', $shipping->id) }}"
                                            class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $shippingspagination->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
