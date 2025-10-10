@extends('Component.main_admin')

<style>
    .table td,
    .table th {
        vertical-align: middle !important;
    }
</style>

@section('content')
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card border-primary" style="border-width: 2px;">
                <div class="card-body">
                    <h4 class="mb-4 text-primary">Form Surat Jalan</h4>

                    <form action="{{ route('SJ.Store') }}" method="POST" id="form-create">
                        @csrf

                        {{-- Informasi Umum --}}
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label>Order Number</label>
                                <input type="text" class="form-control" value="{{ $sale->order_number }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Customer</label>
                                <input type="text" class="form-control"
                                    value="{{ $sale->customer->customer_name ?? '-' }}" readonly>
                                <input type="hidden" name="customer_code"
                                    value="{{ $sale->customer->customer_code ?? '' }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3">
                                <label>Surat Jalan Number</label>
                                <input type="text" name="sj_number" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Ship Date</label>
                                <input type="date" name="ship_date" class="form-control" value="{{ date('Y-m-d') }}"
                                    required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label>Note</label>
                                <textarea name="note" class="form-control" rows="3"></textarea>
                            </div>
                        </div>

                        {{-- Shipping dan Term of Payment --}}
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <label>Shipping 1</label>
                                <select name="ship_1" class="form-control">
                                    <option value="">Pilih Shipping</option>
                                    @foreach ($shippings as $shipping)
                                        <option value="{{ $shipping->shipping_code }}"
                                            {{ $shipping->shipping_code == $selectedShip1 ? 'selected' : '' }}>
                                            {{ $shipping->shipping_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Shipping 2</label>
                                <select name="ship_2" class="form-control">
                                    <option value="">Pilih Shipping</option>
                                    @foreach ($shippings as $shipping)
                                        <option value="{{ $shipping->shipping_code }}"
                                            {{ $shipping->shipping_code == $selectedShip2 ? 'selected' : '' }}>
                                            {{ $shipping->shipping_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Term of Payment (days)</label>
                                <input type="text" name="top" class="form-control"
                                    value="{{ $termOfPayment ?? '-' }}" readonly>
                            </div>
                        </div>

                        {{-- Tabel Produk --}}
                        <h5 class="mt-4 mb-3">Daftar Produk</h5>

                        <button type="button" class="btn btn-secondary mb-3" id="btn-restore">Tampilkan Semua
                            Produk</button>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center">
                                <thead class="table-primary">
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 40%;">Nama Produk</th>
                                        <th style="width: 25%;">Packing</th>
                                        <th style="width: 25%;">Unit</th>
                                        <th style="width: 5%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="produk-table-body">
                                    @php $index = 1; @endphp
                                    @foreach ($details as $detail)
                                        <tr class="produk-row">
                                            <td>{{ $index++ }}</td>
                                            <td class="text-start">
                                                {{ $detail->product->product_name }}
                                                <input type="hidden" name="product_details[]" value="{{ $detail->id }}">
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="number" name="qty_packings[]"
                                                        class="form-control text-end qty-packing"
                                                        value="{{ $detail->remaining_packing }}"
                                                        data-max="{{ $detail->remaining_packing }}" min="0"
                                                        max="{{ $detail->remaining_packing }}">
                                                    <span class="input-group-text bg-light">{{ $detail->packing }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="input-group">
                                                    <input type="number" name="qty_units[]"
                                                        class="form-control text-end qty-unit"
                                                        value="{{ $detail->remaining_unit }}"
                                                        data-max="{{ $detail->remaining_unit }}" min="1"
                                                        max="{{ $detail->remaining_unit }}">
                                                    <span class="input-group-text bg-light">{{ $detail->unit }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger btn-remove">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-primary px-4" onclick="confirmSubmit()">Buat Surat
                                Jalan</button>
                        </div>
                    </form>
                </div>
            </div>
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
            const form = document.getElementById('form-create');

            // Jalankan validasi HTML5 dulu (cek required, max, min, dll)
            if (!form.checkValidity()) {
                form.reportValidity(); // tampilkan pesan error dari browser
                return; // stop lanjut ke SweetAlert
            }

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
                    form.submit(); // submit setelah validasi lolos
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

    {{-- Script untuk hapus dan restore --}}
    <script>
        let originalRows = [];

        document.querySelectorAll('.produk-row').forEach(row => {
            originalRows.push(row.cloneNode(true));
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove')) {
                e.target.closest('tr').remove();
            }
        });

        document.getElementById('btn-restore').addEventListener('click', function() {
            const tbody = document.getElementById('produk-table-body');
            tbody.innerHTML = '';
            originalRows.forEach(row => {
                tbody.appendChild(row.cloneNode(true));
            });
        });
    </script>
@endsection
