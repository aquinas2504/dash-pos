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
                    <h4 class="mb-4 text-primary">Form Pembuatan Sale Invoice</h4>

                    <form id="invoice-form" method="POST" action="{{ route('invoiceSJ.store') }}">
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

                            <input type="hidden" name="sj_number" value="{{ $SuratJalan->sj_number }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>No Surat Jalan</label>
                                <input type="text" class="form-control" value="{{ $SuratJalan->sj_number }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Customer</label>
                                <input type="text" class="form-control"
                                    value="{{ $SuratJalan->customer->customer_name ?? '-' }}" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>PPN Status</label>
                            <input type="text" class="form-control" value="{{ $SuratJalan->ppn_status }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label>Rekening Tujuan Pembayaran</label>
                            <select name="payment_id" class="form-select">
                                <option value="">-- Pilih Rekening --</option>
                                @foreach ($payments as $payment)
                                    <option value="{{ $payment->id }}">
                                        {{ $payment->bank_name }} - {{ $payment->rekening_number }} a/n
                                        {{ $payment->rekening_name }}
                                    </option>
                                @endforeach
                            </select>
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
                                    @foreach ($SuratJalan->SJdetails as $i => $detail)
                                        @php
                                            $product = $detail->product;

                                            $matchedDetail = $detail->getMatchedSaleDetail();

                                            if (empty($detail->so_number)) {
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
                                            <td>{{ rtrim(rtrim(number_format((float) $detail->qty_unit, 2, '.', ''), '0'), '.') }} {{ $detail->unit }}</td>
                                            <td>
                                                <input type="text" name="price[]" class="form-control price-input"
                                                    value="{{ $price ? number_format($price, 0, ',', '.') : '' }}"
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

                        <div class="mt-4 text-start">
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                                data-bs-target="#returModal">
                                Gunakan Retur
                            </button>
                        </div>

                        <div class="mt-4 text-end" id="summary">
                            {{-- <p><strong>Subtotal:</strong> <span id="subtotal">Rp 0</span></p> --}}
                            @if ($SuratJalan->ppn_status === 'yes')
                                <p><strong>DPP:</strong> <span id="dpp">Rp 0</span></p>
                                <p><strong>PPN:</strong> <span id="ppn">Rp 0</span></p>
                            @endif
                            <p><strong>Potongan Retur:</strong> <span id="returDeduction">Rp 0</span></p>
                            <p><strong>Grand Total:</strong> <span id="grandtotal">Rp 0</span></p>
                            <input type="hidden" name="retur_deduction" id="returDeductionInput" value="0">

                        </div>


                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-primary" onclick="confirmSubmit()">Simpan Invoice</button>
                        </div>



                        <!-- Modal Retur -->
                        <div class="modal fade" id="returModal" tabindex="-1" aria-labelledby="returModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content border-secondary">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="returModalLabel">Pilih Retur yang Digunakan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body">
                                        <table class="table table-bordered text-center align-middle">
                                            <thead class="table-secondary">
                                                <tr>
                                                    <th><input type="checkbox" id="checkAllRetur"></th>
                                                    <th>Nomor Retur</th>
                                                    <th>Tanggal</th>
                                                    <th>Total</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($returSales as $retur)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" class="retur-checkbox"
                                                                data-total="{{ $retur->total }}"
                                                                value="{{ $retur->retur_number }}"
                                                                name="selected_returs[]">
                                                        </td>
                                                        <td>{{ $retur->retur_number }}</td>
                                                        <td>{{ $retur->date }}</td>
                                                        <td>{{ number_format($retur->total, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Gunakan
                                            Retur</button>
                                    </div>
                                </div>
                            </div>
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
            const ppnStatus = '{{ $SuratJalan->ppn_status }}';

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


            // Tambahan //
            function recalculateRetur() {
                let totalRetur = 0;
                document.querySelectorAll('.retur-checkbox:checked').forEach(cb => {
                    totalRetur += parseFloat(cb.dataset.total) || 0;
                });

                document.getElementById('returDeduction').innerText = formatRupiah(totalRetur);
                document.getElementById('returDeductionInput').value = totalRetur;

                // Update grand total lagi setelah dikurangi retur
                let subtotal = parseRupiah(document.getElementById('grandtotal').innerText);
                let grandTotal = subtotal;

                if (ppnStatus === 'yes') {
                    grandTotal = subtotal;
                }

                grandTotal = Math.max(0, grandTotal - totalRetur);

                document.getElementById('grandtotal').innerText = formatRupiah(grandTotal);
            }

            document.querySelectorAll('.retur-checkbox').forEach(cb => {
                cb.addEventListener('change', function(e) {
                    const checked = this.checked;
                    const thisReturTotal = parseFloat(this.dataset.total) || 0;

                    const currentTotalRetur = Array.from(document.querySelectorAll(
                            '.retur-checkbox:checked'))
                        .filter(input => input !== this)
                        .reduce((sum, input) => sum + (parseFloat(input.dataset.total) || 0), 0);

                    const newTotal = currentTotalRetur + (checked ? thisReturTotal : 0);
                    const grandTotal = parseRupiah(document.getElementById('grandtotal')
                    .innerText); // sebelum retur

                    if (newTotal > grandTotal) {
                        alert("Total retur melebihi nilai invoice!");
                        this.checked = false;
                        return;
                    }

                    recalculateRetur();
                });
            });


            document.getElementById('checkAllRetur')?.addEventListener('change', function() {
                const checkboxList = document.querySelectorAll('.retur-checkbox');
                const grandTotal = parseRupiah(document.getElementById('grandtotal').innerText);

                let total = 0;
                checkboxList.forEach(cb => {
                    const value = parseFloat(cb.dataset.total) || 0;
                    total += value;
                });

                if (total > grandTotal) {
                    alert("Total retur melebihi nilai invoice! Tidak bisa memilih semua retur.");
                    this.checked = false;
                    return;
                }

                checkboxList.forEach(cb => cb.checked = this.checked);
                recalculateRetur();
            });

            // Tambahan //

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
            recalculateRetur();
        });
    </script>
@endsection
