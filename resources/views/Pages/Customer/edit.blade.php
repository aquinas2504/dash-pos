@extends('Component.main_admin')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <form action="{{ route('customers.update', $customer->customer_code) }}" id="form-edit" method="POST"
                enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Edit Customer</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="customer_code" class="form-label">Kode Customer</label>
                                <input type="text" name="customer_code" id="customer_code" class="form-control"
                                    value="{{ $customer->customer_code }}" readonly>
                            </div>

                            <div class="col-md-4">
                                <label for="customer_name" class="form-label">Nama Customer</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control"
                                    value="{{ old('customer_name', $customer->customer_name) }}" required>
                            </div>

                            <div class="col-md-4">
                                <label for="npwp" class="form-label">NPWP</label>
                                <input type="text" name="npwp" id="npwp" class="form-control"
                                    value="{{ old('npwp', $customer->npwp) }}">
                            </div>

                            <div class="col-md-4">
                                <label for="customer_phone" class="form-label">Nomor Telepon</label>
                                <input type="number" name="customer_phone" id="customer_phone" class="form-control"
                                    value="{{ old('customer_phone', $customer->customer_phone) }}">
                            </div>
                            
                            <div class="col-md-4">
                                <label for="pic" class="form-label">PIC</label>
                                <input type="text" name="pic" id="pic" class="form-control"
                                    value="{{ old('pic', $customer->pic) }}">
                            </div>

                            <div class="col-md-4">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-select">
                                    <option value="Active" {{ $customer->status == 'Active' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="Inactive" {{ $customer->status == 'Inactive' ? 'selected' : '' }}>
                                        Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="city" class="form-label">Kota</label>
                                <input type="text" name="city" id="city" class="form-control"
                                    value="{{ old('city', $customer->city) }}" required>
                            </div>

                            <div class="col-md-8">
                                <label for="address" class="form-label">Alamat</label>
                                <textarea name="address" id="address" rows="2" class="form-control" required>{{ old('address', $customer->address) }}</textarea>
                            </div>

                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="button" class="btn btn-primary" onclick="confirmSubmit()">Update</button>
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">Back</a>
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
