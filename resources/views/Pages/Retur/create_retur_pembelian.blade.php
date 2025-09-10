@extends('Component.main_admin')

@section('content')
    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                html: `{!! implode('<br>', $errors->all()) !!}`
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: "{{ session('error') }}"
            });
        </script>
    @endif

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: "{{ session('success') }}"
            });
        </script>
    @endif



    <div class="container mt-4">

        {{-- ================= FORM TAMBAH LIST RETUR ================= --}}
        <div class="card shadow-sm mb-5">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Tambah Retur Pembelian</h5>
            </div>
            <div class="card-body">
                <!-- Button Tambah -->
                <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#modalAddRetur">
                    Tambah List
                </button>

                <!-- Modal Cari Produk -->
                <div class="modal fade" id="modalAddRetur" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5>Cari Produk</h5>
                            </div>
                            <div class="modal-body">
                                <input type="text" id="searchProduct" class="form-control" placeholder="Nama Produk">
                                <button class="btn btn-secondary mt-2" id="btnSearchInvoice">Search</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Hasil Pencarian -->
                <div class="modal fade" id="modalSearchInvoice" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5>Hasil Pencarian</h5>
                            </div>
                            <div class="modal-body">
                                <table class="table table-bordered" id="invoiceResult">
                                    <thead>
                                        <tr>
                                            <th>Invoice</th>
                                            <th>Tanggal</th>
                                            <th>Produk</th>
                                            <th>Qty</th>
                                            <th>Harga</th>
                                            <th>Supplier</th>
                                            <th>Riwayat Retur</th>
                                        </tr>
                                    </thead>
                                    <tbody><!-- isi ajax --></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Waiting List Retur -->
                <div class="table-responsive">
                    <table class="table table-striped mt-3" id="waitingRetur">
                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Produk</th>
                                <th>Qty</th>
                                <th>Unit</th>
                                <th>Harga</th>
                                <th>Discount</th>
                                <th>Value</th>
                                <th>Supplier</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

                <button class="btn btn-success mt-3" id="btnSaveRetur">Simpan Retur (Waiting List)</button>
            </div>
        </div>


        {{-- ================= FORM SIMPAN RETUR ================= --}}
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Form Simpan Retur</h5>
            </div>
            <div class="card-body">
                {{-- Filter Supplier --}}
                <form method="GET" action="{{ route('retur-purchases.index') }}" class="row g-3 align-items-end mb-4">
                    <div class="col-md-3">
                        <label class="form-label">Supplier</label>
                        <select name="supplier_code" class="form-control" onchange="this.form.submit()">
                            <option value="">-- Semua Supplier --</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->supplier_code }}"
                                    {{ request('supplier_code') == $s->supplier_code ? 'selected' : '' }}>
                                    {{ $s->supplier_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                {{-- Form Simpan Retur --}}
                <form method="POST" action="{{ route('retur-purchase.FinalStore') }}">
                    @csrf

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">No. Retur</label>
                            <input type="text" name="retur_number" class="form-control" value="{{ old('retur_number') }}"
                                required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="date" class="form-control"
                                value="{{ old('date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Catatan</label>
                            <input type="text" name="note" class="form-control" value="{{ old('note') }}">
                        </div>
                        <input type="hidden" name="supplier_code" value="{{ request('supplier_code') }}">
                    </div>

                    {{-- Tabel Data --}}
                    <h5 class="mt-4">List Retur Pembelian</h5>

                    <div class="d-flex justify-content-end mb-3">
                        <a href="{{ route('retur-purchases.index') }}" class="btn btn-secondary">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="returTable">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Produk</th>
                                    <th>Supplier</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Price</th>
                                    <th>Discount</th>
                                    <th>Value</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="returTable">
                                @foreach ($returs as $r)
                                    <tr>
                                        @php
                                            $factor = [
                                                'Pieces' => 1,
                                                'Set' => 1,
                                                'Lusin' => 12,
                                                'Gross' => 144,
                                            ];
                                            $displayPrice = $r->price * ($factor[$r->unit] ?? 1);
                                            $displayValue = \App\Helpers\formatRp::calculateValue(
                                                $r->qty,
                                                $displayPrice,
                                                $r->discount,
                                            );
                                        @endphp

                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            {{ $r->product->product_name ?? '-' }}
                                            <input type="hidden" name="data[{{ $loop->index }}][id_product]"
                                                value="{{ $r->id_product }}">
                                        </td>
                                        <td>
                                            {{ $r->supplier->supplier_name ?? '-' }}
                                            <input type="hidden" name="data[{{ $loop->index }}][supplier_code]"
                                                value="{{ $r->supplier_code }}">
                                        </td>
                                        <td>
                                            {{ $r->qty }}
                                            <input type="hidden" class="qty" name="data[{{ $loop->index }}][qty]"
                                                value="{{ $r->qty }}">
                                        </td>
                                        <td>
                                            {{ $r->unit }}
                                            <input type="hidden" name="data[{{ $loop->index }}][unit]"
                                                value="{{ $r->unit }}">
                                        </td>
                                        <td>
                                            {{-- {{ \App\Helpers\formatRp::rupiah($displayPrice) }}
                                            <input type="hidden" name="data[{{ $loop->index }}][price]"
                                                value="{{ $displayPrice }}"> --}}
                                            <input type="text" class="form-control price"
                                                value="{{ \App\Helpers\formatRp::rupiah($displayPrice) }}">
                                            <input type="hidden" name="data[{{ $loop->index }}][price]"
                                                class="price_raw" value="{{ $displayPrice }}">
                                        </td>
                                        <td>
                                            {{ $r->discount }} %
                                            <input type="hidden" class="discount"
                                                name="data[{{ $loop->index }}][discount]" value="{{ $r->discount }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control value"
                                                value="{{ \App\Helpers\formatRp::rupiah($displayValue) }}" readonly>
                                            <input type="hidden" name="data[{{ $loop->index }}][value]"
                                                class="value_raw" value="{{ $displayValue }}">
                                        </td>
                                        <td>
                                            <button type="button"
                                                class="btn btn-sm btn-danger btn-remove-row">Hapus</button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Tombol Save --}}
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Simpan Retur
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    {{-- Script ubah price manual --}}
    <script>
        function formatRupiah(angka) {
            let number_string = angka.toString().replace(/[^,\d]/g, ''),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }

            rupiah = split[1] !== undefined ? rupiah + ',' + split[1].substr(0, 3) : rupiah;
            return 'Rp ' + rupiah;
        }

        function toNumber(rp) {
            return parseFloat(rp.replace(/[^0-9,-]/g, '').replace(',', '.')) || 0;
        }

        function applyNestedDiscount(price, discountStr) {
            if (!discountStr) return price;

            let discounts = discountStr.toString().split('+').map(d => parseFloat(d.trim()) || 0);
            let finalPrice = price;

            discounts.forEach(d => {
                finalPrice = finalPrice - (finalPrice * d / 100);
            });

            return finalPrice;
        }


        document.querySelector('#returTable').addEventListener('input', function(e) {
            if (e.target.classList.contains('price')) {
                let row = e.target.closest('tr');

                let qty = parseFloat(row.querySelector('.qty').value) || 0;
                let discountStr = row.querySelector('.discount').value; // bisa "10" atau "10+5"

                // ambil angka dari input price
                let rawPrice = toNumber(e.target.value);

                // hitung harga setelah diskon nested
                let netPrice = applyNestedDiscount(rawPrice, discountStr);

                // hitung value total
                let value = netPrice * qty;

                // update hidden input price_raw
                row.querySelector('.price_raw').value = rawPrice;

                // format kembali input price
                e.target.value = formatRupiah(rawPrice);

                // update kolom value
                row.querySelector('.value').value = formatRupiah(value);
                row.querySelector('.value_raw').value = value;
            }
        });
    </script>

    {{-- Script Untuk Rendering data retur ke table untuk di simpan list nya nantinya --}}
    <script>
        let returList = [];

        $('#btnSearchInvoice').click(function() {
            let keyword = $('#searchProduct').val();
            if (!keyword) return;

            $.get("{{ route('retur-purchase.searchInvoice') }}", {
                keyword
            }, function(res) {

                let html = '';
                res.forEach(r => {
                    html += `
                <tr>
                    <td>${r.invoice_number}</td>
                    <td>${r.date}</td>
                    <td>${r.product_name}</td>
                    <td>${r.qty_unit} ${r.unit}</td>
                    <td>${formatRupiah(r.price)}</td>
                    <td>${r.supplier_name}</td>
                    <td>${r.qty_retur ?? 0} Piece / Set</td>
                    <td>
                        <button class="btn btn-sm btn-success" 
                            onclick='useInvoice(${JSON.stringify(r)})'>Gunakan</button>
                    </td>
                </tr>
            `;
                });
                $('#invoiceResult tbody').html(html);
                $('#modalAddRetur').modal('hide');
                $('#modalSearchInvoice').modal('show');
            });
        });

        function useInvoice(data) {
            // Cek duplikat
            if (returList.find(item => item.product_id === data.product_id)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Produk sudah ada di list!',
                });
                return;
            }

            data.qty = ''; // default user isi manual
            data.unit_selected = data.unit; // default unit awal
            returList.push(data);
            renderTable();

            $('#modalSearchInvoice').modal('hide');
        }

        function renderTable() {
            let html = '';
            returList.forEach((r, i) => {
                let priceConverted = convertPrice(r.price, r.unit, r.unit_selected);
                let subtotal = priceConverted * r.qty;
                let totalDisc = applyDiscount(subtotal, r.discount);
                let value = subtotal - totalDisc;

                html += `
                    <tr id="row-${i}">
                        <td>${i+1}</td>
                        <td>${r.product_name}</td>
                        <td>
                            <input type="number" class="form-control" value="${r.qty}" 
                                placeholder="Masukkan Jumlah Retur"
                                oninput="updateQty(${i}, this.value)">
                        </td>
                        <td>
                            <select class="form-control" onchange="updateUnit(${i}, this.value)">
                                <option ${r.unit_selected=='Pieces'?'selected':''}>Pieces</option>
                                <option ${r.unit_selected=='Set'?'selected':''}>Set</option>
                                <option ${r.unit_selected=='Lusin'?'selected':''}>Lusin</option>
                                <option ${r.unit_selected=='Gross'?'selected':''}>Gross</option>
                            </select>
                        </td>
                        <td id="price-${i}">${formatRupiah(priceConverted)}</td>
                        <td>${r.discount ? r.discount : 0}%</td>
                        <td id="value-${i}">${formatRupiah(value)}</td>
                        <td>${r.supplier_name}</td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="removeRow(${i})">
                                Hapus
                            </button>
                        </td>
                    </tr>
                `;
            });
            $('#waitingRetur tbody').html(html);
        }

        // Helper format Rupiah versi JS
        function formatRupiah(value) {
            value = parseFloat(value) || 0;
            return 'Rp ' + value.toLocaleString('id-ID');
        }

        function removeRow(index) {
            returList.splice(index, 1); // hapus array
            renderTable();
        }


        function updateQty(index, qty) {
            returList[index].qty = parseInt(qty) || 0;
            updateRow(index);
        }

        function updateUnit(index, unit) {
            returList[index].unit_selected = unit;
            updateRow(index);
        }

        function updateRow(index) {
            let r = returList[index];
            let priceConverted = convertPrice(r.price, r.unit, r.unit_selected);
            let subtotal = priceConverted * r.qty;
            let totalDisc = applyDiscount(subtotal, r.discount);
            let value = subtotal - totalDisc;

            // ⬅️ tambahin ini supaya r.price ikut diupdate dengan harga baru
            returList[index].price_converted = priceConverted;

            $(`#price-${index}`).text(formatRupiah(priceConverted));
            $(`#value-${index}`).text(formatRupiah(value));
        }

        // Helper konversi harga
        function convertPrice(basePrice, fromUnit, toUnit) {
            let factor = {
                'Pieces': 1,
                'Set': 1,
                'Lusin': 12,
                'Gross': 144
            };
            let price = parseFloat(basePrice) || 0; // pastikan number
            return Math.round(price * (factor[toUnit] / factor[fromUnit]));
        }


        // Helper diskon bertingkat
        function applyDiscount(total, discount) {
            if (!discount || discount.trim() === "") return 0;

            let result = 0;
            let current = total;

            let discounts = discount.split('+').map(d => parseFloat(d) || 0);
            discounts.forEach(d => {
                let potongan = current * (d / 100);
                result += potongan;
                current -= potongan;
            });

            return result; // return total diskon (bukan total akhir)
        }
    </script>

    {{-- Script Button Save List --}}
    <script>
        $('#btnSaveRetur').click(function() {
            if (returList.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Belum ada data retur!',
                });
                return;
            }

            // validasi qty sebelum submit
            let valid = true;
            let message = "";
            returList.forEach((r, i) => {
                if (!r.qty || r.qty <= 0) {
                    valid = false;
                    message +=
                        `Row ${i + 1} - ${r.product_name} (Supplier: ${r.supplier_name}): Qty tidak boleh kosong atau 0\n`;
                }
            });

            if (!valid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Validasi Gagal',
                    html: `<pre style="text-align:left">${message}</pre>`
                });
                return;
            }

            $.ajax({
                url: "{{ route('retur-purchase.store') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    data: returList.map(r => ({
                        id_product: r.product_id,
                        supplier_code: r.supplier_code,
                        qty: r.qty,
                        unit: r.unit_selected,
                        price: r.price_converted ?? convertPrice(r.price, r.unit, r
                            .unit_selected),
                        discount: r.discount,
                        invoice_detail_id: r.invoice_detail_id,
                    }))
                },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: res.message,
                    });
                    returList = [];
                    renderTable();
                },
                error: function(err) {
                    console.error(err);

                    let msg = "Gagal menyimpan data retur!";

                    if (err.responseJSON) {
                        // kalau ada "message" langsung pakai itu
                        if (err.responseJSON.message) {
                            msg = err.responseJSON.message;
                        }
                        // kalau ada "errors" (validasi laravel), looping
                        else if (err.responseJSON.errors) {
                            msg = "";
                            err.responseJSON.errors.forEach(e => {
                                msg +=
                                    `${e.product} → ${e.message}\n`;
                            });
                        }

                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        html: `<pre style="text-align:left">${msg}</pre>`
                    });
                }

            });
        });
    </script>

    {{-- Script untuk buat retur --}}
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('btn-remove-row')) {
                e.target.closest('tr').remove();
                resetRowNumbers();
            }
        });

        function resetRowNumbers() {
            const rows = document.querySelectorAll('#returTable tbody tr');
            rows.forEach((row, index) => {
                // Update nomor urut di kolom pertama
                row.querySelector('td:first-child').textContent = index + 1;

                // Update name input hidden biar index sesuai
                row.querySelectorAll('input').forEach(input => {
                    input.name = input.name.replace(/\[\d+\]/, `[${index}]`);
                });
            });
        }
    </script>
@endsection
