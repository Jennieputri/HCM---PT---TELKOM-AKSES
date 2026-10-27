@extends("layouts")

@section("title", "Akses Ditolak")

@section("content")
	<div class="card mb-4">
		<div class="card-body text-center py-5">
			<i class="bi bi-shield-lock display-5 text-secondary" aria-hidden="true"></i>
			<h3 class="card-title float-none mt-3 mb-2">Akses Ditolak</h3>
			<p class="text-secondary mb-4">{{ $exception->getMessage() ?: "Anda tidak memiliki akses ke halaman ini." }}</p>
			<a href="{{ route("home") }}" class="btn btn-primary">
				<i class="bi bi-house-door me-1" aria-hidden="true"></i>
				Kembali ke Beranda
			</a>
		</div>
	</div>
@endsection
