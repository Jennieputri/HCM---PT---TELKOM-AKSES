@extends("layouts")

@section("title", "Beranda")

@section("content")
	<div id="dashboard-page" class="dashboard" data-summary-url="{{ route("ajax.dashboard.summary") }}">
		<section class="dashboard-hero reveal" aria-labelledby="dashboard-greeting">
			<div class="dashboard-hero-text">
				<p class="dashboard-eyebrow" id="dashboard-today"></p>
				<h2 class="dashboard-greeting" id="dashboard-greeting">
					<span id="dashboard-salutation">Selamat datang</span>
					,
					{{ $employee->name }}
				</h2>
				<p class="dashboard-subtitle">NIK {{ $employee->nik }} · Ringkasan data HCM Telkom Akses Palangkaraya hari ini.</p>
			</div>

			<div class="dashboard-hero-actions">
				@if ($isHcm)
					<a href="{{ route("administrator.approval") }}" class="btn btn-hero">
						<i class="bi bi-person-check" aria-hidden="true"></i>
						Tinjau Persetujuan
					</a>
				@endif

				<a href="{{ route("operasional.sto") }}" class="btn btn-hero is-ghost">
					<i class="bi bi-geo-alt" aria-hidden="true"></i>
					Buka Peta STO
				</a>
			</div>

			<span class="dashboard-hero-orbit is-large" aria-hidden="true"></span>
			<span class="dashboard-hero-orbit is-small" aria-hidden="true"></span>
		</section>

		@php($statTileColumn = $isHcm ? "col-12 col-sm-6 col-xl-3" : "col-12 col-md-4")

		<section class="row g-3 mb-3" aria-label="Indikator utama">
			@if ($isHcm)
				<div class="{{ $statTileColumn }}">
					<article class="stat-tile reveal">
						<span class="stat-tile-icon" aria-hidden="true"><i class="bi bi-people-fill"></i></span>
						<p class="stat-tile-label">Karyawan Aktif</p>
						<p class="stat-tile-value" data-count-up="employees.active">0</p>
						<p class="stat-tile-caption">
							dari
							<span data-count-up="employees.total">0</span>
							akun terdaftar
						</p>
					</article>
				</div>
			@endif

			<div class="{{ $statTileColumn }}">
				<article class="stat-tile reveal">
					<span class="stat-tile-icon" aria-hidden="true"><i class="bi bi-tools"></i></span>
					<p class="stat-tile-label">Alat Kerja Dipinjam</p>
					<p class="stat-tile-value" data-count-up="alat_kerja.borrowed">0</p>
					<p class="stat-tile-caption">
						dari
						<span data-count-up="alat_kerja.total">0</span>
						alat tercatat
					</p>
				</article>
			</div>

			<div class="{{ $statTileColumn }}">
				<article class="stat-tile reveal">
					<span class="stat-tile-icon" aria-hidden="true"><i class="bi bi-broadcast-pin"></i></span>
					<p class="stat-tile-label">STO Aktif</p>
					<p class="stat-tile-value" data-count-up="sto.active">0</p>
					<p class="stat-tile-caption">
						dari
						<span data-count-up="sto.total">0</span>
						lokasi STO
					</p>
				</article>
			</div>

			<div class="{{ $statTileColumn }}">
				@if ($isHcm)
					<a href="{{ route("administrator.approval") }}" class="stat-tile is-link reveal">
						<span class="stat-tile-icon is-warning" aria-hidden="true"><i class="bi bi-hourglass-split"></i></span>
						<p class="stat-tile-label">Menunggu Persetujuan</p>
						<p class="stat-tile-value" data-count-up="administration.pending_approvals">0</p>
						<p class="stat-tile-caption">pendaftar baru perlu ditinjau</p>
					</a>
				@else
					<article class="stat-tile reveal">
						<span class="stat-tile-icon" aria-hidden="true"><i class="bi bi-pin-map"></i></span>
						<p class="stat-tile-label">Kecamatan Terlayani</p>
						<p class="stat-tile-value" data-count-up="sto.kecamatan">0</p>
						<p class="stat-tile-caption">wilayah dengan STO</p>
					</article>
				@endif
			</div>
		</section>

		<div class="row g-3">
			@if ($isHcm)
				<div class="col-12 col-lg-7">
					<section class="card dashboard-panel reveal h-100" aria-labelledby="employee-status-title">
						<div class="card-body">
							<header class="dashboard-panel-header">
								<div>
									<h3 class="dashboard-panel-title" id="employee-status-title">Komposisi Status Karyawan</h3>
									<p class="dashboard-panel-subtitle">Seluruh akun di sistem berdasarkan status akses.</p>
								</div>
								<p class="dashboard-panel-total">
									<span data-count-up="employees.total">0</span>
									akun
								</p>
							</header>

							<div class="stacked-bar" id="employee-status-bar" role="img" aria-label="Komposisi status karyawan"></div>
							<ul class="status-legend" id="employee-status-legend"></ul>
						</div>
					</section>
				</div>
			@endif

			<div class="col-12 col-lg-5">
				<section class="card dashboard-panel reveal h-100" aria-labelledby="alat-kerja-title">
					<div class="card-body">
						<header class="dashboard-panel-header">
							<div>
								<h3 class="dashboard-panel-title" id="alat-kerja-title">Kesiapan Alat Kerja</h3>
								<p class="dashboard-panel-subtitle">Kondisi dan pengembalian alat yang diserahkan.</p>
							</div>
						</header>

						<div class="meter-list" id="alat-kerja-meters"></div>
					</div>
				</section>
			</div>

			<div class="col-12 col-lg-7">
				<section class="card dashboard-panel reveal h-100" aria-labelledby="sto-kecamatan-title">
					<div class="card-body">
						<header class="dashboard-panel-header">
							<div>
								<h3 class="dashboard-panel-title" id="sto-kecamatan-title">Sebaran STO per Kecamatan</h3>
								<p class="dashboard-panel-subtitle">Jumlah lokasi STO di setiap kecamatan.</p>
							</div>
							<a href="{{ route("operasional.sto") }}" class="dashboard-panel-link">
								Lihat peta
								<i class="bi bi-arrow-right" aria-hidden="true"></i>
							</a>
						</header>

						<ul class="bar-list" id="sto-kecamatan-bars" aria-label="Jumlah STO per kecamatan"></ul>
					</div>
				</section>
			</div>

			@if ($isHcm)
				<div class="col-12 col-lg-5">
					<section class="card dashboard-panel reveal h-100" aria-labelledby="master-data-title">
						<div class="card-body">
							<header class="dashboard-panel-header">
								<div>
									<h3 class="dashboard-panel-title" id="master-data-title">Data Master</h3>
									<p class="dashboard-panel-subtitle">Referensi yang dipakai di seluruh modul.</p>
								</div>
							</header>

							<div class="master-data-list">
								<a href="{{ route("administrator.employee") }}" class="master-data-item">
									<span class="master-data-icon" aria-hidden="true"><i class="bi bi-people"></i></span>
									<span class="master-data-label">Karyawan</span>
									<span class="master-data-value" data-count-up="employees.total">0</span>
								</a>
								<a href="{{ route("administrator.role") }}" class="master-data-item">
									<span class="master-data-icon" aria-hidden="true"><i class="bi bi-shield-lock"></i></span>
									<span class="master-data-label">Role</span>
									<span class="master-data-value" data-count-up="administration.roles">0</span>
								</a>
								<a href="{{ route("administrator.position") }}" class="master-data-item">
									<span class="master-data-icon" aria-hidden="true"><i class="bi bi-person-badge"></i></span>
									<span class="master-data-label">Position</span>
									<span class="master-data-value" data-count-up="administration.positions">0</span>
								</a>
							</div>
						</div>
					</section>
				</div>
			@endif
		</div>
	</div>
@endsection

@push("scripts")
	<script src="{{ asset("assets/js/pages/home-dashboard.js") }}"></script>
@endpush
