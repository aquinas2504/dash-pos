@extends('Component.main_admin')

<style>
    .search-result-item:hover {
        background-color: #f8f9fa;
    }

    .search-result-product:hover {
        background-color: #f8f9fa;
    }

    #supplier-search-result {
        max-height: 200px;
        overflow-y: auto;
        position: absolute;
        z-index: 1000;
        width: 100%;
    }

    .form-group.position-relative {
        position: relative;
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
        <form action="{{ route('purchases.store') }}" method="POST" id="purchase-form">
            @csrf
            <div class="row">
                <div class="col-12">

                    {{-- SECTION: Order Info --}}
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">Purchase Order Info</div>
                        <div class="card-body row">
                            <div class="form-group col-md-6">
                                <label>Order Number</label>
                                <input type="text" id="order_number" name="order_number" required class="form-control">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Date</label>
                                <input type="date" id="order_date" name="order_date" value="{{ date('Y-m-d') }}"
                                    class="form-control">
                            </div>

                            <div class="form-group col-md-6 position-relative">
                                <label>Supplier</label>
                                <input type="hidden" name="supplier_code" id="supplier_code">
                                <input type="text" id="search-supplier" placeholder="Search Supplier..."
                                    class="form-control mb-2">
                                <div id="supplier-search-result" class="mt-1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION: Table (Full Width) --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-secondary text-white">Product Selection</div>
                        <div class="card-body">

                            <div class="d-flex align-items-center gap-2 mb-3">
                                <!-- Dropdown Create PO -->
                                <div class="dropdown me-2">
                                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownPoMode"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        Create PO
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="dropdownPoMode">
                                        <li><a class="dropdown-item po-mode" href="#" data-mode="so">Berdasarkan
                                                SO</a></li>
                                        <li><a class="dropdown-item po-mode" href="#" data-mode="manual">Manual</a>
                                        </li>
                                    </ul>
                                </div>

                                <!-- Hidden input untuk menyimpan mode PO -->
                                <input type="hidden" name="po_mode" id="po_mode" value="">
                                <!-- Hidden input untuk PPN status SO mode -->
                                <input type="hidden" name="ppn_status_so" id="ppn_status_so" value="yes">

                                <!-- Button List Pending Sales -->
                                <button type="button" class="btn btn-outline-secondary d-none" id="btn-list-so"
                                    data-bs-toggle="modal" data-bs-target="#modal-sales-pending">
                                    <i class="bi bi-list-ul"></i> List Pending Sales
                                </button>

                                <!-- Form Manual -->
                                <div id="manual-form" class="d-none">
                                    <div class="d-flex align-items-center gap-3">
                                        <!-- Search Input -->
                                        <div class="flex-grow-1">
                                            <input type="text" class="form-control" placeholder="Search Product..."
                                                id="search-product">
                                            <div id="search-result-product" style="display:none;"></div>
                                        </div>

                                        <!-- Radio Buttons -->
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input radio-ppn" type="radio"
                                                name="ppn_option_manual" id="ppn-yes" value="yes" checked>
                                            <label class="form-check-label" for="ppn-yes">PPN</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input radio-ppn" type="radio"
                                                name="ppn_option_manual" id="ppn-no" value="no">
                                            <label class="form-check-label" for="ppn-no">Non-PPN</label>
                                        </div>
                                    </div>
                                </div>

                            </div>


                            <div class="table-responsive">
                                <table
                                    class="table table-bordered table-striped table-hover shadow-sm text-center align-middle"
                                    id="product-table">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="d-none">ID</th>
                                            <th>Name</th>
                                            <th>Code</th>
                                            <th>Packing</th>
                                            <th>QTY by UNIT</th>
                                            <th style="width: 150px">Price/unit</th>
                                            <th style="width: 110px">Discount</th>
                                            <th style="width: 180px">Total</th>
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
                            <!-- Kiri: Note -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Note</label>
                                    <textarea name="note" class="form-control" rows="3"></textarea>
                                </div>
                            </div>

                            <!-- Kanan: Subtotal, PPN, Grand Total -->
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-12">
                                        <div id="subtotal-ppn-group">
                                            <label>Subtotal</label>
                                            <input type="text" id="subtotal" name="subtotal"
                                                class="form-control mb-2" readonly>

                                            <label>PPN</label>
                                            <input type="text" id="ppn" name="ppn_amount"
                                                class="form-control mb-2" readonly>
                                        </div>

                                        <div>
                                            <label>Grand Total</label>
                                            <input type="text" id="grand_total" name="grand_total"
                                                class="form-control mb-2" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mb-5">
                        <button type="button" class="btn btn-success px-4" onclick="confirmSubmit()">Create Purchase
                            Order</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="modal-sales-pending" tabindex="-1" aria-labelledby="salesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="salesModalLabel">Select Pending Sales</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm filter-ppn" data-filter="yes">PPN
                            Yes</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm filter-ppn" data-filter="no">PPN
                            No</button>
                    </div>

                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Select</th>
                                <th>Order Number</th>
                                <th>Order Date</th>
                                <th>Customer</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody id="pending-sales-list">
                            <!-- Data fetched via AJAX -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button id="import-sales-details" class="btn btn-primary">Import Selected Orders</button>
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    {{-- Notif Sukses --}}
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

    {{-- Confirm Submit --}}
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

    {{-- Notif Error --}}
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


    {{-- Search Supplier --}}
    <script>
        const supplierInput = document.getElementById('search-supplier');
        const resultBox = document.getElementById('supplier-search-result');

        supplierInput.addEventListener('keyup', function() {
            const query = this.value;

            if (query.length < 2) {
                resultBox.innerHTML = '';
                return;
            }

            fetch(`/suppliers-search?q=${query}`)
                .then(res => res.json())
                .then(data => {
                    resultBox.innerHTML = '';

                    if (data.length === 0) {
                        resultBox.innerHTML = '<div class="text-muted px-3 py-2">No suppliers found.</div>';
                        return;
                    }

                    data.forEach(supplier => {
                        const item = document.createElement('div');
                        item.textContent = supplier.supplier_name;
                        item.classList.add('p-2', 'border-bottom', 'search-result-item');
                        item.style.cursor = 'pointer';

                        item.onclick = () => {
                            supplierInput.value = supplier.supplier_name;

                            // Optional hidden input to store supplier_code
                            let hiddenInput = document.getElementById('supplier_code');
                            if (!hiddenInput) {
                                hiddenInput = document.createElement('input');
                                hiddenInput.type = 'hidden';
                                hiddenInput.name = 'supplier_code';
                                hiddenInput.id = 'supplier_code';
                                document.getElementById('purchase-form').appendChild(hiddenInput);
                            }
                            hiddenInput.value = supplier.supplier_code;

                            resultBox.innerHTML = '';
                        };

                        resultBox.appendChild(item);
                    });

                    resultBox.classList.add('border', 'bg-white', 'shadow-sm');
                });
        });
    </script>


    {{-- Script Utama --}}
    <script>
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            window.modalData = {};

            let currentMode = null; // mode Create PO: 'so' or 'manual'
            let selectedDetails = {};
            let ppnStatus = 'yes'; // Default global PPN status (untuk mode SO)

            // =============== Untuk tampilan qty di modal ===============
            function formatQuantity(qty) {
                if (Number.isInteger(qty)) return qty;          // misal 2 → 2
                let str = qty.toFixed(2);                       // misal 2.5 → "2.50", 2.55 → "2.55"
                str = str.replace(/\.?0+$/, '');                // hapus nol yang tidak perlu
                return str;
            }

            // =============== Load Pending Sales (Berdasarkan SO) ===============
            function loadPendingSales(filter = 'yes') {
                ppnStatus = filter; // <-- Simpan status ke global

                const existingIds = [...document.querySelectorAll('input[name="id_sale_detail[]"]')]
                    .flatMap(input => input.value.split(','))
                    .map(id => id.trim())
                    .filter(Boolean);

                $.get('/pending-sales', {
                    ppn: filter
                }, function(data) {
                    let rows = '';
                    window.modalData = {};

                    data.forEach(sale => {
                        window.modalData[sale.order_number] = sale.details;

                        let allProductChecked = true;

                        const productList = sale.details.map(d => {
                            const isChecked = existingIds.includes(String(d.detail_id));
                            if (!isChecked) allProductChecked = false;

                            return `
                    <li>
                        <label class="form-check-label">
                            <input type="checkbox" 
                                class="form-check-input select-product" 
                                data-detail-id="${d.detail_id}" 
                                data-order-number="${sale.order_number}"
                                ${isChecked ? 'checked' : ''}>
                            ${d.product_name} ${d.qty_packing} ${d.packing} (${formatQuantity(d.quantity)} ${d.unit}) 
                        </label>
                    </li>
                `;
                        }).join('');

                        rows += `
                <tr>
                    <td>
                        <input type="checkbox" class="select-sale" value="${sale.order_number}"
                            ${allProductChecked ? 'checked' : ''}>
                    </td>
                    <td>${sale.order_number}</td>
                    <td>${sale.sale_date || sale.order_date}</td>
                    <td>${sale.customer_name || '-'}</td>
                    <td>
                        <ul class="list-unstyled">${productList}</ul>
                    </td>
                </tr>
            `;
                    });

                    $('#pending-sales-list').html(rows);

                    // Tampilkan atau sembunyikan DPP dan PPN berdasarkan status filter
                    if (filter === 'no') {
                        $('#dpp').val('');
                        $('#ppn').val('');
                        $('#dpp-ppn-section').hide();
                    } else {
                        $('#dpp-ppn-section').show();
                    }

                    // Rehitung total juga
                    updateSummary();
                });
            }


            // =============== Show Modal Pending Sales & Default Load ===============
            $('#modal-sales-pending').on('show.bs.modal', function() {
                $('.filter-ppn').removeClass('active');
                $('.filter-ppn[data-filter="yes"]').addClass('active');
                ppnStatus = 'yes'; // Set default
                loadPendingSales('yes');
            });


            // =============== Filter PPN Click Handler ===============
            $(document).on('click', '.filter-ppn', function() {
                const filter = $(this).data('filter');
                ppnStatus = filter; // Simpan status

                // Update hidden input ppn_status_so
                $('#ppn_status_so').val(filter);

                $('.filter-ppn').removeClass('active');
                $(this).addClass('active');

                selectedDetails = {};
                renderTable();
                loadPendingSales(filter);
            });


            // =============== Select product checkbox sync with select-sale checkbox ===============
            $(document).on('change', '.select-product', function() {
                const $row = $(this).closest('tr');
                const allChecked = $row.find('.select-product').length === $row.find(
                    '.select-product:checked').length;
                $row.find('.select-sale').prop('checked', allChecked);
            });

            $(document).on('change', '.select-sale', function() {
                const $row = $(this).closest('tr');
                const state = $(this).is(':checked');
                $row.find('.select-product').prop('checked', state);
            });

            // =============== Utilities ===============
            function formatRupiah(angka) {
                let number = parseFloat(angka) || 0;

                // Format dengan 2 digit desimal (biar rapih untuk angka pecahan)
                return 'Rp. ' + number.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2
                });
            }


            function parseDiscount(discountStr) {
                if (!discountStr) return 1;

                const parts = discountStr.split('+').map(d => {
                    // ganti koma jadi titik dulu
                    d = d.replace(',', '.');
                    return parseFloat(d);
                }).filter(n => !isNaN(n));

                return parts.reduce((acc, d) => acc * (1 - d / 100), 1);
            }


            function recalculateTotal(row) {
                const priceRaw = row.find('.price-input').val().replace(/[^\d]/g, '');
                const price = parseFloat(priceRaw) || 0;
                const qty = parseFloat(row.attr('data-quantity')) || 0;
                const discount = row.find('.discount-input').val() || '0';
                const multiplier = parseDiscount(discount);
                const total = qty * price * multiplier;
                row.find('.total-cell').text(`Rp. ${total.toLocaleString()}`);

                // Simpan total dalam data attribute untuk perhitungan summary
                row.attr('data-total', total);

                // Update hidden inputs untuk form submission
                row.find('.input-price-hidden').val(price);
                row.find('.input-discount-hidden').val(discount);
                row.find('.input-total-hidden').val(total);
            }


            // Fungsi utama untuk update summary
            function updateSummary() {
                let grandTotal = 0;

                // Status PPN
                let hasPPN;
                if (currentMode === 'manual') {
                    hasPPN = $('.radio-ppn:checked').val() === 'yes';
                } else {
                    hasPPN = ppnStatus === 'yes';
                }

                // Hitung Grand Total dari semua baris
                $('#product-table tbody tr').each(function() {
                    const total = parseFloat($(this).attr('data-total')) || 0;
                    grandTotal += total;
                });

                // ✅ Selalu update Grand Total
                $('#grand_total').val(formatRupiah(Math.round(grandTotal)));

                if (hasPPN) {
                    const subtotal = Math.round(grandTotal / 1.11);
                    const ppn = Math.round(grandTotal - subtotal);

                    $('#subtotal').val(formatRupiah(subtotal));
                    $('#ppn').val(formatRupiah(ppn));
                    $('#subtotal-ppn-group').show();
                } else {
                    $('#subtotal').val('');
                    $('#ppn').val('');
                    $('#subtotal-ppn-group').hide(); // Grand Total tetap terlihat
                }
            }



            // =============== Render product table from selectedDetails ===============
            function renderTable() {
                const tbody = $('#product-table tbody');
                tbody.empty();

                Object.values(selectedDetails).forEach((item, index) => {
                    const key = `${item.product_id}_${item.packing}_${item.unit}`;
                    const row = $(`
                <tr data-key="${key}" data-quantity="${item.quantity}" data-total="0">
                    <td class="d-none">
                        <input type="hidden" name="id_sale_detail[]" value="${item.ids.join(',')}">
                        <input type="hidden" name="price[]" class="input-price-hidden">
                        <input type="hidden" name="discount[]" class="input-discount-hidden">
                        <input type="hidden" name="total[]" class="input-total-hidden">
                    </td>
                    <td>${item.name}</td>
                    <td>${item.code}</td>
                    <td>${item.qty_packing} ${item.packing}</td>
                    <td>${item.quantity} ${item.unit}</td>
                    <td>
                        <input type="text" class="form-control price-input" placeholder="Rp. 0" value="${item.price || ''}">
                    </td>
                    <td>
                        <input type="text" class="form-control discount-input" placeholder="0" value="${item.discount || ''}">
                    </td>
                    <td class="total-cell">Rp. 0</td>
                    <td>
                        <button type="button" class="btn btn-danger btn-sm remove-row">X</button>
                    </td>
                </tr>
                `);

                    row.find('.price-input').on('input', function() {
                        let value = this.value.replace(/[^\d]/g, '');
                        if (value) {
                            this.value = formatRupiah(value);
                        }
                        // Simpan nilai harga di objek selectedDetails
                        selectedDetails[key].price = this.value;
                        recalculateTotal(row);
                        updateSummary();
                    });

                    row.find('.discount-input').on('input', () => {
                        // Simpan nilai diskon di objek selectedDetails
                        selectedDetails[key].discount = row.find('.discount-input').val();
                        recalculateTotal(row);
                        updateSummary();
                    });

                    row.find('.remove-row').on('click', () => {
                        delete selectedDetails[key];
                        row.remove(); // Hanya hapus baris ini saja tanpa render ulang semua
                        updateSummary();
                    });

                    tbody.append(row);

                    // Hitung total pada saat render
                    recalculateTotal(row);
                });

                updateSummary();
            }

            // =============== Import dari modal sales ke table ===============
            $('#import-sales-details').on('click', function() {
                const selected = $('.select-product:checked');
                if (!selected.length) return alert('Pilih minimal satu produk.');

                selectedDetails = {};

                selected.each(function() {
                    const $input = $(this);
                    const detailId = $input.data('detail-id');
                    const orderNumber = $input.data('order-number');
                    const detail = findDetailInModal(orderNumber, detailId);
                    if (!detail) return;

                    // debug
                    console.log('Detail:', detail);

                    const key = `${detail.product_id}_${detail.packing}_${detail.unit}`;
                    if (!selectedDetails[key]) {
                        selectedDetails[key] = {
                            ids: [detail.detail_id],
                            product_id: detail.product_id,
                            name: detail.product_name,
                            code: detail.product_code,
                            packing: detail.packing,
                            qty_packing: detail.qty_packing,
                            quantity: detail.quantity,
                            unit: detail.unit
                        };
                    } else {
                        selectedDetails[key].ids.push(detail.detail_id);
                        selectedDetails[key].qty_packing += detail.qty_packing;
                        selectedDetails[key].quantity += detail.quantity;
                    }
                });

                $('#modal-sales-pending').modal('hide');
                renderTable();
            });

            function findDetailInModal(orderNumber, detailId) {
                return window.modalData?.[orderNumber]?.find(d => d.detail_id == detailId);
            }

            // =============== Clear product table function ===============
            function clearProductTable() {
                selectedDetails = {};
                $('#product-table tbody').empty();
                updateSummary();
            }

            // =============== Create PO mode switcher ===============
            $(document).on('click', '.po-mode', function(e) {
                e.preventDefault();
                const mode = $(this).data('mode');
                currentMode = mode;

                // Set nilai input hidden untuk mode PO
                $('#po_mode').val(mode);

                clearProductTable();

                if (mode === 'so') {
                    $('#btn-list-so').removeClass('d-none');
                    $('#manual-form').addClass('d-none');
                } else if (mode === 'manual') {
                    $('#btn-list-so').addClass('d-none');
                    $('#manual-form').removeClass('d-none');
                }
            });

            // Reset product table saat ganti opsi PPN di mode Manual
            $('.radio-ppn').on('change', function() {
                if (currentMode === 'manual') {
                    updateSummary(); // biar langsung hitung ulang subtotal & PPN
                }
            });


            // DARI SINI MULAI YANG MANUAL

            $('#search-product').on('input', function() {
                const keyword = $(this).val();
                if (keyword.length >= 2 && currentMode === 'manual') {
                    $.get('/search-products', {
                        keyword
                    }, function(data) {
                        let html = '<ul class="list-group">';
                        data.forEach(product => {
                            html += `<li class="list-group-item search-result-product" 
                            data-id="${product.id}" 
                            data-name="${product.product_name}" 
                            data-code="${product.product_code}">
                            ${product.product_name}
                        </li>`;
                        });
                        html += '</ul>';
                        $('#search-result-product').html(html).show(); // ✅ ID cocok
                    });
                } else {
                    $('#search-result-product').hide(); // ✅ ID cocok
                }
            });



            // event klik produk dari list
            $(document).on('click', '.search-result-product', function() {
                const productId = $(this).data('id');
                const name = $(this).data('name');
                const code = $(this).data('code');

                console.log({
                    productId,
                    name,
                    code
                }); // 🔍 Debug

                $('#search-result-product').hide();
                $('#search-product').val('');

                $.get(`/product-packings/${productId}`, function(data) {
                    addProductRow(productId, name, code, data); // langsung data karena sudah array
                });

            });



            function addProductRow(productId, name, code, data) {
                const rowId = `manual-${Date.now()}`;
                const {
                    all_packings,
                    all_units,
                    product_packings
                } = data;

                let packingOptions = '<option value="">-- Pilih Packing --</option>';
                all_packings.forEach(p => {
                    packingOptions +=
                        `<option value="${p.packing_name}" data-id="${p.packing_id}">${p.packing_name}</option>`;
                });

                let unitOptions = '<option value="">-- Pilih Unit --</option>';
                all_units.forEach(u => {
                    unitOptions +=
                        `<option value="${u.unit_name}" data-id="${u.unit_id}">${u.unit_name}</option>`;
                });

                const row = $(`
                    <tr id="${rowId}" data-quantity="0" data-total="0">
                        <td><input type="hidden" name="id_product[]" value="${productId}">${name}</td>
                        <td>${code}</td>
                        <td>
                            <input type="number" name="qty_packing[]" class="form-control input-packing-qty" min="1" value="1">
                            <select name="packing[]" class="form-control select-packing">${packingOptions}</select>
                        </td>
                        <td>
                            <input type="text" name="qty_unit[]" class="form-control qty-unit">
                            <select name="unit[]" class="form-control select-unit">${unitOptions}</select>
                        </td>
                        <td><input type="text" name="price[]" class="form-control price-input" placeholder="Rp. 0"></td>
                        <td><input type="text" name="discount[]" class="form-control discount-input" placeholder="0"></td>
                        <td class="total-cell">Rp. 0</td>
                        <td><button type="button" class="btn btn-sm btn-danger remove-row">X</button></td>
                        <input type="hidden" name="total[]" class="input-total-hidden" value="0">
                    </tr>
                `).data('product_packings', product_packings); // Simpan untuk referensi update

                $('#product-table tbody').append(row);

                // Event: Hitung otomatis qty unit jika valid
                row.find('.select-packing, .select-unit, .input-packing-qty').on('change input', function() {
                    updateManualQuantity(row);
                });

                row.find('.price-input').on('input', function() {
                    let value = this.value.replace(/[^\d]/g, '');
                    if (value) this.value = formatRupiah(value);
                    recalculateTotal(row);
                    updateSummary();
                });

                row.find('.discount-input').on('input', function() {
                    recalculateTotal(row);
                    updateSummary();
                });
            }

            $(document).on('input', '.input-packing-qty', function() {
                const $row = $(this).closest('tr');
                updateManualQuantity($row);
            });


            $(document).on('change', '.select-unit', function() {
                const $row = $(this).closest('tr');
                updateManualQuantity($row);
            });


            // Ubah harga / diskon
            $(document).on('input', '.price-input, .discount-input', function() {
                const $row = $(this).closest('tr');
                recalculateTotal($row);
                updateSummary();
            });

            // Hapus row
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                updateSummary();
            });

            function updateManualQuantity($row) {
                const packingQty = parseFloat($row.find('.input-packing-qty').val()) || 0;

                const $packingSelect = $row.find('.select-packing');
                const $unitSelect = $row.find('.select-unit');

                const packingId = parseInt($packingSelect.find('option:selected').data('id'));
                const unitId = parseInt($unitSelect.find('option:selected').data('id'));

                const productPackings = $row.data('product_packings') || [];

                const matched = productPackings.find(p =>
                    p.packing_id === packingId && p.unit_id === unitId
                );

                let unitQty = 0;
                if (matched) {
                    unitQty = packingQty * matched.conversion_value;
                }

                $row.find('.qty-unit').val(unitQty);
                $row.attr('data-quantity', unitQty || 0);
                recalculateTotal($row);

                const total = parseFloat($row.attr('data-total')) || 0;
                $row.find('.input-total-hidden').val(total);
                updateSummary();
            }



            // inputan qty by unit manual by user
            $(document).on('input', '.qty-unit', function() {
                const $row = $(this).closest('tr');
                const manualQty = parseFloat($(this).val()) || 0;
                $row.attr('data-quantity', manualQty);
                recalculateTotal($row);
                updateSummary();
            });

            function loadDraft() {
                $.get('/draft/load', {
                    form_type: 'purchase_order'
                }, function(draft) {
                    if (!draft) return; // ga ada draft

                    const data = draft.data; // ← ambil field data dari draft
                    if (!data) return;

                    console.log(data); // lihat datanya

                    // Set header PO
                    $('#order_number').val(data.order_number);
                    $('#order_date').val(data.order_date);
                    $('#search-supplier').val(data.supplier_name);
                    $('#supplier_code').val(data.supplier_code); // <-- pastikan kode supplier valid
                    $('textarea[name="note"]').val(data.note);
                    $('#po_mode').val(data.po_mode);

                    // Mode switch
                    currentMode = data.po_mode;
                    if (currentMode === 'so') {
                        $('#btn-list-so').removeClass('d-none');
                        $('#manual-form').addClass('d-none');
                    } else {
                        $('#btn-list-so').addClass('d-none');
                        $('#manual-form').removeClass('d-none');
                    }

                    // Set PPN
                    if (currentMode === 'manual') {
                        const ppnRadio = data.ppn_status_manual === 'yes' ? '#ppn-yes' : '#ppn-no';
                        $(ppnRadio).prop('checked', true);
                    } else if (currentMode === 'so') {
                        $('#ppn_status_so').val(data.ppn_status_so || 'yes');
                    }

                    // Render products
                    selectedDetails = {};
                    if (currentMode === 'manual') {
                        data.products.forEach(p => {
                            $.get(`/product-packings/${p.id_product}`, function(packData) {
                                addProductRow(p.id_product, p.name, p.code, packData);

                                const $lastRow = $('#product-table tbody tr').last();
                                $lastRow.find('.input-packing-qty').val(p.qty_packing);
                                $lastRow.find('.select-packing').val(p.packing);
                                $lastRow.find('.qty-unit').val(p.qty_unit);
                                $lastRow.find('.select-unit').val(p.unit);
                                $lastRow.find('.price-input').val(p.price);
                                $lastRow.find('.discount-input').val(p.discount);
                                $lastRow.attr('data-quantity', p.qty_unit);
                                recalculateTotal($lastRow);
                            });
                        });
                    } else if (currentMode === 'so') {
                        const productsDraft = data.products;
                        const soIds = productsDraft.map(p => p.so_detail).filter(Boolean);

                        if (soIds.length) {
                            $.get('/draft-sale-details', {
                                ids: soIds.join(',')
                            }, function(details) {

                                productsDraft.forEach(p => {
                                    // Ubah "25,21" menjadi ["25","21"]
                                    const idList = p.so_detail.split(',').map(id => id
                                    .trim());

                                    // Ambil semua detail sale berdasarkan ID-ID ini
                                    const matched = details.filter(d => idList.includes(d.id
                                        .toString()));

                                    if (!matched.length) return;

                                    // Karena produk sama, packing & unit sama
                                    const base = matched[0];

                                    // Jumlahkan qty_packing dan qty_unit
                                    const totalQtyPacking = matched.reduce((sum, d) => sum +
                                        d.qty_packing, 0);
                                    const totalQtyUnit = matched.reduce((sum, d) => sum + d
                                        .qty_unit, 0);

                                    const key =
                                        `${base.id_product}_${base.packing}_${base.unit}`;

                                    selectedDetails[key] = {
                                        ids: idList, // simpan semua ID
                                        product_id: base.id_product,

                                        // ambil dari JSON draft
                                        name: p.name,
                                        code: p.code,
                                        price: p.price ? formatRupiah(p.price.toString()
                                            .replace(/\D/g, '')) : '',
                                        discount: p.discount,

                                        // dari detail sale
                                        packing: base.packing,
                                        qty_packing: totalQtyPacking,
                                        unit: base.unit,
                                        quantity: totalQtyUnit
                                    };
                                });


                                renderTable();
                            });
                        }
                    }


                    updateSummary();
                });
            }

            loadDraft();

        }); // end $(function)
    </script>

    <script>
        // kirim URL dan CSRF token ke JS
        const draftSaveConfig = {
            url: "{{ route('drafts.save') }}",
            csrf: "{{ csrf_token() }}"
        };
    </script>

    <script src="{{ asset('js/draft_createPO.js') }}"></script>
@endsection
