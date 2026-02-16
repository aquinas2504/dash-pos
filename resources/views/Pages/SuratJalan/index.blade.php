@extends('Component.main_admin')

@section('content')
    <div class="card-header d-flex mb-2">
        <a href="{{ route('SJ.CreateManual') }}" class="btn btn-sm btn-warning">
            <i class="fa fa-edit"></i> Create Manual Surat Jalan
        </a>
    </div>

    <div class="row">
        <div class="col">
            <div class="card border-primary" style="border-width: 2px;">
                <div class="card-body">

                    <form method="GET" action="{{ route('pengirimans.index') }}" class="mb-3">
                        <div class="row g-2 align-items-end">

                            {{-- SJ Number --}}
                            <div class="col-md-2">
                                <label class="form-label">No. Surat Jalan :</label>
                                <input type="text" name="sj_number" class="form-control" placeholder="SJ Number"
                                    value="{{ request('sj_number') }}">
                            </div>

                            {{-- SO Number --}}
                            <div class="col-md-2">
                                <label class="form-label">No. SO :</label>
                                <input type="text" name="so_number" class="form-control" placeholder="SO Number"
                                    value="{{ request('so_number') }}">
                            </div>

                            {{-- Customer --}}
                            <div class="col-md-2">
                                <label class="form-label">Customer :</label>
                                <input type="text" name="customer_name" class="form-control" placeholder="Customer Name"
                                    value="{{ request('customer_name') }}">
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

                                <a href="{{ route('pengirimans.index') }}" class="btn btn-secondary">
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
                                <th>No. Surat Jalan</th>
                                <th>No. SO</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($suratJalans as $sj)
                                <tr>
                                    <td>{{ ($suratJalans->currentPage() - 1) * $suratJalans->perPage() + $loop->iteration }}
                                    </td>
                                    <td>{{ $sj->ship_date }}</td>
                                    <td>{{ $sj->sj_number }}</td>
                                    <td>{{ $sj->SJdetails->first()->so_number ?? '-' }}</td>
                                    <td>{{ $sj->customer->customer_name }}</td>
                                    <td>{{ $sj->status }}</td>
                                    <td>

                                        <a href="{{ route('SJ.Print', urlencode($sj->sj_number)) }}"
                                            class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fa fa-print"></i> Surat Jalan
                                        </a>

                                        @if ($sj->status == 'Pending')
                                            <a href="{{ route('invoice.createSJ', urlencode($sj->sj_number)) }}"
                                                class="btn btn-sm btn-success">
                                                <i class="fa fa-file-invoice"></i> Create Invoice
                                            </a>
                                        @endif

                                        @if ($sj->status === 'Pending')
                                            <form action="{{ route('suratjalan.delete', urlencode($sj->sj_number)) }}"
                                                method="POST" style="display:inline;" class="form-delete">
                                                @csrf
                                                @method('DELETE')

                                                <button type="button" class="btn btn-sm btn-danger btn-delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif

                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-center mt-4">
                        {{ $suratJalans->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const deleteButtons = document.querySelectorAll('.btn-delete');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function() {

                    const form = this.closest('.form-delete');

                    Swal.fire({
                        title: 'Yakin ingin menghapus?',
                        text: "Data surat jalan akan dihapus permanen!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });

                });
            });

        });
    </script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}"
            });
        </script>
    @endif
@endsection
