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
                                <input type="text" name="order_number" required class="form-control">
                            </div>

                            <div class="form-group col-md-6">
                                <label>Date</label>
                                <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control"
                                    required>
                            </div>

                            <div class="form-group col-md-6 position-relative">
                                <label>Customer</label>
                                <input type="text" id="search-customer" placeholder="Search Customer..."
                                    class="form-control mb-2">
                                <div id="customer-search-result" class="mt-1"></div>
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

                            <div class="form-row mb-3">
                                <div class="form-group col-md-4">
                                    <label>Shipping 1</label>
                                    <select name="ship_1" class="form-control">
                                        <option value="">-- Select Shipping --</option>
                                        @foreach ($shippings as $shipping)
                                            <option value="{{ $shipping->shipping_code }}">{{ $shipping->shipping_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Shipping 2</label>
                                    <select name="ship_2" class="form-control">
                                        <option value="">-- Select Shipping --</option>
                                        @foreach ($shippings as $shipping)
                                            <option value="{{ $shipping->shipping_code }}">{{ $shipping->shipping_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Term of Payment (days)</label>
                                    <input type="number" name="top" class="form-control" min="0"
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
                            <div class="form-group col-md-6">
                                <label>Note</label>
                                <textarea name="note" class="form-control" rows="3"></textarea>
                            </div>

                            <div class="form-group col-md-6">
                                <div id="subtotal-ppn-section">
                                    <label>Subtotal</label>
                                    <input type="text" id="subtotal" name="subtotal" class="form-control mb-2" readonly>

                                    <label>PPN</label>
                                    <input type="text" id="ppn" name="ppn_amount" class="form-control mb-2" readonly>
                                </div>

                                <label>Grand Total</label>
                                <input type="text" id="grand_total" name="grand_total" class="form-control mb-2" readonly>
                            </div>

                        </div>
                    </div>

                    <div class="text-end mb-5">
                        <button type="button" class="btn btn-success px-4" onclick="confirmSubmit()">Create Sale Order</button>
                    </div>
                </div>
            </div>
        </form>
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

    <script>
        const customerInput = document.getElementById('search-customer');
        const resultBox = document.getElementById('customer-search-result');

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
                        item.textContent = customer.customer_name;
                        item.classList.add('p-2', 'border-bottom', 'search-result-item');
                        item.style.cursor = 'pointer';

                        item.onclick = () => {
                            customerInput.value = customer.customer_name;

                            // Optional hidden input to store customer_code
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

                    resultBox.classList.add('border', 'bg-white', 'shadow-sm');
                });
        });
    </script>

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
                        item.textContent = `${product.product_name} (${product.product_code})`;
                        item.style.cursor = 'pointer';

                        item.addEventListener('click', function() {
                            addProductToTable(product);
                            resultDiv.innerHTML = '';
                            document.getElementById('search-product').value = '';
                        });

                        resultDiv.appendChild(item);
                    });
                });
        });

        function formatRupiah(number) {
            const [whole, decimal] = number.toString().split('.');
            const formatted = 'Rp. ' + whole.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            return decimal && parseInt(decimal) > 0 ? `${formatted},${decimal}` : formatted;
        }

        // Tambahkan di luar fungsi
        function calculateLineTotal(row) {
            const qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
            const priceText = row.querySelector('input[name="unit_price[]"]').value;
            const discountText = row.querySelector('input[name="discount[]"]').value;

            const price = parseFloat(priceText.replace(/[^\d]/g, '')) || 0;

            let total = qty * price;

            // Proses diskon bertingkat
            if (discountText.trim() !== '') {
                const discounts = discountText.split('+').map(d => {
                    // ganti koma jadi titik supaya bisa diparse float
                    d = d.replace(',', '.');
                    return parseFloat(d) || 0;
                });
                discounts.forEach(d => {
                    total -= total * (d / 100);
                });
            }


            const totalFormatted = formatRupiah(Math.round(total));
            row.querySelector('.total-cell').textContent = totalFormatted;

            calculateSummary();
        }

        function calculateSummary() {
            const rows = document.querySelectorAll('#product-table tbody tr');
            let grandtotal = 0;

            rows.forEach(row => {
                const totalText = row.querySelector('.total-cell').textContent.replace(/[^\d]/g, '');
                grandtotal += parseInt(totalText) || 0;
            });

            // cek status PPN
            const ppnStatus = document.querySelector('input[name="ppn"]:checked')?.value || 'no';

            let subtotal, ppn;
            if (ppnStatus === 'yes') {
                subtotal = Math.round(grandtotal / 1.11);
                ppn = grandtotal - subtotal;
            } else {
                subtotal = 0;
                ppn = 0;
            }

            document.getElementById('subtotal').value = formatRupiah(subtotal);
            document.getElementById('ppn').value = formatRupiah(ppn);
            document.getElementById('grand_total').value = formatRupiah(grandtotal);
        }


        function addProductToTable(product) {
            const table = document.querySelector('#product-table tbody');
            const row = document.createElement('tr');
            row.dataset.productId = product.id;

            row.innerHTML = `
                <input type="hidden" name="id_product[]" value="${product.id}">
                <td>${product.product_name}</td>
                <td>${product.product_code}</td>

                <td>
                    <div class="input-group">
                        <input type="number" min="1" class="form-control packing-qty" name="qty_packing[]" placeholder="Qty" style="width: 70px;">
                        <select class="form-select packing-select" name="packing[]" style="width: 100px;"></select>
                    </div>
                </td>

                <td>
                    <div class="input-group">
                        <input type="number" class="form-control unit-qty" name="qty[]" placeholder="0" style="width: 70px;">
                        <select class="form-select unit-select" name="unit[]" style="width: 100px;"></select>
                    </div>
                </td>

                <td><input type="text" class="form-control price-input" name="unit_price[]" placeholder="Rp. 0"></td>
                <td><input type="text" class="form-control discount-input" name="discount[]" placeholder="0"></td>
                <td class="total-cell">-</td>
                <td><button class="btn btn-danger btn-sm remove-row">&times;</button></td>
            `;

            table.appendChild(row);

            const priceInput = row.querySelector('.price-input');
            const discountInput = row.querySelector('.discount-input');
            const removeBtn = row.querySelector('.remove-row');
            const packingSelect = row.querySelector('.packing-select');
            const packingQty = row.querySelector('.packing-qty');
            const unitSelect = row.querySelector('.unit-select');
            const unitQty = row.querySelector('.unit-qty');

            let packingData = []; // data hasil fetch

            removeBtn.addEventListener('click', () => {
                row.remove();
                calculateSummary();
            });

            unitQty.addEventListener('input', () => {
                calculateLineTotal(row);
            });


            priceInput.addEventListener('input', () => {
                let value = priceInput.value.replace(/[^\d]/g, '');
                priceInput.value = value ? formatRupiah(value) : '';
                calculateLineTotal(row);
            });

            discountInput.addEventListener('input', () => {
                discountInput.value = discountInput.value.replace(/[^0-9+,.]/g, '');
                calculateLineTotal(row);
            });

            // Ambil data packing dari backend
            fetch(`/product-packings/${product.id}`)
                .then(res => res.json())
                .then(({
                    all_packings,
                    all_units,
                    product_packings
                }) => {
                    // Isi dropdown packing
                    packingSelect.innerHTML = '<option disabled selected value="">Pilih Packing</option>';
                    all_packings.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.packing_id;
                        opt.textContent = p.packing_name;
                        packingSelect.appendChild(opt);
                    });

                    // Isi dropdown unit
                    unitSelect.innerHTML = '<option disabled selected value="">Pilih Unit</option>';
                    all_units.forEach(u => {
                        const opt = document.createElement('option');
                        opt.value = u.unit_id;
                        opt.textContent = u.unit_name;
                        unitSelect.appendChild(opt);
                    });

                    // Simpan untuk referensi pengecekan
                    packingData = product_packings;
                });

            function updateQtyByUnit() {
                const packingQtyVal = parseFloat(packingQty.value) || 0;
                const packingId = parseInt(packingSelect.value);
                const unitId = parseInt(unitSelect.value);

                const matched = packingData.find(p => p.packing_id === packingId && p.unit_id === unitId);

                if (matched) {
                    unitQty.value = packingQtyVal * matched.conversion_value;
                } else {
                    unitQty.value = ''; // atau '0' kalau kamu mau
                }

                calculateLineTotal(row);
            }


            packingQty.addEventListener('input', updateQtyByUnit);
            unitSelect.addEventListener('change', updateQtyByUnit);
        }
    </script>

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
@endsection
