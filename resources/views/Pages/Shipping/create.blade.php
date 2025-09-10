@extends('Component.main_admin')

@section('content')
    <div class="row justify-content-center mt-4">
        <div class="col-lg-8">
            <form action="{{ route('shippings.store') }}" method="POST" id="form-create">
                @csrf

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tambah Data Pengiriman</h5>
                    </div>

                    <div class="card-body">
                        <div class="mb-3">
                            <label for="shipping_name" class="form-label">Nama Pengiriman</label>
                            <input type="text" name="shipping_name" id="shipping_name" class="form-control"
                                placeholder="Masukkan nama pengiriman" value="{{ old('shipping_name') }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat</label>
                            <textarea name="address" id="address" rows="3" class="form-control" placeholder="Masukkan alamat lengkap"
                                required>{{ old('address') }}</textarea>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="button" class="btn btn-success" onclick="confirmSubmit()">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

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
                title: 'Yakin simpan data ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-create').submit();
                }
            });
        }
    </script>

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let errorMessages = `
                <ul class="text-left" style="padding-left: 1.2em;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            `;

                Swal.fire({
                    title: 'Validasi Gagal!',
                    html: errorMessages,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            });
        </script>
    @endif
@endsection
