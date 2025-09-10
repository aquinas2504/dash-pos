@extends('Component.main_admin')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('users.update', $user->id) }}" id="form-edit">
                @csrf
                @method('PUT')

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Edit User</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="name" class="form-label">Nama</label>
                                <input type="text" id="name" name="name" class="form-control"
                                    value="{{ old('name', $user->name) }}" required>
                            </div>

                            <div class="col-md-12">
                                <label for="sub_role" class="form-label">Sub Role</label>
                                <select name="sub_role" id="sub_role" class="form-control" required>
                                    <option value="">-- Pilih Sub Role --</option>
                                    <option value="admin" {{ $user->sub_role === 'admin' ? 'selected' : '' }}>Admin
                                    </option>
                                    <option value="sales" {{ $user->sub_role === 'sales' ? 'selected' : '' }}>Sales
                                    </option>
                                    <!-- Tambahkan opsi lainnya sesuai kebutuhan -->
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="button" class="btn btn-warning" onclick="confirmSubmit()">Update</button>
                        <a href="{{ route('users.index') }}" class="btn btn-secondary">Back</a>
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
