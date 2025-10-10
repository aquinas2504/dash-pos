@extends('Component.main_admin')

@section('content')
    <style>
        .table td,
        .table th {
            vertical-align: middle !important;
        }
    </style>

    <div class="row">
        <div class="col-lg-10 offset-lg-1">
            <div class="card border-primary" style="border-width: 2px;">
                <div class="card-body">
                    <h4 class="mb-4 text-primary">Form Pembuatan Invoice</h4>

                    <form id="invoice-form" method="POST" action="{{ route('invoices.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Invoice Number</label>
                                <input type="text" name="invoice_number" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>

                            <input type="hidden" name="penerimaan_number" value="{{ $penerimaan->penerimaan_number }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>No Terima</label>
                                <input type="text" class="form-control" value="{{ $penerimaan->penerimaan_number }}"
                                    readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Supplier</label>
                                <input type="text" class="form-control"
                                    value="{{ $penerimaan->supplier->supplier_name ?? '-' }}" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>PPN Status</label>
                            <input type="text" class="form-control" value="{{ $penerimaan->ppn_status }}" readonly>
                        </div>

                        <hr>

                        <h5 class="mb-3">Daftar Produk</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center" id="invoice-table">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Packing</th>
                                        <th>Unit</th>
                                        <th>Price</th>
                                        <th>Discount</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($penerimaan->details as $i => $detail)
                                        @php
                                            $product = $detail->product;

                                            $matchedDetail = $detail->getMatchedPurchaseDetail();

                                            if ($isManual) {
                                                $price = old("price.$i", 0);
                                                $discount = old("discount.$i", '');
                                            } else {
                                                $price = $matchedDetail->price ?? 0;
                                                $discount = $matchedDetail->discount ?? '';
                                            }

                                            $qty = $detail->qty_unit ?? 0;
                                        @endphp

                                        <tr>
                                            <td class="text-start">{{ $product->product_name ?? '-' }}</td>
                                            <td>{{ $detail->qty_packing }} {{ $detail->packing }}</td>
                                            <td>{{ $detail->qty_unit }} {{ $detail->unit }}</td>
                                            <td>
                                                <input type="text" name="price[]" class="form-control price-input"
                                                    value="{{ $price ? number_format((float) $price, 0, ',', '.') : '' }}"
                                                    placeholder="Rp 0" data-qty="{{ $qty }}">
                                            </td>
                                            <td>
                                                <input type="text" name="discount[]" class="form-control discount-input"
                                                    value="{{ $discount }}" placeholder="0">
                                            </td>
                                            <td class="total-col">Rp 0</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 text-end" id="summary">
                            {{-- <p><strong>Subtotal:</strong> <span id="subtotal">Rp 0</span></p> --}}
                            @if ($penerimaan->ppn_status === 'yes')
                                <p><strong>DPP:</strong> <span id="dpp">Rp 0</span></p>
                                <p><strong>PPN:</strong> <span id="ppn">Rp 0</span></p>
                            @endif
                            <p><strong>Grand Total:</strong> <span id="grandtotal">Rp 0</span></p>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-primary" onclick="confirmSubmit()">Simpan Invoice</button>
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
                    document.getElementById('invoice-form').submit();
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
        document.addEventListener('DOMContentLoaded', function() {
            const ppnStatus = '{{ $penerimaan->ppn_status }}';

            function formatRupiah(angka) {
                const number = parseFloat(angka);
                if (isNaN(number)) return 'Rp 0';
                return 'Rp ' + number.toLocaleString('id-ID', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                });
            }

            function parseRupiah(value) {
                if (!value) return 0;
                return parseFloat(value.replace(/[^0-9]/g, '')) || 0;
            }

            function parseDiscountNested(discountStr, price) {
                let net = price;
                discountStr.split('+').forEach(d => {
                    const rate = parseFloat(d.replace(',', '.'));
                    if (!isNaN(rate)) {
                        net -= net * rate / 100;
                    }
                });
                return price - net;
            }

            function recalculate() {
                let subtotal = 0;
                const rows = document.querySelectorAll('#invoice-table tbody tr');

                rows.forEach(row => {
                    const priceInput = row.querySelector('.price-input');
                    const discountInput = row.querySelector('.discount-input');
                    let price = parseRupiah(priceInput.value);
                    let qty = parseFloat(priceInput.dataset.qty) || 0;
                    let discountStr = discountInput.value || '';

                    const totalDiscountPerUnit = parseDiscountNested(discountStr, price);
                    let total = qty * (price - totalDiscountPerUnit);
                    subtotal += total;

                    row.querySelector('.total-col').innerText = formatRupiah(total);
                });

                let dpp = subtotal;
                let ppn = 0;
                if (ppnStatus === 'yes') {
                    dpp = Math.round(subtotal / 1.11);
                    ppn = subtotal - dpp;
                }

                // document.getElementById('subtotal').innerText = formatRupiah(subtotal);
                if (ppnStatus === 'yes') {
                    document.getElementById('dpp').innerText = formatRupiah(dpp);
                    document.getElementById('ppn').innerText = formatRupiah(ppn);
                }
                document.getElementById('grandtotal').innerText = formatRupiah(subtotal);
            }

            document.querySelectorAll('.price-input').forEach(input => {
                if (input.value) {
                    let onlyNum = input.value.replace(/\D/g, '');
                    input.value = onlyNum ? formatRupiah(onlyNum) : '';
                }

                input.addEventListener('input', function(e) {
                    let cursorPos = this.selectionStart;
                    let oldValue = this.value;
                    let oldLength = oldValue.length;

                    let numericValue = oldValue.replace(/\D/g, '');
                    if (numericValue === '') numericValue = '0';

                    this.value = formatRupiah(numericValue);

                    let newLength = this.value.length;
                    cursorPos = cursorPos + (newLength - oldLength);
                    if (cursorPos > newLength) cursorPos = newLength;

                    this.setSelectionRange(cursorPos, cursorPos);
                    recalculate();
                });
            });

            document.querySelectorAll('.discount-input').forEach(input => {
                input.addEventListener('input', recalculate);
            });

            recalculate();
        });
    </script>
@endsection
