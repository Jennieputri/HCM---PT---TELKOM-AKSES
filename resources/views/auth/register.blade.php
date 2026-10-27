<!DOCTYPE html>
<html lang="id">
	<head>
		<meta charset="UTF-8" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<title>Register | HCM PT. Telkom Akses Branch Palangkaraya</title>

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

						<p>Buat akun untuk mengakses Human Capital Management System PT. Telkom Akses Branch Palangkaraya.</p>
					</div>
				</div>

				<div class="login-card">
					<img src="{{ asset("assets/images/logo-telkomakses.png") }}" class="card-logo" alt="Logo Telkom Akses" />

					<h2>Daftar Akun</h2>

					<p class="login-description">Silakan lengkapi data untuk membuat akun.</p>

					@include("partials.alerts")

					<form id="register-form" action="{{ route("register.submit") }}" method="POST" novalidate>
						@csrf

						<div class="form-group">
							<label for="nik">NIK</label>

							<div class="input-group-custom">
								<i class="bi bi-person-badge"></i>

								<input
									type="text"
									id="nik"
									name="nik"
									value="{{ old("nik") }}"
									placeholder="Masukkan NIK"
									inputmode="numeric"
									autocomplete="off"
									required
								/>
							</div>
						</div>

						<div class="form-group">
							<label for="name">Nama Lengkap</label>

							<div class="input-group-custom">
								<i class="bi bi-person"></i>

								<input
									type="text"
									id="name"
									name="name"
									value="{{ old("name") }}"
									placeholder="Masukkan nama lengkap"
									maxlength="100"
									required
								/>
							</div>
						</div>

						<div class="form-group">
							<label for="password">Password</label>

							<div class="input-group-custom">
								<i class="bi bi-lock"></i>

								<input type="password" id="password" name="password" placeholder="Masukkan Password" minlength="8" required />

								<button type="button" class="password-button" aria-label="Tampilkan password">
									<i class="bi bi-eye" aria-hidden="true"></i>
								</button>
							</div>
						</div>

						<div class="form-group">
							<label for="password_confirmation">Konfirmasi Password</label>

							<div class="input-group-custom">
								<i class="bi bi-lock-fill"></i>

								<input
									type="password"
									id="password_confirmation"
									name="password_confirmation"
									placeholder="Ulangi password"
									required
								/>
							</div>
						</div>

						<button type="submit" class="btn-login">
							Daftar

							<i class="bi bi-person-plus"></i>
						</button>
					</form>

					<div class="divider">
						<span>sudah punya akun?</span>
					</div>

					<a href="{{ route("login") }}" class="btn-register">
						<i class="bi bi-box-arrow-in-right"></i>

						Kembali ke Login
					</a>

					<div class="copyright">© 2026 PT. Telkom Akses Branch Palangkaraya</div>
				</div>
			</div>
		</div>

		<script src="{{ asset("assets/js/pages/auth-password-toggle.js") }}"></script>
		<script src="{{ asset("assets/js/pages/auth-register.js") }}"></script>
	</body>
</html>
