@extends('Component.main_admin')

<style>
    .search-result-item:hover {
        background-color: #f8f9fa;
    }

    #customer-search-result {
        max-height: 200px;
        overflow-y: auto;
        position: absolute;
        z-index: 1000;
        width: 100%;
    }

    .form-group.position-relative {
        position: relative;
    }

    #product-search-result {
        position: absolute;
        z-index: 1000;
        width: 100%;
        background: white;
        border: 1px solid #ccc;
        max-height: 200px;
        overflow-y: auto;
    }

    .btn-check-sm {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        line-height: 1;
        color: white;
    }
</style>


@section('content')
    <div class="container-fluid">
        <form action="{{ route('sales.store') }}" method="POST" id="purchase-form">
            @csrf
            <div class="row">
                <div class="col-12">

                    {{-- SECTION: Order Info --}}
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">Sale Order Info</div>
                        <div class="card-body row">
                            <div class="form-group col-md-6">
                                <label>Order Number</label>
                                <input type="text" id="order_number" name="order_number" required class="form-control">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Date</label>
                                <input type="date" id="date" name="date" value="{{ date('Y-m-d') }}"
                                    class="form-control" required>
                            </div>

                            <div class="form-group col-md-6 position-relative">
                                <label>Customer</label>
                                <input type="hidden" name="customer_code" id="customer_code">
                                <input type="text" id="search-customer" placeholder="Search Customer..."
                                    class="form-control mb-2">
                                <div id="customer-search-result" class="mt-1"></div>
                                <div id="customer-address" class="text-muted small mt-2"></div>
                            </div>

                            <div class="form-group col-md-6">
                                <label>PPN</label><br>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ppn" value="yes" checked>
                                    <label class="form-check-label">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="ppn" value="no">
                                    <label class="form-check-label">No</label>
                                </div>
                            </div>

                            <div class="form-row mb-2">
                                <div class="form-group col-md-4">
                                    <label>Shipping 1</label>
                                    <select name="ship_1" class="form-control select-shipping"
                                        data-target="#ship1-address">
                                        <option value="">-- Select Shipping --</option>
                                        @foreach ($shippings as $shipping)
                                            <option value="{{ $shipping->shipping_code }}"
                                                data-address="{{ $shipping->address }}">
                                                {{ $shipping->shipping_name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <small id="ship1-address" class="text-muted shipping-address d-none"></small>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Shipping 2</label>
                                    <select name="ship_2" class="form-control select-shipping"
                                        data-target="#ship2-address">
                                        <option value="">-- Select Shipping --</option>
                                        @foreach ($shippings as $shipping)
                                            <option value="{{ $shipping->shipping_code }}"
                                                data-address="{{ $shipping->address }}">
                                                {{ $shipping->shipping_name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <small id="ship2-address" class="text-muted shipping-address d-none"></small>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Term of Payment (days)</label>
                                    <input type="number" name="top" id="top" class="form-control" min="0"
                                        placeholder="Ex: 30">
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION: Product Search & Table (Full Width) --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">Product Selection</div>
                        <div class="card-body">

                            <!-- Product Selection area tetap seperti sebelumnya, tapi kita pakai product-search-result sebagai trigger modal -->
                            <div class="form-group position-relative mb-3 w-25">
                                <input type="text" id="search-product" placeholder="Search product..."
                                    class="form-control">
                                <div id="product-search-result" class="mt-1"></div>
                            </div>

                            <div class="table-responsive">
                                <table
                                    class="table table-bordered table-striped table-hover shadow-sm text-center align-middle"
                                    id="product-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Packing</th>
                                            <th>Quantity by Unit</th>
                                            <th>Price/unit</th>
                                            <th>Discount</th>
                                            <th>Total</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION: Tax, Discount, Note --}}
            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">Additional Options</div>
                        <div class="card-body row">
                            <div class="form-group col-md-6">
                                <label>Note</label>
                                <textarea name="note" id="note" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="form-group col-md-6">
                                <div id="subtotal-ppn-section">
                                    <label>Subtotal</label>
                                    <input type="text" id="subtotal" name="subtotal" class="form-control mb-2"
                                        readonly>

                                    <label>PPN</label>
                                    <input type="text" id="ppn" name="ppn_amount" class="form-control mb-2"
                                        readonly>
                                </div>

                                <label>Grand Total</label>
                                <input type="text" id="grand_total" name="grand_total" class="form-control mb-2"
                                    readonly>
                            </div>

                        </div>
                    </div>

                    <div class="text-end mb-5">
                        <button type="button" class="btn btn-success px-4" onclick="confirmSubmit()">Create Sale
                            Order</button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Modal untuk input lengkap produk -->
        <div class="modal fade" id="productModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form id="productModalForm" onsubmit="return false;">
                        <div class="modal-header">
                            <h5 class="modal-title" id="productModalTitle">Tambah Produk</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">

                            <!-- Hidden: product ID -->
                            <input type="hidden" id="modal_product_id">

                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label">Product Name</label>
                                    <input type="text" id="modal_product_name" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Product Code</label>
                                    <input type="text" id="modal_product_code" class="form-control" readonly>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Packing</label>
                                    <select id="modal_packing" class="form-select"></select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Qty Packing</label>
                                    <input type="number" min="0" id="modal_qty_packing" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Unit</label>
                                    <select id="modal_unit" class="form-select"></select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label">Qty Unit</label>
                                    <input type="number" min="0" id="modal_qty_unit" class="form-control">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Price / unit</label>
                                    <input type="text" id="modal_price" class="form-control" placeholder="Rp. 0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Discount (ex: 10+5)</label>
                                    <input type="text" id="modal_discount" class="form-control" placeholder="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Total (Preview)</label>
                                    <input type="text" id="modal_total_preview" class="form-control" readonly>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" id="modal_save_btn" class="btn btn-primary">Simpan ke Tabel</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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

    {{-- Script Search Shipping --}}
    <script>
        $(document).ready(function() {
            $('.select-shipping').select2({
                placeholder: '-- Select Shipping --',
                allowClear: true,
                width: '100%',
                templateResult: formatShipping,
                templateSelection: formatShippingSelection
            });

            $('.select-shipping').on('change', function() {
                const selected = $(this).find(':selected');
                const address = selected.data('address') || '';
                const target = $(this).data('target');

                if (address) {
                    $(target).text(address).removeClass('d-none');
                } else {
                    $(target).text('').addClass('d-none');
                }
            });


            function formatShipping(shipping) {
                if (!shipping.id) return shipping.text;

                const address = $(shipping.element).data('address');

                return $(`
                <div>
                    <div><strong>${shipping.text}</strong></div>
                    <div style="font-size: 12px; color: #666;">${address || ''}</div>
                </div>
            `);
            }

            function formatShippingSelection(shipping) {
                return shipping.text;
            }
        });
    </script>


    {{-- Script Search Customer --}}
    <script>
        const customerInput = document.getElementById('search-customer');
        const resultBox = document.getElementById('customer-search-result');
        const addressBox = document.getElementById('customer-address');

        customerInput.addEventListener('keyup', function() {
            const query = this.value;

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
                        item.classList.add('p-2', 'border-bottom', 'search-result-item');
                        item.style.cursor = 'pointer';

                        item.innerHTML = `
                            <div class="fw-semibold">${customer.customer_name}</div>
                            <div class="text-muted small">${customer.address}</div>
                        `;

                        item.onclick = () => {
                            // set input utama
                            customerInput.value = customer.customer_name;

                            // set hidden customer_code
                            document.getElementById('customer_code').value = customer.customer_code;

                            // tampilkan address di bawah field
                            addressBox.textContent = customer.address;

                            // clear result
                            resultBox.innerHTML = '';
                        };

                        resultBox.appendChild(item);
                    });

                    resultBox.classList.add('border', 'bg-white', 'shadow-sm');
                });
        });
    </script>

    {{-- Script Search Product & Modal Handling --}}
    <script>
        document.getElementById('search-product').addEventListener('input', function() {
            const query = this.value;

            if (query.length < 2) {
                document.getElementById('product-search-result').innerHTML = '';
                return;
            }

            fetch(`/products-search?q=${query}`)
                .then(res => res.json())
                .then(products => {
                    const resultDiv = document.getElementById('product-search-result');
                    resultDiv.innerHTML = '';

                    products.forEach(product => {
                        const item = document.createElement('div');
                        item.classList.add('list-group-item', 'list-group-item-action');
                        item.textContent = `${product.product_name}`;
                        item.style.cursor = 'pointer';

                        item.addEventListener('click', function() {
                            openModalWithProduct(product);
                            resultDiv.innerHTML = '';
                            document.getElementById('search-product').value = '';
                        });

                        resultDiv.appendChild(item);
                    });
                });
        });

        /* --- Utilities (format rupiah) --- */
        function formatRupiah(number) {
            if (number === '' || number === null || isNaN(number)) return '-';
            const n = parseInt(number);
            const whole = n.toString();
            return 'Rp. ' + whole.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        function parseRupiahToNumber(str) {
            if (!str) return 0;
            const digits = str.toString().replace(/[^\d]/g, '');
            return parseInt(digits) || 0;
        }

        const priceInput = document.getElementById('modal_price');

        priceInput.addEventListener('input', function(e) {
            let value = e.target.value;

            // Ambil hanya angka dari input (hilangkan huruf, titik, spasi, dll)
            value = value.replace(/[^\d]/g, '');

            // Jika kosong, jangan tampilkan Rp. biar user bisa ngetik nyaman
            if (value === '') {
                e.target.value = '';
                return;
            }

            // Ubah ke format rupiah
            e.target.value = formatRupiah(value);

            // Setelah diformat, otomatis posisi kursor ke akhir
            e.target.setSelectionRange(e.target.value.length, e.target.value.length);
        });



        /* --- Global state for modal packing/unit data per product --- */
        const modalState = {
            packingData: [], // product_packings
            allPackings: [],
            allUnits: []
        };

        /* Bootstrap modal instance (assumes bootstrap 5 is loaded) */
        const productModalEl = document.getElementById('productModal');
        const bsProductModal = new bootstrap.Modal(productModalEl);

        /* Open modal with product basic info and fetch its packings/units */
        function openModalWithProduct(product, existingRow = null) {
            // fill basic fields
            document.getElementById('modal_product_id').value = product.id;
            document.getElementById('modal_product_name').value = product.product_name;
            document.getElementById('modal_product_code').value = product.product_code;
            document.getElementById('modal_qty_packing').value = '';
            document.getElementById('modal_qty_unit').value = '';
            document.getElementById('modal_price').value = '';
            document.getElementById('modal_discount').value = '';
            document.getElementById('modal_total_preview').value = '';

            document.getElementById('productModalTitle').textContent = existingRow ? 'Edit Produk' : 'Tambah Produk';
            document.getElementById('modal_save_btn').dataset.editingRowId = existingRow ? existingRow.dataset.rowId : '';

            // fetch packing/unit data
            fetch(`/product-packings/${product.id}`)
                .then(res => res.json())
                .then(({
                    all_packings,
                    all_units,
                    product_packings
                }) => {
                    modalState.packingData = product_packings || [];
                    modalState.allPackings = all_packings || [];
                    modalState.allUnits = all_units || [];

                    // populate selects
                    const packingSel = document.getElementById('modal_packing');
                    const unitSel = document.getElementById('modal_unit');

                    packingSel.innerHTML = '<option value="">Pilih Packing</option>';
                    modalState.allPackings.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.packing_id;
                        opt.textContent = p.packing_name;
                        packingSel.appendChild(opt);
                    });

                    unitSel.innerHTML = '<option value="">Pilih Unit</option>';
                    modalState.allUnits.forEach(u => {
                        const opt = document.createElement('option');
                        opt.value = u.unit_id;
                        opt.textContent = u.unit_name;
                        unitSel.appendChild(opt);
                    });

                    // If editing existingRow, prefill modal with row's values
                    if (existingRow) {
                        prefillModalFromRow(existingRow);
                    }

                    bsProductModal.show();
                });
        }

        /* Prefill modal fields when editing */
        function prefillModalFromRow(row) {
            // read data attributes / cell texts
            document.getElementById('modal_product_id').value = row.dataset.productId;
            document.getElementById('modal_product_name').value = row.querySelector('.cell-name').textContent.trim();
            document.getElementById('modal_product_code').value = row.querySelector('.cell-code').textContent.trim();

            // packing/unit: we stored ids in data attributes when creating row
            const packingId = row.dataset.packingId || '';
            const unitId = row.dataset.unitId || '';
            const qtyPacking = row.dataset.qtyPacking || '';
            const qtyUnit = row.dataset.qtyUnit || '';
            const price = row.dataset.price || '';
            const discount = row.dataset.discount || '';

            document.getElementById('modal_packing').value = packingId;
            document.getElementById('modal_qty_packing').value = qtyPacking;
            document.getElementById('modal_unit').value = unitId;
            document.getElementById('modal_qty_unit').value = qtyUnit;
            document.getElementById('modal_price').value = price ? formatRupiah(price) : '';
            document.getElementById('modal_discount').value = discount;

            // preview total
            updateModalPreviewTotal();
        }

        /* Calculate total in modal (like per-line calc) */
        function calculateTotalFromModalInputs() {
            const qtyUnit = parseFloat(document.getElementById('modal_qty_unit').value) || 0;
            const priceText = document.getElementById('modal_price').value || '';
            const discountText = document.getElementById('modal_discount').value || '';

            const price = parseRupiahToNumber(priceText) || 0;
            let total = qtyUnit * price;

            if (discountText.trim() !== '') {
                const ds = discountText.split('+').map(d => d.replace(',', '.')).map(parseFloat).map(x => isNaN(x) ? 0 : x);
                ds.forEach(d => {
                    total -= total * (d / 100);
                });
            }
            return Math.round(total);
        }

        function updateModalPreviewTotal() {
            const total = calculateTotalFromModalInputs();
            document.getElementById('modal_total_preview').value = total ? formatRupiah(total) : 'Rp. 0';
        }

        /* Hook modal input changes to preview update */
        ['modal_qty_packing', 'modal_qty_unit', 'modal_price', 'modal_discount', 'modal_packing', 'modal_unit'].forEach(
            id => {
                document.addEventListener('input', (e) => {
                    if (e.target && e.target.id === id) {
                        // If packing changed and packing->unit conversion exists we may auto fill qty unit based on packing
                        if (id === 'modal_packing' || id === 'modal_qty_packing' || id === 'modal_unit') {
                            updateModalQtyUnitFromPacking();
                        }
                        updateModalPreviewTotal();
                    }
                });
            });

        /* When packing + unit selected, try fill qty unit by conversion */
        function updateModalQtyUnitFromPacking() {
            const packingId = parseInt(document.getElementById('modal_packing').value) || null;
            const unitId = parseInt(document.getElementById('modal_unit').value) || null;
            const qtyPackingVal = parseFloat(document.getElementById('modal_qty_packing').value) || 0;

            if (packingId && unitId) {
                const matched = modalState.packingData.find(p => p.packing_id === packingId && p.unit_id === unitId);
                if (matched) {
                    document.getElementById('modal_qty_unit').value = qtyPackingVal * matched.conversion_value;
                } else {
                    // tidak ditemukan: kosongkan agar user isi manual
                    document.getElementById('modal_qty_unit').value = '';
                }
            }
        }

        /* Save tombol di modal: tambah atau update row */
        document.getElementById('modal_save_btn').addEventListener('click', function() {

            const productId = document.getElementById('modal_product_id').value;
            const productName = document.getElementById('modal_product_name').value;
            const productCode = document.getElementById('modal_product_code').value;
            const packingId = document.getElementById('modal_packing').value || '';
            const packingText = document.getElementById('modal_packing').selectedOptions.length ? document
                .getElementById('modal_packing').selectedOptions[0].text : '';
            const qtyPacking = document.getElementById('modal_qty_packing').value || '';
            const unitId = document.getElementById('modal_unit').value || '';
            const unitText = document.getElementById('modal_unit').selectedOptions.length ? document.getElementById(
                'modal_unit').selectedOptions[0].text : '';
            const qtyUnit = document.getElementById('modal_qty_unit').value || '';
            const priceText = document.getElementById('modal_price').value || '';
            const priceNumeric = parseRupiahToNumber(priceText);
            const discount = document.getElementById('modal_discount').value || '0';
            const total = calculateTotalFromModalInputs();

            // Build row data attributes
            const rowData = {
                productId,
                productName,
                productCode,
                packingId,
                packingText,
                qtyPacking,
                unitId,
                unitText,
                qtyUnit,
                priceNumeric,
                discount,
                total
            };

            const editingRowId = this.dataset.editingRowId || '';

            if (editingRowId) {
                // update existing row (find by data-row-id)
                const existingRow = document.querySelector(
                    `#product-table tbody tr[data-row-id="${editingRowId}"]`);
                if (existingRow) {
                    fillRowWithData(existingRow, rowData);
                }
            } else {
                // create new row with unique id
                const newRowId = 'r' + Date.now();
                const tbody = document.querySelector('#product-table tbody');
                const tr = document.createElement('tr');
                tr.dataset.rowId = newRowId;
                tr.dataset.productId = productId;
                fillRowWithData(tr, rowData);
                tbody.appendChild(tr);
            }

            // clear editing flag & hide modal
            delete this.dataset.editingRowId;
            bsProductModal.hide();
            calculateSummary();
        });

        /* Fill a <tr> element with textual cells + hidden inputs stored as data attributes */
        function fillRowWithData(tr, data) {
            // Set dataset for easy edit later
            tr.dataset.productId = data.productId;
            tr.dataset.packingId = data.packingId;
            tr.dataset.qtyPacking = data.qtyPacking;
            tr.dataset.unitId = data.unitId;
            tr.dataset.qtyUnit = data.qtyUnit;
            tr.dataset.price = data.priceNumeric;
            tr.dataset.discount = data.discount;
            tr.dataset.total = data.total;

            // Build inner HTML as text cells (rapi) and include hidden inputs (optional)
            tr.innerHTML = `
                <td class="cell-name text-start">${escapeHtml(data.productName)}</td>
                <td class="cell-code">${escapeHtml(data.productCode)}</td>
                <td class="cell-packing">${data.qtyPacking ? (data.qtyPacking + ' ' + escapeHtml(data.packingText)) : escapeHtml(data.packingText)}</td>
                <td class="cell-qtyunit">${data.qtyUnit ? (data.qtyUnit + ' ' + escapeHtml(data.unitText)) : escapeHtml(data.unitText)}</td>
                <td class="cell-price">${data.priceNumeric ? formatRupiah(data.priceNumeric) : '-'}</td>
                <td class="cell-discount">${data.discount ? escapeHtml(data.discount) : '-'}</td>
                <td class="cell-total">${data.total ? formatRupiah(data.total) : '-'}</td>
                <td class="cell-action">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit" title="Edit"><i class="fas fa-pen"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete" title="Hapus"><i class="fas fa-trash"></i></button>
                    <!-- Hidden inputs for form submit -->
                    <input type="hidden" name="id_product[]" value="${escapeHtml(data.productId)}">
                    <input type="hidden" name="product_name[]" value="${escapeHtml(data.productName)}">  
                    <input type="hidden" name="packing[]" value="${escapeHtml(data.packingId)}">
                    <input type="hidden" name="qty_packing[]" value="${escapeHtml(data.qtyPacking)}">
                    <input type="hidden" name="unit[]" value="${escapeHtml(data.unitId)}">
                    <input type="hidden" name="qty[]" value="${escapeHtml(data.qtyUnit)}">
                    <input type="hidden" name="unit_price[]" value="${escapeHtml(data.priceNumeric)}">
                    <input type="hidden" name="discount[]" value="${escapeHtml(data.discount || '0')}">
                    <input type="hidden" name="line_total[]" value="${escapeHtml(data.total)}">
                </td>
            `;

            // bind edit / delete events (since row might be newly created)
            tr.querySelector('.btn-edit').addEventListener('click', function() {
                openRowInModalForEdit(tr);
            });
            tr.querySelector('.btn-delete').addEventListener('click', function() {
                if (confirm('Hapus baris ini?')) {
                    tr.remove();
                    calculateSummary();
                }
            });
        }

        /* escapeHtml utility to avoid XSS in text nodes */
        function escapeHtml(text) {
            if (text === null || text === undefined) return '';
            return String(text)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        /* Open existing row into modal to edit */
        function openRowInModalForEdit(tr) {
            // Build a pseudo product object to pass to openModalWithProduct()
            const product = {
                id: tr.dataset.productId,
                product_name: tr.querySelector('.cell-name').textContent.trim(),
                product_code: tr.querySelector('.cell-code').textContent.trim()
            };
            // pass existing row so modal will prefill
            openModalWithProduct(product, tr);
        }

        /* After any row changes, recalc summary (subtotal/ppn/grand) */
        function calculateSummary() {
            const rows = document.querySelectorAll('#product-table tbody tr');
            let grandtotal = 0;
            rows.forEach(row => {
                const t = parseInt(row.dataset.total) || 0;
                grandtotal += t;
            });

            // cek status PPN
            const ppnStatus = document.querySelector('input[name="ppn"]:checked')?.value || 'no';

            let subtotal = 0,
                ppn = 0;
            if (ppnStatus === 'yes') {
                // assume grandtotal includes PPN: DPP = grand / 1.11
                subtotal = Math.round(grandtotal / 1.11);
                ppn = grandtotal - subtotal;
            } else {
                subtotal = grandtotal;
                ppn = 0;
            }

            // set ke elemen input (format rupiah)
            if (document.getElementById('subtotal')) document.getElementById('subtotal').value = formatRupiah(subtotal);
            if (document.getElementById('ppn')) document.getElementById('ppn').value = formatRupiah(ppn);
            if (document.getElementById('grand_total')) document.getElementById('grand_total').value = formatRupiah(
                grandtotal);
        }

        /* When modal is hidden, clear editing id to avoid stale state */
        productModalEl.addEventListener('hidden.bs.modal', function() {
            document.getElementById('modal_save_btn').dataset.editingRowId = '';
        });
    </script>

    {{-- Script Load Draft --}}
    <script>
        function loadDraft() {
            fetch('/draft/load?form_type=sale_order')
                .then(res => res.json())
                .then(draft => {
                    if (!draft || !draft.data) return;

                    const data = draft.data;

                    // HEADER: isi field utama
                    if (data.order_number) document.getElementById('order_number').value = data.order_number;
                    if (data.order_date) document.getElementById('date').value = data.order_date;
                    if (data.top) document.getElementById('top').value = data.top;
                    if (data.note) document.getElementById('note').value = data.note;

                    // PPN
                    if (data.ppn_status) {
                        const ppnEl = document.querySelector(`input[name="ppn"][value="${data.ppn_status}"]`);
                        if (ppnEl) ppnEl.checked = true;
                    }

                    // CUSTOMER (tampilkan name di input, simpan code di hidden)
                    if (data.supplier_code) document.getElementById('customer_code').value = data.supplier_code;
                    if (data.supplier_name) document.getElementById('search-customer').value = data.supplier_name;

                    // SHIPPING
                    if (data.ship_1) {
                        const sel1 = document.querySelector('select[name="ship_1"]');
                        if (sel1) sel1.value = data.ship_1;
                    }
                    if (data.ship_2) {
                        const sel2 = document.querySelector('select[name="ship_2"]');
                        if (sel2) sel2.value = data.ship_2;
                    }

                    // PRODUCTS
                    if (Array.isArray(data.products) && data.products.length) {
                        const tbody = document.querySelector('#product-table tbody');
                        tbody.innerHTML = ''; // clear dulu

                        data.products.forEach((p, idx) => {
                            const tr = document.createElement('tr');
                            const rowId = 'r' + Date.now() + idx; // unique id

                            // ambil name untuk packing/unit kalau ada
                            const packingName = p.packing_name ||
                                ''; // nanti bisa diambil dari modalState.allPackings
                            const unitName = p.unit_name || '';

                            tr.dataset.rowId = rowId;
                            tr.dataset.productId = p.id_product || '';
                            tr.dataset.packingId = p.packing || '';
                            tr.dataset.qtyPacking = p.qty_packing || '';
                            tr.dataset.unitId = p.unit || '';
                            tr.dataset.qtyUnit = p.qty_unit || '';
                            tr.dataset.price = p.price || '';
                            tr.dataset.discount = p.discount || '';
                            tr.dataset.total = p.total || '';

                            tr.innerHTML = `
                        <td class="cell-name text-start">${escapeHtml(p.name)}</td>
                        <td class="cell-code">${escapeHtml(p.code)}</td>
                        <td class="cell-packing">${p.qty_packing ? (p.qty_packing + ' ' + packingName) : packingName}</td>
                        <td class="cell-qtyunit">${p.qty_unit ? (p.qty_unit + ' ' + unitName) : unitName}</td>
                        <td class="cell-price">${p.price ? formatRupiah(p.price) : '-'}</td>
                        <td class="cell-discount">${p.discount ? p.discount : '-'}</td>
                        <td class="cell-total">${p.total ? formatRupiah(p.total) : '-'}</td>
                        <td class="cell-action">
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit" title="Edit"><i class="fas fa-pen"></i></button>
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" title="Hapus"><i class="fas fa-trash"></i></button>
                            <input type="hidden" name="id_product[]" value="${p.id_product}">
                            <input type="hidden" name="product_name[]" value="${escapeHtml(p.name)}">  
                            <input type="hidden" name="packing[]" value="${p.packing}">
                            <input type="hidden" name="qty_packing[]" value="${p.qty_packing}">
                            <input type="hidden" name="unit[]" value="${p.unit}">
                            <input type="hidden" name="qty[]" value="${p.qty_unit}">
                            <input type="hidden" name="unit_price[]" value="${p.price}">
                            <input type="hidden" name="discount[]" value="${p.discount}">
                            <input type="hidden" name="line_total[]" value="${p.total}">
                        </td>
                    `;

                            // bind edit/delete
                            tr.querySelector('.btn-edit').addEventListener('click', () => openRowInModalForEdit(
                                tr));
                            tr.querySelector('.btn-delete').addEventListener('click', () => {
                                if (confirm('Hapus baris ini?')) {
                                    tr.remove();
                                    calculateSummary();
                                }
                            });

                            tbody.appendChild(tr);
                        });
                    }

                    // SUBTOTAL, PPN, GRAND
                    if (data.subtotal && document.getElementById('subtotal')) document.getElementById('subtotal')
                        .value = data.subtotal;
                    if (data.ppn && document.getElementById('ppn')) document.getElementById('ppn').value = data.ppn;
                    if (data.grand_total && document.getElementById('grand_total')) document.getElementById(
                        'grand_total').value = data.grand_total;
                });
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadDraft();
        });
    </script>

    {{-- Script PPN Handling --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ppnRadios = document.querySelectorAll('input[name="ppn"]');
            const subtotalPpnSection = document.getElementById("subtotal-ppn-section");

            // Inisialisasi kondisi saat load
            toggleDppPpn();

            // Cek setiap kali user klik radio
            ppnRadios.forEach(radio => {
                radio.addEventListener('change', toggleDppPpn);
            });

            function toggleDppPpn() {
                const isPpnYes = document.querySelector('input[name="ppn"]:checked').value === "yes";

                subtotalPpnSection.style.display = isPpnYes ? 'block' : 'none';

                calculateSummary();
            }
        });
    </script>

    <script>
        // kirim URL dan CSRF token ke JS
        const draftSaveConfig = {
            url: "{{ route('drafts.save') }}",
            csrf: "{{ csrf_token() }}"
        };
    </script>

    <script src="{{ asset('js/draft_createSO.js') }}"></script>
@endsection
