<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PT Dash</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('templates/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('templates/dist/css/adminlte.min.css') }}">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Untuk search shipping di create SO --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />




    <style>
        /* Table Styling */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
            background-color: #fff;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table th,
        .table td {
            padding: 6px 9px;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #ddd;
        }

        .table th {
            background-color: #f4f4f4;
            color: #333;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 15px;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .table img {
            border-radius: 4px;
            object-fit: cover;
        }

        .table td {
            font-size: 12px;
            color: #555;
        }

        /* Add responsiveness */
        @media (max-width: 768px) {

            .table,
            .table thead,
            .table tbody,
            .table th,
            .table td,
            .table tr {
                display: block;
            }

            .table tr {
                margin-bottom: 15px;
            }

            .table td {
                text-align: right;
                padding-left: 50%;
                position: relative;
            }

            .table td:before {
                content: attr(data-label);
                position: absolute;
                left: 15px;
                text-align: left;
                font-weight: bold;
                color: #333;
            }

            .table th {
                display: none;
            }
        }

        /* Besarkan Modal */
        .modal-lg {
            max-width: 50%;
            /* Atur persentase sesuai kebutuhan */
        }

        /* Gambar agar lebih besar dan responsif */
        .modal-body img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .pagination .page-item.active .page-link {
            background-color: #007bff;
            /* Warna tombol aktif */
            border-color: #007bff;
            color: white;
        }

        .pagination .page-item .page-link {
            color: #007bff;
            border: 1px solid #dee2e6;
        }

        .pagination .page-item:hover .page-link {
            background-color: #f8f9fa;
            color: #0056b3;
        }

        /* Default styling untuk tombol filter */
        .btn-filter {
            border: 2px solid #ddd;
            color: #333;
            background-color: #f9f9f9;
            font-weight: bold;
            padding: 2px 4px;
            transition: all 0.3s ease-in-out;
        }

        /* Hover effect */
        .btn-filter:hover {
            background-color: #e2e6ea;
            border-color: #ccc;
        }

        /* Active styling untuk tombol aktif */
        .btn-filter-active {
            background-color: #007bff;
            /* Warna biru */
            color: #fff;
            border-color: #007bff;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            /* Tambahkan bayangan */
            transform: scale(1.05);
            /* Sedikit membesar */
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: #fff;
            z-index: 1000;
        }

        .content-wrapper {
            height: calc(100vh - 56px);
            /* 100% tinggi layar dikurangi tinggi navbar */
            overflow-y: auto;
            /* Hanya bagian content yang bisa di-scroll */
            padding-bottom: 60px;
            /* Hindari overlap dengan footer */
        }
    </style>

</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        @include('Component.Navbar.navbar')

        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        @include('Component.Sidebar.sidebar_admin')

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <section class="content-header">
                <div class="container-fluid">
                    @yield('header')
                </div><!-- /.container-fluid -->
            </section>

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    @yield('content')
                    <script>
                        @if (session('success'))
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: '{{ session('success') }}',
                                confirmButtonText: 'OK'
                            });
                        @endif
                    </script>
                </div>
            </section>
            <!-- /.content -->
        </div>


        <footer class="main-footer">
            <div class="float-right d-none d-sm-block">
                <b>Version</b> 1.0
            </div>
            <strong>Copyright &copy; 2025 </strong> PT. Dash
        </footer>

    </div>


    <!-- jQuery -->
    <script src="{{ asset('/templates/plugins/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap 4 -->
    <script src="{{ asset('/templates/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ asset('/templates/dist/js/adminlte.min.js') }}"></script>
    {{-- untuk Search Shipping di Create So --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const logoutBtn = document.getElementById('logout-button');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Yakin ingin logout?',
                        text: "Sesi Anda akan diakhiri.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Logout',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('logout-form').submit();
                        }
                    });
                });
            }
        });
    </script>

</body>

</html>
