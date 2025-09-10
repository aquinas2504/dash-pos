<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login - PT. Dash Megah Internasional</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .login-card {
            max-width: 420px;
            margin: 80px auto;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
        }

        .company-logo {
            height: 75px;
            margin-bottom: 20px;
        }

        .company-name {
            font-weight: bold;
            font-size: 20px;
            margin-bottom: 30px;
        }

        .footer-text {
            font-size: 12px;
            color: #888;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="login-card text-center">
            <img src="{{ asset('img/logo-dmi.jpg') }}" alt="Logo PT. DMI" class="company-logo">
            <div class="company-name">PT. Dash Megah Internasional</div>

            @if ($errors->any())
                <script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Login Gagal',
                        html: `{!! implode('<br>', $errors->all()) !!}`,
                        confirmButtonText: 'Coba Lagi'
                    });
                </script>
            @endif


            @if (session('logout_success'))
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil Logout',
                        text: "{{ session('logout_success') }}",
                        timer: 1000,
                        showConfirmButton: false
                    });
                </script>
            @endif


            <form method="POST" action="{{ url('/login') }}">
                @csrf

                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="email" placeholder="Email"
                        required>
                    <label for="email">Email</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="password" name="password" class="form-control" id="password" placeholder="Password"
                        required>
                    <label for="password">Password</label>
                </div>

                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>

            <div class="footer-text">
                &copy; {{ date('Y') }} PT. Dash Megah Internasional. All rights reserved.
            </div>
        </div>
    </div>

    <!-- Optional: Bootstrap JS (if needed) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
