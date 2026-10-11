<!DOCTYPE html>
<html lang="id">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<title>Login | HCM PT. Telkom Akses Branch Palangkaraya</title>

		<link rel="icon" type="image/png" sizes="32x32" href="{{ asset("favicon-32x32.png") }}" />
		<link rel="apple-touch-icon" href="{{ asset("logo-icon.png") }}" />

		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource-variable/source-sans-3@5.3.0/index.css" />

		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

		<link rel="stylesheet" href="{{ asset("assets/css/login.css") }}" />
	</head>

	<body>
		<div class="login-page">
			<div class="login-container">
				<div class="login-info">
					<div class="info-content">
						<span class="hcm-text">HCM</span>

						<h1>
							PT. Telkom Akses
							<br />
							Branch Palangkaraya
						</h1>

						<div class="red-line"></div>

						<p>
							Human Capital Management System untuk membantu pengelolaan data karyawan, STO, absensi, dan alat kerja PT. Telkom
							Akses Branch Palangkaraya.
						</p>
					</div>
				</div>

				<div class="login-card">
					<img src="{{ asset("assets/images/logo-telkomakses.png") }}" class="card-logo" alt="Logo Telkom Akses" />

					<h2>Selamat Datang</h2>

					<p class="login-description">Silakan masuk untuk mengakses HCM PT. Telkom Akses Branch Palangkaraya.</p>

					@include("partials.alerts")

					<form action="{{ route("login.submit") }}" method="POST">
						@csrf

						<div class="form-group">
							<label for="nik">NIK</label>

							<div class="input-group-custom">
								<i class="bi bi-person"></i>

								<input
									type="text"
									id="nik"
									name="nik"
									value="{{ old("nik") }}"
									placeholder="Masukkan NIK"
									inputmode="numeric"
									autocomplete="username"
									required
								/>
							</div>
						</div>

						<div class="form-group">
							<label for="password">Password</label>

							<div class="input-group-custom">
								<i class="bi bi-lock"></i>

								<input
									type="password"
									id="password"
									name="password"
									placeholder="Masukkan password"
									autocomplete="current-password"
									required
								/>

								<button type="button" class="password-button" onclick="togglePassword()">
									<i class="bi bi-eye" id="eyeIcon"></i>
								</button>
							</div>
						</div>

						<button type="submit" class="btn-login">
							Masuk
							<i class="bi bi-arrow-right"></i>
						</button>
					</form>

					<div class="divider">
						<span>atau</span>
					</div>

					<a href="{{ route("register") }}" class="btn-register">
						<i class="bi bi-person-plus"></i>
						Daftar Akun
					</a>

					<div class="copyright">© 2026 PT. Telkom Akses Branch Palangkaraya</div>
				</div>
			</div>
		</div>

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
