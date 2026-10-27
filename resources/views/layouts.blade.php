<!DOCTYPE html>
<html lang="id">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta name="csrf-token" content="{{ csrf_token() }}" />

		<title>@yield("title") | HCM PT. Telkom Akses Branch Palangkaraya</title>

		<script src="{{ asset("assets/js/color-mode.js") }}"></script>

		<link rel="icon" type="image/png" sizes="32x32" href="{{ asset("favicon-32x32.png") }}" />
		<link rel="apple-touch-icon" href="{{ asset("logo-icon.png") }}" />

		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fontsource-variable/source-sans-3@5.3.0/index.css" />
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" />
		<link
			rel="stylesheet"
			href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@2.1.8/css/dataTables.bootstrap5.min.css"
		/>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
		<link
			rel="stylesheet"
			href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
		/>

		<link rel="stylesheet" href="{{ asset("assets/css/adminlte.min.css") }}" />
		<link rel="stylesheet" href="{{ asset("assets/css/hcm.css") }}" />

		@stack("styles")
	</head>

	<body class="layout-fixed sidebar-expand-lg">
		<div class="app-wrapper">
			<nav class="app-header navbar navbar-expand bg-body">
				<div class="container-fluid">
					<ul class="navbar-nav">
						<li class="nav-item">
							<a class="nav-link" data-lte-toggle="sidebar" href="#" role="button" aria-label="Buka tutup menu">
								<i class="bi bi-list"></i>
							</a>
						</li>
					</ul>

					<ul class="navbar-nav ms-auto align-items-center">
						<li class="nav-item dropdown">
							<a
								class="nav-link"
								href="#"
								id="theme-menu"
								role="button"
								aria-label="Ganti tema warna"
								data-bs-toggle="dropdown"
								aria-expanded="false"
							>
								<i class="bi bi-sun-fill" data-lte-theme-icon="light" aria-hidden="true"></i>
								<i class="bi bi-moon-fill d-none" data-lte-theme-icon="dark" aria-hidden="true"></i>
								<i class="bi bi-circle-half d-none" data-lte-theme-icon="auto" aria-hidden="true"></i>
							</a>

							<ul class="dropdown-menu dropdown-menu-end" aria-labelledby="theme-menu">
								<li>
									<button
										type="button"
										class="dropdown-item d-flex align-items-center"
										data-bs-theme-value="light"
										aria-pressed="false"
									>
										<i class="bi bi-sun-fill me-2" aria-hidden="true"></i>
										Light
										<i class="bi bi-check-lg ms-auto d-none" aria-hidden="true"></i>
									</button>
								</li>
								<li>
									<button
										type="button"
										class="dropdown-item d-flex align-items-center"
										data-bs-theme-value="dark"
										aria-pressed="false"
									>
										<i class="bi bi-moon-fill me-2" aria-hidden="true"></i>
										Dark
										<i class="bi bi-check-lg ms-auto d-none" aria-hidden="true"></i>
									</button>
								</li>
								<li>
									<button
										type="button"
										class="dropdown-item d-flex align-items-center"
										data-bs-theme-value="auto"
										aria-pressed="false"
									>
										<i class="bi bi-circle-half me-2" aria-hidden="true"></i>
										System
										<i class="bi bi-check-lg ms-auto d-none" aria-hidden="true"></i>
									</button>
								</li>
							</ul>
						</li>

						@php($userInitial = mb_strtoupper(mb_substr((string) Auth::user()->name, 0, 1)))

						<li class="nav-item dropdown user-menu">
							<a
								href="#"
								class="nav-link dropdown-toggle d-flex align-items-center"
								id="user-menu"
								role="button"
								data-bs-toggle="dropdown"
								aria-expanded="false"
								aria-label="Menu akun {{ Auth::user()->name }}"
							>
								<span class="user-avatar" aria-hidden="true">{{ $userInitial }}</span>
								<span class="d-none d-md-inline ms-2">{{ Auth::user()->name }}</span>
							</a>

							<ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end" aria-labelledby="user-menu">
								<li class="user-header">
									<span class="user-avatar user-avatar-lg" aria-hidden="true">{{ $userInitial }}</span>

									<p>
										{{ Auth::user()->name }}
										<small>NIK {{ Auth::user()->nik }}</small>
									</p>
								</li>

								<li class="user-footer">
									<form action="{{ route("logout") }}" method="POST">
										@csrf

										<button type="submit" class="btn btn-outline-primary w-100">
											<i class="bi bi-box-arrow-right me-1" aria-hidden="true"></i>
											Keluar
										</button>
									</form>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</nav>

			<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">
				<div class="sidebar-brand">
					<a href="{{ route("home") }}" class="brand-link">
						<span class="brand-logo-box">
							<img src="{{ asset("logo-icon.png") }}" alt="Logo Telkom Akses" class="brand-image" />
						</span>

						<span class="brand-text">
							<strong>HCM</strong>
							Telkom Akses
						</span>
					</a>
				</div>

				<div class="sidebar-wrapper">
					<nav class="mt-2" aria-label="Menu utama">
						<ul class="nav sidebar-menu flex-column" data-lte-toggle="treeview" data-accordion="false">
							<li class="nav-item">
								<a href="{{ route("home") }}" class="nav-link {{ request()->routeIs("home") ? "active" : "" }}">
									<i class="nav-icon bi bi-house-door"></i>
									<p>Beranda</p>
								</a>
							</li>

							<li class="nav-header">OPERASIONAL</li>

							<li class="nav-item">
								<a
									href="{{ route("operasional.absen") }}"
									class="nav-link {{ request()->routeIs("operasional.absen") ? "active" : "" }}"
								>
									<i class="nav-icon bi bi-calendar2-check"></i>
									<p>Data Absen</p>
								</a>
							</li>

							<li class="nav-item">
								<a
									href="{{ route("operasional.alat-kerja") }}"
									class="nav-link {{ request()->routeIs("operasional.alat-kerja") ? "active" : "" }}"
								>
									<i class="nav-icon bi bi-tools"></i>
									<p>Data Alat Kerja</p>
								</a>
							</li>

							<li class="nav-item">
								<a
									href="{{ route("operasional.sto") }}"
									class="nav-link {{ request()->routeIs("operasional.sto") ? "active" : "" }}"
								>
									<i class="nav-icon bi bi-geo-alt"></i>
									<p>Data STO</p>
								</a>
							</li>

							@if ($isHcm)
								<li class="nav-header">ADMINISTRATOR</li>

								<li class="nav-item">
									<a
										href="{{ route("administrator.approval") }}"
										class="nav-link {{ request()->routeIs("administrator.approval") ? "active" : "" }}"
									>
										<i class="nav-icon bi bi-person-check"></i>
										<p>
											Persetujuan Akun
											@if ($pendingApprovalCount > 0)
												<span class="nav-badge" aria-label="{{ $pendingApprovalCount }} akun menunggu persetujuan">
													{{ $pendingApprovalCount }}
												</span>
											@endif
										</p>
									</a>
								</li>

								<li class="nav-item">
									<a
										href="{{ route("administrator.employee") }}"
										class="nav-link {{ request()->routeIs("administrator.employee") ? "active" : "" }}"
									>
										<i class="nav-icon bi bi-people"></i>
										<p>Data Karyawan</p>
									</a>
								</li>

								<li class="nav-item">
									<a
										href="{{ route("administrator.role") }}"
										class="nav-link {{ request()->routeIs("administrator.role") ? "active" : "" }}"
									>
										<i class="nav-icon bi bi-shield-lock"></i>
										<p>Data Role</p>
									</a>
								</li>

								<li class="nav-item">
									<a
										href="{{ route("administrator.position") }}"
										class="nav-link {{ request()->routeIs("administrator.position") ? "active" : "" }}"
									>
										<i class="nav-icon bi bi-person-badge"></i>
										<p>Data Position</p>
									</a>
								</li>
							@endif
						</ul>
					</nav>
				</div>
			</aside>

			<main class="app-main">
				<div class="app-content-header">
					<div class="container-fluid">
						<div class="row align-items-end">
							<div class="col-sm-6">
								<h1 class="page-title">@yield("title")</h1>
							</div>

							<div class="col-sm-6">
								<nav aria-label="breadcrumb">
									<ol class="breadcrumb float-sm-end">
										@if (request()->routeIs("home"))
											<li class="breadcrumb-item active" aria-current="page">Beranda</li>
										@else
											<li class="breadcrumb-item"><a href="{{ route("home") }}">Beranda</a></li>
											<li class="breadcrumb-item active" aria-current="page">@yield("title")</li>
										@endif
									</ol>
								</nav>
							</div>
						</div>
					</div>
				</div>

				<div class="app-content">
					<div class="container-fluid">
						@yield("content")
					</div>
				</div>
			</main>

			<footer class="app-footer">
				<div class="float-end d-none d-sm-inline">Human Capital Management System</div>
				<strong>&copy; {{ date("Y") }} PT. Telkom Akses Branch Palangkaraya</strong>
			</footer>
		</div>

		<aside
			class="offcanvas offcanvas-end record-detail"
			tabindex="-1"
			id="record-detail-panel"
			aria-labelledby="record-detail-title"
		>
			<div class="offcanvas-header">
				<div>
					<p class="record-detail-eyebrow">Detail Data</p>
					<h5 class="offcanvas-title" id="record-detail-title"></h5>
				</div>
				<button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
			</div>

			<div class="offcanvas-body">
				<section class="record-detail-section">
					<h6 class="record-detail-heading">Informasi</h6>
					<dl class="record-detail-list" id="record-detail-list"></dl>
				</section>

				<section class="record-detail-section" id="record-detail-audit">
					<h6 class="record-detail-heading">Riwayat Data</h6>
					<dl class="record-detail-list" id="record-detail-audit-list"></dl>
				</section>
			</div>
		</aside>

		<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
		<script src="{{ asset("assets/js/adminlte.min.js") }}"></script>
		<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/id.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/datatables.net@2.1.8/js/dataTables.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@2.1.8/js/dataTables.bootstrap5.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
		<script src="{{ asset("assets/js/hcm-ajax.js") }}"></script>
		@stack("scripts")
	</body>
</html>
