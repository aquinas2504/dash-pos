@extends('Component.main_admin')

<style>
    #customer-search-result,
    .product-search-result {
        max-height: 200px;
        overflow-y: auto;
        position: relative;
        width: 100%;
        z-index: 1050;
    }

    .search-result-item:hover {
        background-color: #f1f1f1;
    }

    .table td,
    .table th {
        vertical-align: middle !important;
    }


    .form-inline-select {
        display: flex;
        gap: 0.5rem;
    }

    .form-inline-select input {
        flex: 1;
    }

    .form-inline-select select {
        width: 100px;
    }
</style>


@section('content')
    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card border-success" style="border-width: 2px;">
                <div class="card-body">
                    <h4 class="mb-4 text-success">Form Surat Jalan Manual</h4>

                    <form action="{{ route('SJ.StoreManual') }}" method="POST" id="purchase-form">
                        @csrf

                        {{-- Informasi Umum --}}
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

                        <div class="mb-3 position-relative">
                            <label>Customer</label>
                            <input type="text" name="customer_code" id="search-customer" placeholder="Search Customer..."
                                class="form-control">
                            <div id="customer-search-result" class="border bg-white shadow-sm mt-1"></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4 mb-3">
                                <label>Shipping 1</label>
                                <select name="ship_1" class="form-control">
                                    <option value="">Pilih Shipping</option>
                                    @foreach ($shippings as $shipping)
                                        <option value="{{ $shipping->shipping_code }}">{{ $shipping->shipping_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Shipping 2</label>
                                <select name="ship_2" class="form-control">
                                    <option value="">Pilih Shipping</option>
                                    @foreach ($shippings as $shipping)
                                        <option value="{{ $shipping->shipping_code }}">{{ $shipping->shipping_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label>Term of Payment (days)</label>
                                <input type="number" name="top" class="form-control"
                                    placeholder="Misal: 30 hari setelah invoice">
                            </div>

                            <!-- Tambahkan bagian pilihan PPN -->
                            <div class="mb-3">
                                <label>Pakai PPN?</label>
                                <div>
                                    <input type="radio" name="ppn_status" id="ppn_yes" value="yes" required>
                                    <label for="ppn_yes">Ya</label>
                                    <input type="radio" name="ppn_status" id="ppn_no" value="no" required>
                                    <label for="ppn_no">Tidak</label>
                                </div>
                            </div>
                        </div>


                        <div class="mb-3">
                            <label>Note</label>
                            <textarea name="note" class="form-control" rows="2"></textarea>
                        </div>

                        {{-- Tabel Produk Manual --}}
                        <h5 class="mt-4 mb-3">Produk</h5>
                        <table class="table table-bordered text-center align-middle" id="table-manual">
                            <thead class="table-primary">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 30%;">Nama Produk</th>
                                    <th style="width: 30%;">Packing</th>
                                    <th style="width: 30%;">Unit</th>
                                    <th style="width: 5%;">
                                        <button type="button" class="btn btn-sm btn-success" id="add-row">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- JS CUY --}}
                            </tbody>
                        </table>

                        <div class="text-end mt-4">
                            <button type="button" class="btn btn-success px-4" onclick="confirmSubmit()">Buat Surat Jalan</button>
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
                    document.getElementById('purchase-form').submit();
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

    {{-- SCRIPT CUSTOMER SEARCH --}}
    <script>
        document.getElementById('search-customer').addEventListener('keyup', function() {
            const query = this.value;
            const resultBox = document.getElementById('customer-search-result');

            if (query.length < 2) {
                resultBox.innerHTML = '';
                return;
            }

            fetch(`/customers-search?q=${query}`)
                .then(res => res.json())
                .then(data => {
                    resultBox.innerHTML = '';

                    if (data.length === 0) {
                        resultBox.innerHTML = '<div class="text-muted px-3 py-2">No customers found.</div>';
                        return;
                    }

                    data.forEach(customer => {
                        const item = document.createElement('div');
                        item.textContent = customer.customer_name;
                        item.classList.add('p-2', 'border-bottom', 'search-result-item');
                        item.style.cursor = 'pointer';

                        item.onclick = () => {
                            document.getElementById('search-customer').value = customer
                                .customer_name;

                            let hiddenInput = document.getElementById('customer_code');
                            if (!hiddenInput) {
                                hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'customer_code';
                                hiddenInput.id = 'customer_code';
                                document.getElementById('purchase-form').appendChild(hiddenInput);
                            }
                            hiddenInput.value = customer.customer_code;

                            resultBox.innerHTML = '';
                        };

                        resultBox.appendChild(item);
                    });
                });
        });
    </script>

    {{-- SCRIPT ADD/REMOVE ROW --}}
    <script>
        document.getElementById('add-row').addEventListener('click', function() {
            const tbody = document.querySelector('#table-manual tbody');
            const index = tbody.rows.length;

            const newRow = `
                <tr>
                    <td>${index + 1}</td>
                    <td class="text-start position-relative">
                        <input type="text" class="form-control search-product" placeholder="Search product...">
                        <div class="product-search-result"></div>
                        <input type="hidden" name="manual[${index}][id_product]">
                    </td>
                    <td>
                        <div class="form-inline-select">
                            <input type="number" name="manual[${index}][qty_packing]" class="form-control" placeholder="Qty">
                            <select name="manual[${index}][packing]" class="form-control">
                                @foreach ($packings as $packing)
                                    <option value="{{ $packing }}">{{ $packing }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td>
                        <div class="form-inline-select">
                            <input type="number" name="manual[${index}][qty_unit]" class="form-control" placeholder="Qty">
                            <select name="manual[${index}][unit]" class="form-control">
                                @foreach ($units as $unit)
                                    <option value="{{ $unit }}">{{ $unit }}</option>
                                @endforeach
                            </select>
                        </div>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-row">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
            tbody.insertAdjacentHTML('beforeend', newRow);
            reindexTable();
        });


        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-row')) {
                e.target.closest('tr').remove();
                reindexTable();
            }
        });

        function reindexTable() {
            const rows = document.querySelectorAll('#table-manual tbody tr');
            rows.forEach((row, i) => {
                row.querySelector('td:first-child').textContent = i + 1;

                // Perbarui name input berdasarkan index
                const inputProduct = row.querySelector('input[type="text"].search-product');
                const hiddenProduct = row.querySelector('input[type="hidden"]');
                const qtyPacking = row.querySelector('input[name*="[qty_packing]"]');
                const packing = row.querySelector('select[name*="[packing]"]');
                const qtyUnit = row.querySelector('input[name*="[qty_unit]"]');
                const unit = row.querySelector('select[name*="[unit]"]');

                if (inputProduct && hiddenProduct && qtyPacking && packing && qtyUnit && unit) {
                    inputProduct.name = `manual[${i}][product_name]`; // Opsional, tergantung kamu pakai atau tidak
                    hiddenProduct.name = `manual[${i}][id_product]`;
                    qtyPacking.name = `manual[${i}][qty_packing]`;
                    packing.name = `manual[${i}][packing]`;
                    qtyUnit.name = `manual[${i}][qty_unit]`;
                    unit.name = `manual[${i}][unit]`;
                }
            });
        }
    </script>

    {{-- SCRIPT SEARCH PRODUK --}}
    <script>
        document.addEventListener('input', function(e) {
            if (e.target && e.target.matches('.search-product')) {
                const input = e.target;
                const resultBox = input.closest('td').querySelector('.product-search-result');
                const query = input.value.trim();

                if (query.length < 2) {
                    resultBox.innerHTML = '';
                    return;
                }

                fetch(`/products-search?q=${encodeURIComponent(query)}`)
                    .then(res => res.json())
                    .then(data => {
                        resultBox.innerHTML = '';

                        if (!data.length) {
                            resultBox.innerHTML = '<div class="p-2 text-muted">No products found.</div>';
                            return;
                        }

                        data.forEach(product => {
                            const item = document.createElement('div');
                            item.classList.add('p-2', 'border-bottom', 'search-result-item');
                            item.style.cursor = 'pointer';
                            item.textContent = `${product.product_name} (${product.product_code})`;

                            item.addEventListener('click', () => {
                                input.value = product.product_name;
                                const hiddenInput = input.closest('td').querySelector(
                                    'input[type="hidden"]');
                                hiddenInput.value = product.id;
                                resultBox.innerHTML = '';
                            });

                            resultBox.appendChild(item);
                        });
                    })
                    .catch(err => {
                        console.error(err);
                        resultBox.innerHTML =
                            '<div class="p-2 text-danger">Terjadi kesalahan saat fetch produk.</div>';
                    });
            }
        });
    </script>
@endsection
