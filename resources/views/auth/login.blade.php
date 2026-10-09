
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | HCM PT Telkom Akses Palangkaraya</title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Bootstrap Icons -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <!-- CSS Login -->
    <link
        rel="stylesheet"
        href="{{ asset('assets/css/login.css') }}"
    >
</head>

<body>

<div class="login-page">

    <div class="login-container">

        <!-- =========================
             BAGIAN KIRI
        ========================== -->

        <div class="login-info">

            <div class="info-content">

                <span class="hcm-text">
                    HCM
                </span>

                <h1>
                    PT Telkom Akses<br>
                    Palangkaraya
                </h1>

                <div class="red-line"></div>

                <p>
                    Human Capital Management System untuk membantu
                    pengelolaan data karyawan, STO, absensi, dan alat kerja
                    PT Telkom Akses Palangkaraya.
                </p>

            </div>

        </div>


        <!-- =========================
             LOGIN CARD
        ========================== -->

        <div class="login-card">

            <!-- Logo -->
            <img
                src="{{ asset('assets/images/Telkomakses.jpeg') }}"
                class="card-logo"
                alt="Logo Telkom Akses"
            >

            <h2>
                Selamat Datang
            </h2>

            <p class="login-description">
                Silakan masuk untuk mengakses
                HCM PT Telkom Akses Palangkaraya.
            </p>


            <!-- Form Login -->

            <form action="#" method="POST">

                @csrf

                <!-- NIK -->

                <div class="form-group">

                    <label for="nik">
                        NIK
                    </label>

                    <div class="input-group-custom">

                        <i class="bi bi-person"></i>

                        <input
                            type="text"
                            id="nik"
                            name="nik"
                            placeholder="Masukkan NIK"
                            autocomplete="username"
                            required
                        >

                    </div>

                </div>


                <!-- Password -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-group-custom">

                        <i class="bi bi-lock"></i>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            required
                        >

                        <button
                            type="button"
                            class="password-button"
                            onclick="togglePassword()"
                        >
                            <i
                                class="bi bi-eye"
                                id="eyeIcon"
                            ></i>
                        </button>

                    </div>

                </div>


                <!-- Tombol Login -->

                <button
                    type="submit"
                    class="btn-login"
                >
                    Masuk
                    <i class="bi bi-arrow-right"></i>
                </button>

            </form>


            <!-- Divider -->

            <div class="divider">
                <span>atau</span>
            </div>


            <!-- Register -->

            <a
                href="{{ route('register') }}"
                class="btn-register"
            >
                <i class="bi bi-person-plus"></i>
                Daftar Akun
            </a>


            <!-- Copyright -->

            <div class="copyright">
                © 2026 PT Telkom Akses Palangkaraya
            </div>

        </div>

    </div>

</div>


<!-- =========================
     JAVASCRIPT PASSWORD
========================== -->

<script>

function togglePassword() {

    const password = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    if (password.type === 'password') {

        password.type = 'text';

        eyeIcon.classList.remove('bi-eye');

        eyeIcon.classList.add('bi-eye-slash');

    } else {

        password.type = 'password';

        eyeIcon.classList.remove('bi-eye-slash');

        eyeIcon.classList.add('bi-eye');

    }

}

</script>

</body>
</html>