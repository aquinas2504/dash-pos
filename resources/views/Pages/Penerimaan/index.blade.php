@extends('Component.main_admin')

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card-header d-flex" style="margin-bottom: 10px">
        <a href="{{ route('penerimaan.create.manual') }}" class="btn btn-sm btn-warning">
            <i class="fa fa-edit"></i> Create Manual Penerimaan
        </a>
    </div>

    <div class="row">
        <div class="col">
            <div class="card border-primary" style="border-width: 2px;">
                <div class="card-body">

                    <form method="GET" action="{{ route('penerimaans.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">

                            {{-- Search Penerimaan Number --}}
                            <div class="col-md-2">
                                <label class="form-label">No. Penerimaan :</label>
                                <input type="text" name="penerimaan_number" class="form-control" placeholder="Penerimaan Number"
                                    value="{{ request('penerimaan_number') }}">
                            </div>

                            {{-- Search PO Number --}}
                            <div class="col-md-2">
                                <label class="form-label">No. PO :</label>
                                <input type="text" name="po_number" class="form-control" placeholder="PO Number"
                                    value="{{ request('po_number') }}">
                            </div>

                            {{-- Search Supplier --}}
                            <div class="col-md-2">
                                <label class="form-label">Supplier :</label>
                                <input type="text" name="supplier_name" class="form-control" placeholder="Supplier Name"
                                    value="{{ request('supplier_name') }}">
                            </div>

                            {{-- Filter Date --}}
                            <div class="col-md-4">
                                <label class="form-label">Date :</label>
                                <div class="input-group">
                                    <span class="input-group-text">From</span>
                                    <input type="date" name="date_from" class="form-control"
                                        value="{{ request('date_from') }}">

                                    <span class="input-group-text">To</span>
                                    <input type="date" name="date_to" class="form-control"
                                        value="{{ request('date_to') }}">
                                </div>
                            </div>

                            {{-- Button --}}
                            <div class="col-md-2 mt-2">
                                <button class="btn btn-primary">
                                    <i class="fa fa-filter"></i> Filter
                                </button>

                                <a href="{{ route('penerimaans.index') }}" class="btn btn-secondary">
                                    Reset
                                </a>
                            </div>

                            {{-- STATUS RADIO --}}
                            <div class="mt-3 d-flex gap-3">
                                @php
                                    $status = request('status', 'All');
                                @endphp

                                @foreach (['All', 'Pending', 'Difaktur'] as $item)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="status"
                                            value="{{ $item }}" id="status_{{ $item }}"
                                            {{ $status === $item ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status_{{ $item }}">
                                            {{ $item }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </form>

                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>No. Penerimaan</th>
                                <th>No. PO</th>
                                <th>Supplier</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($penerimaans as $penerimaan)
                                @php
                                    $firstDetail = $penerimaan->details->first();
                                    $poNumber = $firstDetail?->po_number ?? '-';
                                    $supplierName = $penerimaan->supplier?->supplier_name ?? '-';
                                @endphp

                                <tr>
                                    <td>{{ ($penerimaans->currentPage() - 1) * $penerimaans->perPage() + $loop->iteration }}</td>
                                    <td>{{ $penerimaan->date }}</td>
                                    <td>{{ $penerimaan->penerimaan_number }}</td>
                                    <td>{{ $poNumber }}</td>
                                    <td>{{ $supplierName }}</td>
                                    <td>{{ $penerimaan->status }}</td>
                                    <td>

                                        <button class="btn btn-sm btn-info btn-view-detail" data-bs-toggle="modal"
                                            data-bs-target="#detailModal" data-penerimaan='@json($penerimaan)'
                                            data-details='@json($penerimaan->details)' data-po="{{ $poNumber }}"
                                            data-supplier="{{ $supplierName }}">
                                            <i class="fa fa-eye"></i>
                                        </button>


                                        @if ($penerimaan->status !== 'Difaktur')
                                            <a href="{{ route('invoice.create', urlencode($penerimaan->penerimaan_number)) }}"
                                                class="btn btn-sm btn-success">
                                                <i class="fa fa-file-invoice"></i> Invoice
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- model untuk view detail --}}

                    <div class="modal fade" id="detailModal" tabindex="-1">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title" id="modalTitle">Detail Penerimaan</h5>
                                    <button type="button" class="btn-close btn-close-white"
                                        data-bs-dismiss="modal"></button>
                                </div>

                                <div class="modal-body">

                                    {{-- Info --}}
                                    <table class="table table-sm table-bordered mb-4">
                                        <tr>
                                            <th>No. Penerimaan</th>
                                            <td id="m_penerimaan"></td>
                                        </tr>
                                        <tr>
                                            <th>No. PO</th>
                                            <td id="m_po"></td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal</th>
                                            <td id="m_date"></td>
                                        </tr>
                                        <tr>
                                            <th>Supplier</th>
                                            <td id="m_supplier"></td>
                                        </tr>
                                        <tr>
                                            <th>PPN Status</th>
                                            <td id="m_ppn"></td>
                                        </tr>
                                        <tr>
                                            <th>Note</th>
                                            <td id="m_note"></td>
                                        </tr>
                                    </table>

                                    {{-- Detail Produk --}}
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th>Qty Packing</th>
                                                <th>Qty Unit</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detailTableBody"></tbody>
                                    </table>

                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $penerimaans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-view-detail').forEach(button => {
                button.addEventListener('click', function() {

                    const penerimaan = JSON.parse(this.dataset.penerimaan);
                    const details = JSON.parse(this.dataset.details);

                    document.getElementById('modalTitle').innerText =
                        'Detail Penerimaan - ' + penerimaan.penerimaan_number;

                    document.getElementById('m_penerimaan').innerText = penerimaan
                        .penerimaan_number;
                    document.getElementById('m_po').innerText = this.dataset.po ?? '-';
                    document.getElementById('m_date').innerText = penerimaan.date;
                    document.getElementById('m_supplier').innerText = this.dataset.supplier ?? '-';
                    document.getElementById('m_ppn').innerText = penerimaan.ppn_status ?? '-';
                    document.getElementById('m_note').innerText = penerimaan.note ?? '-';

                    const tbody = document.getElementById('detailTableBody');
                    tbody.innerHTML = '';

                    details.forEach((item, index) => {
                        tbody.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.product?.product_name ?? '-'}</td>
                        <td>${item.qty_packing ?? 0} ${item.packing ?? ''}</td>
                        <td>${item.qty_unit ?? 0} ${item.unit ?? ''}</td>
                    </tr>
                `;
                    });

                });
            });
        });
    </script>
@endsection
