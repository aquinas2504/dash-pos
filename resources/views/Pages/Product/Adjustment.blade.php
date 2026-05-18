@extends('Component.main_admin')

@section('content')
    <div class="container">

        <form action="{{ route('inventory-adjustment.store') }}" method="POST" id="adjustmentForm">
            @csrf

            <div class="card">
                <div class="card-body">

                    <div class="row">

                        <div class="col-md-3">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label>Type</label>

                            <select name="type" class="form-control" required>
                                <option value="">Select</option>
                                <option value="Plus">Plus</option>
                                <option value="Minus">Minus</option>
                            </select>
                        </div>

                    </div>

                    <hr>

                    <button type="button" class="btn btn-primary mb-3" id="addRow">
                        Add Product
                    </button>

                    <div class="table-responsive">

                        <table class="table table-bordered" id="productTable">

                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>PPN</th>
                                    <th>Qty</th>
                                    <th>Unit</th>
                                    <th>Price / Unit</th>
                                    <th>Value</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>

                            </tbody>

                        </table>

                    </div>

                    <div class="row">

                        <div class="col-md-4 ms-auto">
                            <label>Total Value</label>

                            <input type="text" name="total_value" id="total_value" class="form-control" readonly>
                        </div>

                    </div>

                    <div class="mt-3">
                        <label>Reason</label>

                        <textarea name="reason" class="form-control" rows="3"></textarea>
                    </div>

                    <button class="btn btn-success mt-3">
                        Save Adjustment
                    </button>

                </div>
            </div>

        </form>

    </div>

    <script>
        let products = @json($products);

        function renderProductOptions() {

            let html = '<option value="">Select Product</option>';

            products.forEach(product => {

                html += `
            <option value="${product.id}">
                ${product.product_name}
            </option>
        `;
            });

            return html;
        }

        $('#addRow').click(function() {

            let row = `
        <tr>

            <td>
                <select name="product_id[]" class="form-control product-select" required>
                    ${renderProductOptions()}
                </select>
            </td>

            <td>
                <select name="ppn[]" class="form-control" required>
                    <option value="yes">PPN</option>
                    <option value="no">Non PPN</option>
                </select>
            </td>

            <td>
                <input type="number"
                       name="qty[]"
                       class="form-control qty"
                       required>
            </td>

            <td>
                <select name="unit[]" class="form-control">
                    <option value="Pieces">Pieces</option>
                    <option value="Lusin">Lusin</option>
                    <option value="Gross">Gross</option>
                </select>
            </td>

            <td>
                <input type="text"
                       name="price[]"
                       class="form-control price"
                       required>
            </td>

            <td>
                <input type="text"
                       class="form-control value"
                       readonly>
            </td>

            <td>
                <button type="button" class="btn btn-danger removeRow">
                    X
                </button>
            </td>

        </tr>
    `;

            $('#productTable tbody').append(row);
            initSelect2();
        });

        $(document).on('click', '.removeRow', function() {

            $(this).closest('tr').remove();

            calculateTotal();
        });

        $(document).on('keyup change', '.qty, .price', function() {

            let row = $(this).closest('tr');

            let qty = parseFloat(row.find('.qty').val()) || 0;

            let price = row.find('.price').val().replace(/\./g, '');

            price = parseFloat(price) || 0;

            let value = qty * price;

            row.find('.value').val(formatRupiah(value.toString()));

            calculateTotal();
        });

        $(document).on('keyup', '.price', function() {

            let value = $(this).val();

            $(this).val(formatRupiah(value));
        });

        function calculateTotal() {
            let total = 0;

            $('.value').each(function() {

                let value = $(this).val().replace(/\./g, '');

                total += parseFloat(value) || 0;
            });

            $('#total_value').val(formatRupiah(total.toString()));
        }

        function initSelect2() {

            $('.product-select').select2({
                width: '100%',
                placeholder: 'Search Product'
            });
        }

        function formatRupiah(angka) {
            let number_string = angka.replace(/[^,\d]/g, '').toString();

            let split = number_string.split(',');

            let sisa = split[0].length % 3;

            let rupiah = split[0].substr(0, sisa);

            let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

            if (ribuan) {

                let separator = sisa ? '.' : '';

                rupiah += separator + ribuan.join('.');
            }

            return rupiah;
        }
    </script>

    <script>
        $('#adjustmentForm').submit(function(e) {

            let type = $('select[name="type"]').val();

            let message = '';

            if (type == 'Plus') {

                message = 'Anda yakin ingin MENAMBAHKAN product tersebut?';

            } else if (type == 'Minus') {

                message = 'Anda yakin ingin MENGURANGI product tersebut?';

            } else {

                message = 'Anda yakin ingin menyimpan adjustment ini?';
            }

            if (!confirm(message)) {

                e.preventDefault();
            }
        });
    </script>
@endsection
