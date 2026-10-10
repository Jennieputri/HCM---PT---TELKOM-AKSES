@extends("layouts")

@section("title", "Beranda")

@section("content")
	<div class="row">
		<div class="col-12">
			<div class="card mb-4">
				<div class="card-body">
					<h2 class="fs-4 mb-1">Selamat datang, {{ $employee->name }}</h2>
					<p class="text-secondary mb-0">NIK {{ $employee->nik }}</p>
				</div>
			</div>
		</div>

		<div class="col-lg-3 col-6">
			<div class="small-box text-bg-danger">
				<div class="inner">
					<h3>{{ $totalActiveEmployees }}</h3>
					<p>Karyawan Aktif</p>
				</div>

				<i class="small-box-icon bi bi-people-fill" aria-hidden="true"></i>
			</div>
		</div>
	</div>
@endsection
