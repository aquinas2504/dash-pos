@extends('Component.main_admin')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <form action="{{ route('suppliers.update', $supplier->supplier_code) }}" id="form-edit" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Edit Supplier</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="supplier_code" class="form-label">Kode Supplier</label>
                                <input type="text" name="supplier_code" id="supplier_code" class="form-control"
                                    value="{{ $supplier->supplier_code }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label for="supplier_name" class="form-label">Nama Supplier</label>
                                <input type="text" name="supplier_name" id="supplier_name" class="form-control"
                                    value="{{ old('supplier_name', $supplier->supplier_name) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label for="supplier_phone" class="form-label">Nomor Telepon</label>
                                <input type="number" name="supplier_phone" id="supplier_phone" class="form-control"
                                    value="{{ old('supplier_phone', $supplier->supplier_phone) }}">
                            </div>

                            <div class="col-md-4">
                                <label for="npwp" class="form-label">NPWP</label>
                                <input type="text" name="npwp" id="npwp" class="form-control"
                                    value="{{ old('npwp', $supplier->npwp) }}">
                            </div>

                            <div class="col-md-4">
                                <label for="city" class="form-label">Kota</label>
                                <input type="text" name="city" id="city" class="form-control"
                                    value="{{ old('city', $supplier->city) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="Active" {{ $supplier->status == 'Active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="Inactive" {{ $supplier->status == 'Inactive' ? 'selected' : '' }}>
                                        Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label for="address" class="form-label">Alamat</label>
                                <textarea name="address" id="address" rows="2" class="form-control" required>{{ old('address', $supplier->address) }}</textarea>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="button" class="btn btn-primary" onclick="confirmSubmit()">Update</button>
                        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Back</a>
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
        function confirmSubmit() {
            Swal.fire({
                title: 'Yakin update data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
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
