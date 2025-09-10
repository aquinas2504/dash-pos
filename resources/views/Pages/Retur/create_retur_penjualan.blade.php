@extends('Component.main_admin')

@section('content')

    <style>
        #invoice_suggestions {
            max-height: 200px;
            overflow-y: auto;
        }

        .form-control-sm,
        .form-select-sm {
            font-size: 0.875rem;
        }
    </style>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container my-4">
        <h3 class="mb-4">Form Retur Penjualan</h3>
        <form method="POST" action="{{ route('retur-sales.store') }}">
            @csrf
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="retur_number" class="form-label">Nomor Retur</label>
                    <input type="text" name="retur_number" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label for="date" class="form-label">Tanggal Retur</label>
                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-3">
                    <label for="invoice_number" class="form-label">Nomor Invoice</label>
                    <div class="input-group">
                        <input type="text" id="invoice_search" class="form-control" placeholder="Cari invoice..."
                            autocomplete="off">
                        <input type="hidden" name="invoice_number" id="invoice_number">
                        <button type="button" class="btn btn-primary" id="search_invoice_btn">Cari</button>
                        <button type="button" class="btn btn-outline-secondary" id="show_history_btn"
                            data-bs-toggle="modal" data-bs-target="#historyModal">Riwayat</button>
                    </div>
                    <ul id="invoice_suggestions" class="list-group position-absolute mt-1 w-100" style="z-index: 1050;">
                    </ul>
                </div>
                <div class="col-md-3">
                    <!-- Tombol baru -->
                    <button type="button" class="btn btn-outline-dark" data-bs-toggle="modal"
                        data-bs-target="#searchInvoiceModal">
                        Cari Invoice by Produk & Customer
                    </button>
                </div>

            </div>


            <table class="table table-bordered align-middle text-nowrap" id="returTable">
                <thead class="table-light text-center">
                    <tr>
                        <th>No</th>
                        <th>Produk</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Harga</th>
                        <th>Diskon</th>
                        <th>Value</th>
                        <th>Catatan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Isi dinamis --}}
                </tbody>
            </table>

            <div class="text-end">
                <button type="submit" class="btn btn-success px-4">Simpan Retur</button>
            </div>


        </form>
    </div>

    <!-- Modal History -->
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="historyModalLabel">Riwayat Retur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3" id="historyModalBody">
                    <div class="text-center text-muted">Silakan cari invoice terlebih dahulu.</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Search Invoice-->
    <div class="modal fade" id="searchInvoiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cari Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="searchInvoiceForm" onsubmit="return false">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Customer</label>
                                <input type="text" id="customer_search" class="form-control" autocomplete="off">
                                <ul id="customer_suggestions" class="list-group position-absolute w-100 mt-1"
                                    style="z-index:1050;">
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nama Produk</label>
                                <input type="text" id="product_search" class="form-control" autocomplete="off">
                                <ul id="product_suggestions" class="list-group position-absolute w-100 mt-1"
                                    style="z-index:1050;"></ul>
                            </div>
                        </div>
                        <div class="text-end">
                            <button id="checkInvoiceBtn" class="btn btn-primary">Check Invoice</button>
                        </div>
                    </form>

                    <div id="invoiceResult" class="mt-4"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // === GLOBAL FUNCTIONS ===

        // Format ke rupiah
        function formatRupiah(number) {
            const isInteger = Number.isInteger(number);
            return 'Rp ' + number
                .toFixed(isInteger ? 0 : 2)
                .replace('.', ',')
                .replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        // Konversi unit
        const unitConversion = {
            Gross: {
                Lusin: 12,
                Pieces: 144,
                Set: 144
            },
            Lusin: {
                Pieces: 12,
                Set: 12
            },
            Set: {},
            Pieces: {}
        };

        function convertUnit(qty, price, fromUnit, toUnit) {
            if (fromUnit === toUnit) return {
                qty,
                price
            };
            const conversion = unitConversion[fromUnit]?.[toUnit];
            if (!conversion) return {
                qty,
                price
            };
            return {
                qty: qty * conversion,
                price: price / conversion
            };
        }

        function calculateValue(qty, price, discountStr) {
            let value = qty * price;
            if (!discountStr) return value;
            let discounts = discountStr.split('+').map(d => parseFloat(d.trim()));
            discounts.forEach(d => {
                value -= value * (d / 100);
            });
            return value;
        }

        // === GLOBAL DOM FUNCTIONS ===

        function selectInvoice(invoiceNumber) {
            document.getElementById('invoice_number').value = invoiceNumber;
            document.getElementById('invoice_search').value = invoiceNumber;

            loadInvoiceDetails(invoiceNumber);

            $('#searchInvoiceModal').modal('hide');

        }

        function loadInvoiceDetails(invoiceNumber) {
            const returTable = document.querySelector('#returTable tbody');
            let rowIndex = 1;

            fetch(`/invoice-detail/${invoiceNumber}`)
                .then(res => {
                    if (!res.ok) throw new Error('Invoice tidak ditemukan');
                    return res.json();
                })
                .then(data => {
                    returTable.innerHTML = '';

                    if (!data.length) {
                        returTable.innerHTML = `
                    <tr>
                        <td colspan="8" class="text-center text-danger fw-bold">Invoice tidak ditemukan atau tidak memiliki detail.</td>
                    </tr>`;
                        return;
                    }

                    data.forEach((item, index) => {
                        const allowedUnits = Object.keys(unitConversion[item.unit] || {});
                        const canChangeUnit = allowedUnits.length > 0;

                        const unitSelect = canChangeUnit ?
                            `<select name="details[${index}][unit]" class="form-select form-select-sm unit-select"
                        data-index="${index}" data-original-unit="${item.unit}" data-original-qty="${item.qty_unit}"
                        data-original-price="${item.price}">
                        <option value="${item.unit}" selected>${item.unit}</option>
                        ${allowedUnits.map(u => `<option value="${u}">${u}</option>`).join('')}
                    </select>` :
                            `${item.unit}<input type="hidden" name="details[${index}][unit]" value="${item.unit}">`;

                        const value = calculateValue(item.qty_unit, item.price, item.discount);
                        const row = document.createElement('tr');

                        row.innerHTML = `
                    <td>${rowIndex}</td>
                    <td>
                        ${item.product_name}
                        <input type="hidden" name="details[${index}][id_product]" value="${item.id_product}">
                    </td>
                    <td>
                        <input type="number" name="details[${index}][qty]" class="form-control form-control-sm qty-input"
                        value="${item.qty_unit}" min="1" max="${item.qty_unit}" data-index="${index}">
                    </td>
                    <td>${unitSelect}</td>
                    <td>
                        <span class="price-text">${formatRupiah(item.price)}</span>
                        <input type="hidden" class="price-input" name="details[${index}][price]" value="${item.price}">
                    </td>
                    <td>
                        ${item.discount ?? '-'}
                        <input type="hidden" name="details[${index}][discount]" value="${item.discount}">
                    </td>
                    <td>
                        <span class="value-text">${formatRupiah(value)}</span>
                        <input type="hidden" class="value-input" name="details[${index}][value]" value="${value}">
                    </td>
                    <td>
                        <input type="text" name="details[${index}][note]" class="form-control">
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('tr').remove()">x</button>
                    </td>`;

                        returTable.appendChild(row);
                        rowIndex++;
                    });
                })
                .catch(error => {
                    returTable.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-danger fw-bold">${error.message}</td>
                </tr>`;
                });
        }

        // === MAIN DOM EVENTS ===

        document.addEventListener('DOMContentLoaded', () => {
            const returTable = document.querySelector('#returTable tbody');
            const input = document.getElementById('invoice_search');
            const searchBtn = document.getElementById('search_invoice_btn');
            const suggestionsBox = document.getElementById('invoice_suggestions');
            const invoiceHiddenInput = document.getElementById('invoice_number');
            const historyBtn = document.getElementById('show_history_btn');
            const historyModalBody = document.getElementById('historyModalBody');

            const debounce = (fn, delay = 300) => {
                let timeout;
                return (...args) => {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => fn(...args), delay);
                };
            };

            function attachSuggestions(inputId, suggestionId, endpoint) {
                const input = document.getElementById(inputId);
                const list = document.getElementById(suggestionId);

                input.addEventListener('input', debounce(() => {
                    const keyword = input.value.trim();
                    if (!keyword) return list.innerHTML = '';

                    fetch(`${endpoint}?q=${encodeURIComponent(keyword)}`)
                        .then(res => res.json())
                        .then(data => {
                            list.innerHTML = '';
                            data.forEach(item => {
                                const li = document.createElement('li');
                                li.className = 'list-group-item list-group-item-action';
                                li.textContent = item.name;
                                li.addEventListener('click', () => {
                                    input.value = item.name;
                                    list.innerHTML = '';
                                });
                                list.appendChild(li);
                            });
                        });
                }, 300));
            }

            attachSuggestions('customer_search', 'customer_suggestions', '/search-customer');
            attachSuggestions('product_search', 'product_suggestions', '/search-product');

            input.addEventListener('input', function() {
                let query = this.value;
                if (query.length >= 2) {
                    fetch(`/invoice-search?query=${query}`)
                        .then(res => res.json())
                        .then(data => {
                            suggestionsBox.innerHTML = '';
                            data.forEach(invoice => {
                                let li = document.createElement('li');
                                li.classList.add('list-group-item', 'list-group-item-action');
                                li.textContent = invoice.invoice_number;
                                li.addEventListener('click', () => {
                                    input.value = invoice.invoice_number;
                                    invoiceHiddenInput.value = invoice.invoice_number;
                                    suggestionsBox.innerHTML = '';
                                });
                                suggestionsBox.appendChild(li);
                            });
                        });
                } else {
                    suggestionsBox.innerHTML = '';
                }
            });

            searchBtn.addEventListener('click', () => {
                const manualInput = input.value.trim();
                if (!manualInput) {
                    alert('Silakan isi Invoice Number terlebih dahulu');
                    return;
                }
                suggestionsBox.innerHTML = '';
                invoiceHiddenInput.value = manualInput;
                loadInvoiceDetails(manualInput);
            });

            historyBtn.addEventListener('click', () => {
                const invoice = invoiceHiddenInput.value.trim();
                if (!invoice) {
                    historyModalBody.innerHTML =
                        `<div class="text-center text-danger">Invoice belum dipilih.</div>`;
                    return;
                }

                historyModalBody.innerHTML = `<div class="text-center">Memuat...</div>`;

                fetch(`/retur-history/${invoice}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.length) {
                            historyModalBody.innerHTML =
                                `<div class="text-center text-warning">Belum ada retur untuk invoice ini.</div>`;
                            return;
                        }

                        let html = data.map(retur => `
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <h6 class="mb-0">Retur #${retur.retur_number}</h6>
                                    <small class="text-muted">Tanggal: ${retur.date}</small>
                                </div>
                                <div class="text-end fw-bold text-primary">
                                    Total: Rp ${parseFloat(retur.total).toLocaleString('id-ID')}
                                </div>
                            </div>
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produk</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-end">Value</th>
                                        <th>Catatan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${retur.details.map(d => `
                                            <tr>
                                                <td>${d.product?.product_name ?? 'Produk'}</td>
                                                <td class="text-center">${d.qty}</td>
                                                <td class="text-center">${d.unit}</td>
                                                <td class="text-end">Rp ${parseFloat(d.value).toLocaleString('id-ID')}</td>
                                                <td>${d.note ?? '-'}</td>
                                            </tr>`).join('')}
                                </tbody>
                            </table>
                        </div>
                    </div>
                `).join('');
                        historyModalBody.innerHTML = html;
                    })
                    .catch(() => {
                        historyModalBody.innerHTML =
                            `<div class="text-danger text-center">Gagal mengambil data retur.</div>`;
                    });
            });

            document.getElementById('checkInvoiceBtn').addEventListener('click', () => {
                const customer = document.getElementById('customer_search').value.trim();
                const product = document.getElementById('product_search').value.trim();
                const resultDiv = document.getElementById('invoiceResult');

                if (!customer || !product) {
                    resultDiv.innerHTML =
                        `<div class="alert alert-warning">Isi kedua field terlebih dahulu.</div>`;
                    return;
                }

                resultDiv.innerHTML = `<div class="text-center">Memuat...</div>`;

                fetch(
                        `/search-invoices?customer=${encodeURIComponent(customer)}&product=${encodeURIComponent(product)}`)
                    .then(res => res.json())
                    .then(data => {
                        if (!data.length) {
                            resultDiv.innerHTML =
                                `<div class="alert alert-info">Tidak ditemukan invoice untuk kriteria tersebut.</div>`;
                            return;
                        }

                        resultDiv.innerHTML = `
                    <ul class="list-group">
                        ${data.map(inv => `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>
                                        <strong>${inv.invoice_number}</strong> - ${inv.date}<br>
                                        <small>Customer: ${inv.customer_name}</small>
                                    </span>
                                    <button class="btn btn-sm btn-outline-success" onclick="selectInvoice('${inv.invoice_number}')">Gunakan</button>
                                </li>`).join('')}
                    </ul>`;
                    })
                    .catch(() => {
                        resultDiv.innerHTML =
                        `<div class="alert alert-danger">Gagal memuat data.</div>`;
                    });
            });

            returTable.addEventListener('change', function(e) {
                if (e.target.classList.contains('unit-select')) {
                    const select = e.target;
                    const row = select.closest('tr');
                    const index = select.dataset.index;
                    const newUnit = select.value;
                    const originalUnit = select.dataset.originalUnit;
                    const originalQty = parseFloat(select.dataset.originalQty);
                    const originalPrice = parseFloat(select.dataset.originalPrice);
                    const discount = row.querySelector(`input[name="details[${index}][discount]"]`).value;

                    const {
                        qty,
                        price
                    } = convertUnit(originalQty, originalPrice, originalUnit, newUnit);
                    const value = calculateValue(qty, price, discount);

                    const qtyInput = row.querySelector('.qty-input');
                    qtyInput.value = qty;
                    qtyInput.setAttribute('max', qty);

                    row.querySelector('.price-text').textContent = formatRupiah(price);
                    row.querySelector('.price-input').value = price;

                    row.querySelector('.value-text').textContent = formatRupiah(value);
                    row.querySelector('.value-input').value = value;

                    let unitInput = row.querySelector(`input[name="details[${index}][unit]"]`);
                    if (!unitInput) {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = `details[${index}][unit]`;
                        hidden.value = newUnit;
                        select.insertAdjacentElement('afterend', hidden);
                    } else {
                        unitInput.value = newUnit;
                    }
                }
            });

            returTable.addEventListener('input', function(e) {
                if (e.target.classList.contains('qty-input')) {
                    const input = e.target;
                    const row = input.closest('tr');
                    const index = input.dataset.index;

                    let qty = parseFloat(input.value) || 0;
                    const price = parseFloat(row.querySelector('.price-input').value);
                    const discount = row.querySelector(`input[name="details[${index}][discount]"]`).value;

                    const value = calculateValue(qty, price, discount);

                    row.querySelector('.value-text').textContent = formatRupiah(value);
                    row.querySelector('.value-input').value = value;
                }
            });
        });
    </script>



@endsection
