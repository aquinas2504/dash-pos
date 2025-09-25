@extends('Component.main_admin')


@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <form action="{{ route('customers.store') }}" id="form-create" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tambah Customer</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="customer_name" class="form-label">Nama Customer</label>
                                <input type="text" name="customer_name" id="customer_name" class="form-control"
                                    placeholder="Masukkan nama customer" value="{{ old('customer_name') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label for="npwp" class="form-label">NPWP</label>
                                <input type="text" name="npwp" id="npwp" class="form-control"
                                    placeholder="Masukkan NPWP" value="{{ old('npwp') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="customer_phone" class="form-label">Nomor Telepon</label>
                                <input type="text" name="customer_phone" id="customer_phone" class="form-control"
                                    placeholder="Masukkan nomor telepon" value="{{ old('customer_phone') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="pic" class="form-label">PIC</label>
                                <input type="text" name="pic" id="pic" class="form-control"
                                    placeholder="Masukkan nama PIC" value="{{ old('pic') }}">
                            </div>

                            <div class="col-md-4">
                                <label for="city" class="form-label">Kota</label>
                                <input type="text" name="city" id="city" class="form-control"
                                    placeholder="Masukkan kota" value="{{ old('city') }}" required>
                            </div>

                            <div class="col-md-8">
                                <label for="address" class="form-label">Alamat</label>
                                <textarea name="address" id="address" rows="3" class="form-control" placeholder="Masukkan alamat lengkap"
                                    required>{{ old('address') }}</textarea>
                            </div>
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
@endsection
