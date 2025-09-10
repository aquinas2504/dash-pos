@extends('Component.main_admin')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            
            <form method="POST" id="form-create" action="{{ route('payment.store') }}">
                @csrf

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tambah Rekening</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="rekening_name" class="form-label">Nama Rekening</label>
                                <input type="text" name="rekening_name" id="rekening_name" class="form-control"
                                    placeholder="Masukkan Nama Rekening" value="{{ old('rekening_name') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="rekening_number" class="form-label">No. Rekening</label>
                                <input type="text" name="rekening_number" id="rekening_number" class="form-control"
                                    placeholder="Masukkan Nomor Rekening" value="{{ old('rekening_number') }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="bank_name" class="form-label">Nama Bank</label>
                                <input type="text" name="bank_name" id="bank_name" class="form-control"
                                    placeholder="Masukkan Nama Bank" value="{{ old('bank_name') }}" required>
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
