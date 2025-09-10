@extends('Component.main_admin')

@section('content')
    <div class="row justify-content-center mt-4">
        <div class="col-lg-8">
            <form action="{{ route('shippings.update', $shipping->id) }}" id="form-edit-shipping" method="POST">
                @csrf
                @method('PUT')

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-white">
                        <h5 class="mb-0">Edit Shipping</h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label for="shipping_name" class="form-label">Nama Pengiriman</label>
                            <input type="text" name="shipping_name" id="shipping_name" class="form-control"
                                value="{{ old('shipping_name', $shipping->shipping_name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea name="address" id="address" rows="3" class="form-control" required>{{ old('address', $shipping->address) }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="button" class="btn btn-warning" onclick="confirmSubmit()">Update</button>
                        <a href="{{ route('shippings.index') }}" class="btn btn-secondary">Back</a>
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
                    document.getElementById('form-edit-shipping').submit();
                }
            });
        }
    </script>
@endsection
