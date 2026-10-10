<!DOCTYPE html>
<html lang="id">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />

		<title>HCM PT. Telkom Akses Branch Palangkaraya</title>

		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />

		<style>
			body {
				background-color: #f5f6f8;
				font-family: 'Segoe UI', Arial, sans-serif;
			}

			.sidebar {
				width: 250px;
				height: 100vh;
				position: fixed;
				left: 0;
				top: 0;
				background: #b5121b;
				color: white;
				z-index: 1000;
			}

			.sidebar-logo {
				height: 75px;
				display: flex;
				align-items: center;
				padding: 0 22px;
				border-bottom: 1px solid rgba(255, 255, 255, 0.15);
			}

			.sidebar-logo .logo-box {
				width: 42px;
				height: 42px;
				background: white;
				color: #b5121b;
				border-radius: 10px;
				display: flex;
				align-items: center;
				justify-content: center;
				font-weight: 800;
				margin-right: 10px;
			}

			.sidebar-logo h5 {
				margin: 0;
				font-size: 15px;
				font-weight: 700;
			}

			.sidebar-logo small {
				opacity: 0.8;
				font-size: 11px;
			}

			.menu-title {
				font-size: 11px;
				text-transform: uppercase;
				opacity: 0.65;
				padding: 25px 22px 10px;
				letter-spacing: 1px;
			}

			.sidebar-menu {
				padding: 0 12px;
			}

			.sidebar-menu a {
				display: flex;
				align-items: center;
				gap: 13px;
				color: rgba(255, 255, 255, 0.85);
				text-decoration: none;
				padding: 12px 14px;
				border-radius: 8px;
				margin-bottom: 4px;
				font-size: 14px;
				transition: 0.2s;
			}

			.sidebar-menu a i {
				font-size: 18px;
				width: 22px;
			}

			.sidebar-menu a:hover,
			.sidebar-menu a.active {
				background: rgba(255, 255, 255, 0.16);
				color: white;
			}

			.main-content {
				margin-left: 250px;
				min-height: 100vh;
			}

			.topbar {
				height: 75px;
				background: white;
				border-bottom: 1px solid #e5e5e5;
				display: flex;
				align-items: center;
				justify-content: space-between;
				padding: 0 30px;
			}

			.page-title h4 {
				margin: 0;
				font-weight: 700;
				color: #333;
			}

			.page-title small {
				color: #888;
			}

			.admin-profile {
				display: flex;
				align-items: center;
				gap: 10px;
			}

			.admin-avatar {
				width: 40px;
				height: 40px;
				border-radius: 50%;
				background: #b5121b;
				color: white;
				display: flex;
				align-items: center;
				justify-content: center;
				font-weight: 700;
			}

			.admin-info strong {
				display: block;
				font-size: 13px;
			}

			.admin-info small {
				color: #888;
				font-size: 11px;
			}

			.content {
				padding: 30px;
			}

			.welcome-card {
				background: linear-gradient(135deg, #b5121b, #d71920);
				color: white;
				border-radius: 14px;
				padding: 28px 30px;
				margin-bottom: 25px;
				position: relative;
				overflow: hidden;
			}

			.welcome-card::after {
				content: '';
				width: 180px;
				height: 180px;
				border: 30px solid rgba(255, 255, 255, 0.08);
				border-radius: 50%;
				position: absolute;
				right: -50px;
				top: -70px;
			}

			.welcome-card h3 {
				font-weight: 700;
				margin-bottom: 7px;
			}

			.welcome-card p {
				margin: 0;
				opacity: 0.85;
			}

			.stat-card {
				background: white;
				border-radius: 12px;
				padding: 20px;
				border: 1px solid #eee;
				height: 100%;
				transition: 0.2s;
			}

			.stat-card:hover {
				transform: translateY(-3px);
				box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07);
			}

			.stat-icon {
				width: 48px;
				height: 48px;
				border-radius: 10px;
				display: flex;
				align-items: center;
				justify-content: center;
				font-size: 22px;
				margin-bottom: 15px;
			}

			.icon-red {
				background: #fde7e8;
				color: #b5121b;
			}

			.icon-blue {
				background: #e7f0ff;
				color: #3674d9;
			}

			.icon-green {
				background: #e5f7ee;
				color: #20945a;
			}

			.icon-orange {
				background: #fff0df;
				color: #e88a20;
			}

			.stat-card h3 {
				font-size: 25px;
				font-weight: 700;
				margin: 0;
			}

			.stat-card p {
				color: #888;
				font-size: 13px;
				margin: 4px 0 0;
			}

			.dashboard-card {
				background: white;
				border: 1px solid #eee;
				border-radius: 12px;
				overflow: hidden;
			}

			.dashboard-card-header {
				padding: 18px 20px;
				border-bottom: 1px solid #eee;
				display: flex;
				align-items: center;
				justify-content: space-between;
			}

			.dashboard-card-header h5 {
				margin: 0;
				font-size: 16px;
				font-weight: 700;
			}

			.table {
				margin: 0;
			}

			.table th {
				background: #fafafa;
				color: #666;
				font-size: 12px;
				font-weight: 600;
				border-bottom: 1px solid #eee;
				padding: 13px 20px;
			}

			.table td {
				padding: 14px 20px;
				font-size: 13px;
				vertical-align: middle;
			}

			.badge-status {
				padding: 6px 10px;
				border-radius: 20px;
				font-size: 11px;
			}

			.badge-hadir {
				background: #e5f7ee;
				color: #16834e;
			}

			.badge-cuti {
				background: #fff0df;
				color: #c97612;
			}

			.badge-sakit {
				background: #fde7e8;
				color: #b5121b;
			}

			@media (max-width: 768px) {
				.sidebar {
					width: 70px;
				}

				.sidebar-logo {
					justify-content: center;
					padding: 0;
				}

				.sidebar-logo .logo-box {
					margin: 0;
				}

				.sidebar-logo h5,
				.sidebar-logo small,
				.menu-title,
				.sidebar-menu a span {
					display: none;
				}

				.sidebar-menu {
					padding: 0 10px;
				}

				.sidebar-menu a {
					justify-content: center;
					padding: 12px 5px;
				}

				.sidebar-menu a i {
					margin: 0;
				}

				.main-content {
					margin-left: 70px;
				}

				.content {
					padding: 20px;
				}

				.admin-info {
					display: none;
				}
			}
		</style>
	</head>

	<body>
		<div class="sidebar">
			<div class="sidebar-logo">
				<div class="logo-box">TA</div>

				<div>
					<h5>Telkom Akses</h5>
					<small>HCM Palangkaraya</small>
				</div>
			</div>

			<div class="menu-title">Menu Utama</div>

			<div class="sidebar-menu">
				<a href="#" class="active">
					<i class="bi bi-grid-1x2-fill"></i>
					<span>Dashboard</span>
				</a>

				<a href="#">
					<i class="bi bi-people-fill"></i>
					<span>Data Karyawan</span>
				</a>

				<a href="#">
					<i class="bi bi-geo-alt-fill"></i>
					<span>Data STO</span>
				</a>

				<a href="#">
					<i class="bi bi-calendar-check-fill"></i>
					<span>Absen</span>
				</a>

				<a href="#">
					<i class="bi bi-tools"></i>
					<span>Data Alat Kerja</span>
				</a>

				<div class="menu-title">Lainnya</div>

				<a href="#">
					<i class="bi bi-file-earmark-bar-graph-fill"></i>
					<span>Laporan</span>
				</a>
			</div>
		</div>

		<div class="main-content">
			<div class="topbar">
				<div class="page-title">
					<h4>Dashboard</h4>
					<small>Human Capital Management System</small>
				</div>

				<div class="admin-profile">
					<div class="admin-avatar">A</div>

					<div class="admin-info">
						<strong>Administrator</strong>
						<small>Admin HCM</small>
					</div>

					<i class="bi bi-chevron-down text-muted"></i>
				</div>
			</div>

			<div class="content">
				<div class="welcome-card">
					<h3>Selamat Datang di HCM 👋</h3>

					<p>Sistem Informasi Human Capital Management PT. Telkom Akses Branch Palangkaraya</p>
				</div>

				<div class="row g-4 mb-4">
					<div class="col-xl-3 col-md-6">
						<div class="stat-card">
							<div class="stat-icon icon-red">
								<i class="bi bi-people-fill"></i>
							</div>

							<h3>0</h3>
							<p>Total Karyawan</p>
						</div>
					</div>

					<div class="col-xl-3 col-md-6">
						<div class="stat-card">
							<div class="stat-icon icon-blue">
								<i class="bi bi-geo-alt-fill"></i>
							</div>

							<h3>0</h3>
							<p>Total STO</p>
						</div>
					</div>

					<div class="col-xl-3 col-md-6">
						<div class="stat-card">
							<div class="stat-icon icon-green">
								<i class="bi bi-calendar-check-fill"></i>
							</div>

							<h3>0</h3>
							<p>Pengajuan Absen</p>
						</div>
					</div>

					<div class="col-xl-3 col-md-6">
						<div class="stat-card">
							<div class="stat-icon icon-orange">
								<i class="bi bi-tools"></i>
							</div>

							<h3>0</h3>
							<p>Data Alat Kerja</p>
						</div>
					</div>
				</div>

				<div class="row g-4">
					<div class="col-lg-8">
						<div class="dashboard-card">
							<div class="dashboard-card-header">
								<h5>Aktivitas Absen Terbaru</h5>

								<a href="#" class="text-danger text-decoration-none small">Lihat Semua</a>
							</div>

							<div class="table-responsive">
								<table class="table">
									<thead>
										<tr>
											<th>Nama Karyawan</th>
											<th>Jenis</th>
											<th>Tanggal</th>
											<th>Status</th>
										</tr>
									</thead>

									<tbody>
										<tr>
											<td>Belum ada data</td>
											<td>-</td>
											<td>-</td>
											<td>
												<span class="badge-status badge-hadir">-</span>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<div class="col-lg-4">
						<div class="dashboard-card">
							<div class="dashboard-card-header">
								<h5>Informasi Sistem</h5>
							</div>

							<div class="p-4">
								<div class="d-flex mb-3">
									<i class="bi bi-building text-danger fs-4 me-3"></i>

									<div>
										<small class="text-muted">Perusahaan</small>
										<div class="fw-semibold">PT Telkom Akses</div>
									</div>
								</div>

								<div class="d-flex mb-3">
									<i class="bi bi-geo-alt text-danger fs-4 me-3"></i>

									<div>
										<small class="text-muted">Lokasi</small>
										<div class="fw-semibold">Palangkaraya</div>
									</div>
								</div>

								<div class="d-flex">
									<i class="bi bi-shield-check text-danger fs-4 me-3"></i>

									<div>
										<small class="text-muted">Akses</small>
										<div class="fw-semibold">Administrator</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	</body>
</html>
