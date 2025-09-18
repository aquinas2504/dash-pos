@extends('Component.main_admin')


@section('content')
    
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card-header d-flex justify-content-between align-items-center"
        style="position: relative; margin-bottom: 10px;">
        <div>
            <a href="{{ route('products.create') }}" class="btn btn-sm btn-primary mr-2">
                <i class="fa fa-plus-square"></i> Add Product
            </a>
        </div>
    </div>


    <div class="row">
        <div class="col">
            <div class="card border-primary" style="border-width: 2px;">
                <div class="row mb-3">
                    <div class="col">

                        <form method="GET" action="{{ route('products.index') }}" class="d-flex">

                            <div class="input-group w-50 ml-2 mt-2">
                                <input type="text" name="search" class="form-control border-secondary"
                                    style="border-radius: 10px" placeholder="Search..." value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary ml-3"
                                    style="border-radius: 10px">Search</button>
                                <a href="{{ route('products.index') }}" class="btn btn-secondary ml-2"
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
                                <th>Code</th>
                                <th>Name</th>
                                <th>Stock Gudang</th>
                                <th>Avg Harga Beli</th>
                                <th>Avg Harga Jual</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($productspagination as $product)
                                <tr>
                                    <td>{{ ($productspagination->currentPage() - 1) * $productspagination->perPage() + $loop->iteration }}</td>
                                    <td>{{ $product->product_code ?? "-" }}</td>
                                    <td>{{ $product->product_name }}</td>
                                    <td class="{{ $product->stok_gudang < 0 ? 'text-danger fw-bold' : '' }}">
                                        {{ $product->stok_gudang }}
                                    </td>
                                    <td>
                                        @php
                                            $avg =
                                                $product->total_purchase_qty > 0
                                                    ? $product->total_purchase_amount / $product->total_purchase_qty
                                                    : 0;
                                        @endphp
                                        {{ 'Rp ' . number_format($avg, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        @php
                                            $avg =
                                                $product->total_sold_qty > 0
                                                    ? $product->total_sold_amount / $product->total_sold_qty
                                                    : 0;
                                        @endphp
                                        {{ 'Rp ' . number_format($avg, 0, ',', '.') }}
                                    </td>

                                    <td>
                                        <a href="{{ route('products.edit', $product->id) }}"
                                            class="btn btn-sm btn-warning">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $productspagination->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
