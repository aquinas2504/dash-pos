@extends('Component.main_admin')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <form action="{{ route('payment.update', $payment->id) }}" id="form-edit-payment" method="POST">
                @csrf
                @method('PUT')

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0">Edit Payment</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="rekening_name" class="form-label">Nama Rekening</label>
                                <input type="text" name="rekening_name" id="rekening_name" class="form-control"
                                    value="{{ old('rekening_name', $payment->rekening_name) }}" required>
                            </div>
                            <div class="col-md-12">
                                <label for="rekening_number" class="form-label">No. Rekening</label>
                                <input type="text" name="rekening_number" id="rekening_number" class="form-control"
                                    value="{{ old('rekening_number', $payment->rekening_number) }}" required>
                            </div>
                            <div class="col-md-12">
                                <label for="bank_name" class="form-label">Nama Bank</label>
                                <input type="text" name="bank_name" id="bank_name" class="form-control"
                                    value="{{ old('bank_name', $payment->bank_name) }}" required>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="button" class="btn btn-warning" onclick="confirmSubmit()">Update</button>
                        <a href="{{ route('payment.index') }}" class="btn btn-secondary">Back</a>
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
                    document.getElementById('form-edit-payment').submit();
                }
            });
        }
    </script>
    
@endsection