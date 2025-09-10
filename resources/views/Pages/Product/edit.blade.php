@extends('Component.main_admin')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <form action="{{ route('products.update', $product->id) }}" id="form-edit" method="POST">
                @csrf
                @method('PUT')

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Edit Product</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="product_code" class="form-label">Product Code</label>
                                <input type="text" name="product_code" id="product_code" class="form-control"
                                    value="{{ old('product_code', $product->product_code ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="product_name" class="form-label">Product Name</label>
                                <input type="text" name="product_name" id="product_name" class="form-control"
                                    value="{{ old('product_name', $product->product_name ?? '') }}" required>
                            </div>
                        <hr class="my-4">

                        <h6 class="text-primary">Packing & Unit Conversions</h6>
                        <div id="conversion-container" class="row gy-2">
                            @foreach ($product->productPackings ?? [] as $i => $conv)
                                <div class="form-row conversion-row mb-2">
                                    {{-- HIDDEN ID --}}
                                    <input type="hidden" name="conversions[{{ $i }}][id]"
                                        value="{{ $conv->id }}">

                                    <div class="col-md-3">
                                        <select name="conversions[{{ $i }}][packing_id]" class="form-control"
                                            required>
                                            <option value="">-- Select Packing --</option>
                                            @foreach ($packings as $packing)
                                                <option value="{{ $packing->id }}"
                                                    {{ $conv->packing_id == $packing->id ? 'selected' : '' }}>
                                                    {{ $packing->packing_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="number" name="conversions[{{ $i }}][conversion_value]"
                                            class="form-control" placeholder="Qty" required
                                            value="{{ $conv->conversion_value }}">
                                    </div>
                                    <div class="col-md-3">
                                        <select name="conversions[{{ $i }}][unit_id]" class="form-control"
                                            required>
                                            <option value="">-- Select Unit --</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}"
                                                    {{ $conv->unit_id == $unit->id ? 'selected' : '' }}>
                                                    {{ $unit->unit_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                                    </div>
                                </div>
                            @endforeach

                        </div>

                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addConversionRow()">+
                            Add Conversion</button>


                        <div class="mt-4">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="4"
                                placeholder="Enter product description">{{ old('description', $product->description ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="button" class="btn btn-warning" onclick="confirmSubmit()">Update</button>
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- SweetAlert for error/success --}}
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Validasi Gagal!',
                    html: `<ul class="text-left ps-4">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>`,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Berhasil!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif

    <script>
        let index = {{ count($product->productPackings ?? []) }};

        function addConversionRow() {
            const container = document.getElementById('conversion-container');
            const newRow = document.createElement('div');
            newRow.classList.add('form-row', 'conversion-row', 'mb-2');
            newRow.innerHTML = `
                <div class="col-md-3">
                    <select name="conversions[${index}][packing_id]" class="form-control" required>
                        <option value="">-- Select Packing --</option>
                        @foreach ($packings as $packing)
                            <option value="{{ $packing->id }}">{{ $packing->packing_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="number" name="conversions[${index}][conversion_value]" class="form-control" placeholder="Qty" required>
                </div>
                <div class="col-md-3">
                    <select name="conversions[${index}][unit_id]" class="form-control" required>
                        <option value="">-- Select Unit --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->unit_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-danger btn-sm remove-row">Remove</button>
                </div>
            `;
            container.appendChild(newRow);
            index++;
        }

        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-row')) {
                e.target.closest('.conversion-row').remove();
            }
        });

        function confirmSubmit() {
            Swal.fire({
                title: 'Yakin update data produk ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f39c12',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Update!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-edit').submit();
                }
            });
        }
    </script>
@endsection
