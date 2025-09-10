@extends('Component.main_admin')

@section('content')
    <div class="container mt-4">
        <h1>DASHBOARD ON GOING</h1>
    </div>

    @if (session('login_success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Login Berhasil',
                text: "{{ session('login_success') }}",
                timer: 2000,
                showConfirmButton: false
            });
        </script>
    @endif
@endsection
