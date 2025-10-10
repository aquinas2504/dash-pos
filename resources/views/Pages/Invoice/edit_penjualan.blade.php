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
            <div class="card border-warning" style="border-width: 2px;">
                <div class="card-body">
                    <h4 class="mb-4 text-warning">Edit Sale Invoice</h4>

                    <form method="POST" id="invoice-form" action="{{ route('invoiceSJ.update', $invoice->invoice_number) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Invoice Number</label>
                                <input type="text" class="form-control" value="{{ $invoice->invoice_number }}" readonly>
                                <input type="hidden" name="invoice_number" value="{{ $invoice->invoice_number }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Date</label>
                                <input type="date" name="date" class="form-control" value="{{ $invoice->date }}">
                            </div>
                            <input type="hidden" name="sj_number" value="{{ $invoice->sj_number }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>No Surat Jalan</label>
                                <input type="text" class="form-control" value="{{ $invoice->sj_number }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Customer</label>
                                <input type="text" class="form-control"
                                    value="{{ $invoice->suratJalan->customer->customer_name ?? '-' }}" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label>PPN Status</label>
                            <input type="text" class="form-control" value="{{ $invoice->suratJalan->ppn_status }}"
                                readonly>
                        </div>

                        <div class="mb-3">
                            <label>Rekening Tujuan Pembayaran</label>
                            <select name="payment_id" class="form-select">
                                <option value="">-- Pilih Rekening --</option>
                                @foreach ($payments as $payment)
                                    <option value="{{ $payment->id }}"
                                        {{ $invoice->payment_id == $payment->id ? 'selected' : '' }}>
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
                                <thead class="table-warning">
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
                                    @foreach ($invoice->suratJalan->SJdetails as $i => $detail)
                                        @php
                                            $product = $detail->product;
                                            $matched = $invoice->details
                                                ->where('surat_jalan_detail', $detail->id)
                                                ->first();
                                            $price = old("price.$i", $matched->price ?? 0);
                                            $discount = old("discount.$i", $matched->discount ?? '');
                                            $qty = $detail->qty_unit ?? 0;
                                        @endphp
                                        <tr>
                                            <td class="text-start">{{ $product->product_name ?? '-' }}</td>
                                            <td>{{ $detail->qty_packing }} {{ $detail->packing }}</td>
                                            <td>{{ $detail->qty_unit }} {{ $detail->unit }}</td>
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

                        @if ($invoice->retur_used == 0)
                            <div class="mt-4 text-start">
                                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#returModal">
                                    Gunakan Retur
                                </button>
                            </div>
                        @endif


                        <div class="mt-4 text-end" id="summary">
                            {{-- <p><strong>Subtotal:</strong> <span id="subtotal">Rp 0</span></p> --}}

                            @if ($invoice->suratJalan->ppn_status === 'yes')
                                <p><strong>DPP:</strong> <span id="dpp">Rp 0</span></p>
                                <p><strong>PPN:</strong> <span id="ppn">Rp 0</span></p>
                            @endif

                            @if ($invoice->retur_used > 0)
                                <p><strong>Potongan Retur:</strong> <span
                                        id="returDeduction">{{ number_format($invoice->retur_used, 0, ',', '.') }}</span>
                                </p>
                                <input type="hidden" name="retur_deduction" id="returDeductionInput" value="{{ $invoice->retur_used }}">
                            @else
                                {{-- Potongan retur default 0 --}}
                                <p><strong>Potongan Retur:</strong> <span id="returDeduction">0</span></p>
                                <input type="hidden" name="retur_deduction" id="returDeductionInput" value="0">
                            @endif



                            <p><strong>Grand Total:</strong> <span id="grandtotal">Rp 0</span></p>
                        </div>

                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-warning" onclick="confirmSubmit()">Update Invoice</button>
                        </div>

                        @if ($invoice->retur_used == 0)
                            <div class="modal fade" id="returModal" tabindex="-1" aria-labelledby="returModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content border-secondary">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Pilih Retur yang Digunakan</h5>
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
                                            <button type="button" class="btn btn-primary"
                                                data-bs-dismiss="modal">Gunakan
                                                Retur</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

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
            const ppnStatus = '{{ $invoice->suratJalan->ppn_status }}';

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

                document.querySelectorAll('#invoice-table tbody tr').forEach(row => {
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

                // === Hitung potongan retur ===
                let totalRetur = parseFloat(document.getElementById('returDeductionInput').value) || 0;
                const returCheckboxes = document.querySelectorAll('.retur-checkbox');
                if (returCheckboxes.length > 0) {
                    totalRetur = 0;
                    returCheckboxes.forEach(cb => {
                        if (cb.checked) totalRetur += parseFloat(cb.dataset.total) || 0;
                    });
                }

                document.getElementById('returDeduction').innerText = formatRupiah(totalRetur);
                document.getElementById('returDeductionInput').value = totalRetur;

                let grandTotal = Math.max(0, subtotal - totalRetur);
                document.getElementById('grandtotal').innerText = formatRupiah(grandTotal);
            }

            // Event: harga
            document.querySelectorAll('.price-input').forEach(input => {
                if (input.value) {
                    let onlyNum = input.value.replace(/\D/g, '');
                    input.value = onlyNum ? formatRupiah(onlyNum) : '';
                }
                input.addEventListener('input', function() {
                    let cursorPos = this.selectionStart;
                    let oldLength = this.value.length;

                    let numericValue = this.value.replace(/\D/g, '');
                    if (numericValue === '') numericValue = '0';

                    this.value = formatRupiah(numericValue);

                    let newLength = this.value.length;
                    cursorPos = cursorPos + (newLength - oldLength);
                    if (cursorPos > newLength) cursorPos = newLength;

                    this.setSelectionRange(cursorPos, cursorPos);
                    recalculate();
                });
            });

            // Event: diskon
            document.querySelectorAll('.discount-input').forEach(input => {
                input.addEventListener('input', recalculate);
            });

            // Event: retur checkbox
            document.querySelectorAll('.retur-checkbox').forEach(cb => {
                cb.addEventListener('change', function() {
                    const subtotal = parseRupiah(document.getElementById('grandtotal').innerText);
                    const totalRetur = Array.from(document.querySelectorAll('.retur-checkbox:checked'))
                        .reduce((sum, cb) => sum + (parseFloat(cb.dataset.total) || 0), 0);

                    if (totalRetur > subtotal) {
                        alert("Total retur melebihi nilai invoice!");
                        this.checked = !this.checked; // kembalikan ke kondisi sebelumnya
                        return;
                    }
                    recalculate();
                });
            });


            // Event: check all retur
            document.getElementById('checkAllRetur')?.addEventListener('change', function() {
                const grandTotal = parseRupiah(document.getElementById('grandtotal').innerText);
                const totalSemuaRetur = Array.from(document.querySelectorAll('.retur-checkbox'))
                    .reduce((sum, cb) => sum + (parseFloat(cb.dataset.total) || 0), 0);

                if (totalSemuaRetur > grandTotal) {
                    alert("Total retur melebihi nilai invoice! Tidak bisa memilih semua retur.");
                    this.checked = false;
                    return;
                }
                document.querySelectorAll('.retur-checkbox').forEach(cb => cb.checked = this.checked);
                recalculate();
            });

            // Kalkulasi awal
            recalculate();
        });
    </script>

@endsection
