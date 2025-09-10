@extends('Component.main_admin')


@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <form action="{{ route('products.store') }}" id="form-create" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Tambah Produk Baru</h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="product_code" class="form-label">Kode Produk</label>
                                <input type="text" name="product_code" id="product_code" class="form-control"
                                    placeholder="Masukkan Kode Produk" value="{{ old('product_code') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="product_name" class="form-label">Nama Produk</label>
                                <input type="text" name="product_name" id="product_name" class="form-control"
                                    placeholder="Masukkan Nama Produk" value="{{ old('product_name') }}" required>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="text-primary">Packing & Unit Konversi</h6>
                        <div id="conversion-container" class="row gy-2">
                            {{-- Rows will be inserted here --}}
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addConversionRow()">+
                            Tambah Konversi</button>

                        <div class="mt-4">
                            <label for="description" class="form-label">Deskripsi</label>
                            <textarea name="description" id="description" class="form-control" rows="4"
                                placeholder="Masukkan Keterangan Produk">{{ old('description') }}</textarea>
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

    <script>
        let index = 1;

        function addConversionRow() {
            const container = document.getElementById('conversion-container');
            const newRow = document.createElement('div');
            newRow.classList.add('form-row', 'conversion-row', 'mb-2');
            newRow.innerHTML = `
                <div class="col-md-3">
                    <select name="conversions[${index}][packing_id]" class="form-control" required>
                        <option value="">-- Pilih Packing --</option>
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
                        <option value="">-- Pilih Unit --</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}">{{ $unit->unit_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-danger btn-sm remove-row">Hapus</button>
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
    </script>
@endsection
