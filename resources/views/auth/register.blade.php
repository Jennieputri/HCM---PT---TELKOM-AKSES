<!DOCTYPE html>
<html lang="id">
	<head>
		<meta charset="UTF-8" />

		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<title>Register | HCM PT Telkom Akses Palangkaraya</title>

		<link rel="icon" type="image/png" sizes="32x32" href="{{ asset("favicon-32x32.png") }}" />
		<link rel="apple-touch-icon" href="{{ asset("logo-icon.png") }}" />

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
							PT Telkom Akses
							<br />
							Palangkaraya
						</h1>

						<div class="red-line"></div>

						<p>Buat akun untuk mengakses Human Capital Management System PT Telkom Akses Palangkaraya.</p>
					</div>
				</div>

				<div class="login-card">
					<img src="{{ asset("assets/images/logo-telkomakses.png") }}" class="card-logo" alt="Logo Telkom Akses" />

					<h2>Daftar Akun</h2>

					<p class="login-description">Silakan lengkapi data untuk membuat akun.</p>

					@include("partials.alerts")

					<form action="{{ route("register.submit") }}" method="POST" novalidate>
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

								<button type="button" class="password-button" onclick="toggleRegisterPassword()">
									<i class="bi bi-eye" id="registerEyeIcon"></i>
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

					<div class="copyright">© 2026 PT Telkom Akses Palangkaraya</div>
				</div>
			</div>
		</div>

		<script>
			function toggleRegisterPassword() {
				const password = document.getElementById('password');

				const eyeIcon = document.getElementById('registerEyeIcon');

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

		<style>
			.input-error {
				border: 2px solid #dc3545 !important;
				outline: none !important;
				box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.12) !important;
				border-radius: 12px !important;
				box-sizing: border-box;
				width: 100%;
			}

			input.input-error::placeholder {
				color: #dc3545 !important;
				opacity: 1;
			}

			.input-group-custom.input-error {
				border: 2px solid #dc3545 !important;
				border-radius: 12px !important;
				box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.12) !important;
				overflow: hidden;
			}

			.input-group-custom.input-error input {
				border: none !important;
				box-shadow: none !important;
				outline: none !important;
			}
		</style>

		<script>
			document.addEventListener('DOMContentLoaded', function () {
				const nik = document.getElementById('nik');
				const password = document.getElementById('password');

				if (!nik || !password) return;

				const form = nik.closest('form');

				if (!form) return;

				function showError(input, message) {
					input.value = '';
					input.placeholder = message;
					input.classList.add('input-error');

					const wrapper = input.closest('.input-group-custom');
					if (wrapper) {
						wrapper.classList.add('input-error');
					}
				}

				function clearError(input, placeholder) {
					input.classList.remove('input-error');
					input.placeholder = placeholder;

					const wrapper = input.closest('.input-group-custom');
					if (wrapper) {
						wrapper.classList.remove('input-error');
					}
				}

				form.addEventListener('submit', function (event) {
					let hasError = false;

					if (!/^[0-9]+$/.test(nik.value)) {
						showError(nik, 'NIK harus berupa angka!');
						hasError = true;
					}

					if (password.value.length < 8) {
						showError(password, 'Password minimal 8 karakter!');
						hasError = true;
					}

					if (hasError) {
						event.preventDefault();
					}
				});

				nik.addEventListener('input', function () {
					if (this.classList.contains('input-error')) {
						clearError(this, 'Masukkan NIK');
					}
				});

				password.addEventListener('input', function () {
					if (this.classList.contains('input-error')) {
						clearError(this, 'Masukkan Password');
					}
				});
			});
		</script>
	</body>
</html>
