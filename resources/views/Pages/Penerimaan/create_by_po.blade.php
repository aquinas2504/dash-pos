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
                    <h4 class="mb-4 text-primary">Form Penerimaan Pembelian</h4>

                    <form method="POST" action="{{ route('penerimaan.store') }}" id="form-create">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Nomor Penerimaan</label>
                                <input type="text" name="penerimaan_number" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Tanggal</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}"
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>Supplier</label>
                            <input type="text" name="supplier_name" class="form-control"
                                value="{{ $purchase->supplier->supplier_name ?? '-' }}" readonly>
                            <input type="hidden" name="supplier_code"
                                value="{{ $purchase->supplier->supplier_code ?? '' }}">
                        </div>

                        <div class="mb-4">
                            <label>Catatan</label>
                            <textarea name="note" class="form-control" rows="3"></textarea>
                        </div>

                        <h5 class="mt-4 mb-3">Daftar Produk</h5>

                        <button type="button" class="btn btn-secondary mb-3" id="btn-restore">Tampilkan Semua
                            Produk</button>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center">
                                <thead class="table-primary">
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 40%;">Nama Produk</th>
                                        <th style="width: 20%;">Packing</th>
                                        <th style="width: 20%;">Unit</th>
                                        <th style="width: 5%;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="produk-table-body">
                                    @foreach ($groupedDetails as $i => $item)
                                        <tr class="produk-row">
                                            <td>{{ $i + 1 }}</td>
                                            <td class="text-start">
                                                <strong>{{ $item['product_name'] }}</strong>

                                                <div class="text-muted small mt-1">
                                                    Diterima: {{ $item['received_packing'] }}/{{ $item['ordered_packing'] }}
                                                    {{ $item['packing'] }},
                                                    {{ $item['received_unit'] }}/{{ $item['ordered_unit'] }}
                                                    {{ $item['unit'] }}
                                                </div>

                                                @foreach ($item['po_details'] as $poDetailId)
                                                    <input type="hidden" name="details[{{ $i }}][po_details][]"
                                                        value="{{ $poDetailId }}">
                                                @endforeach
                                                <input type="hidden" name="details[{{ $i }}][id_product]"
                                                    value="{{ $item['id_product'] }}">
                                            </td>

                                            <td>
                                                <div class="row g-1">
                                                    <div class="col-7">
                                                        <input type="number"
                                                            name="details[{{ $i }}][qty_packing]"
                                                            value="{{ $item['qty_packing'] }}"
                                                            class="form-control text-end" min="1"
                                                            max="{{ $item['qty_packing'] }}">
                                                    </div>
                                                    <div class="col-5">
                                                        <input type="text" name="details[{{ $i }}][packing]"
                                                            value="{{ $item['packing'] }}" class="form-control" readonly>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="row g-1">
                                                    <div class="col-7">
                                                        <input type="number" name="details[{{ $i }}][qty_unit]"
                                                            value="{{ $item['qty_unit'] }}" class="form-control text-end"
                                                            min="1" max="{{ $item['qty_unit'] }}">
                                                    </div>
                                                    <div class="col-5">
                                                        <input type="text" name="details[{{ $i }}][unit]"
                                                            value="{{ $item['unit'] }}" class="form-control" readonly>
                                                    </div>
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

                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-primary px-4" onclick="confirmSubmit()">Simpan Penerimaan</button>
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
