@extends("layouts")

@section("title", "Struktur Organisasi")

@push("styles")
	<link rel="stylesheet" href="{{ asset("assets/css/pages/operasional-struktur-organisasi.css") }}" />
@endpush

@section("content")
	<div class="struktur-wrapper">
		<div class="struktur-search">
			<input
				type="search"
				id="cari-struktur"
				class="form-control"
				placeholder="Cari nama, jabatan, atau sub unit..."
				aria-label="Cari struktur organisasi"
			/>
		</div>

		@if ($totalMembers > 0)
			<div class="bagan-organisasi">
				<div class="bagan-utama">
					<section class="bagan-kolom" aria-label="Project Manager">
						<div class="daftar-anggota">
							@forelse ($projectManager as $member)
								@include("operasional.partials.struktur-kartu", ["member" => $member])
							@empty
								<div class="pesan-kosong">Data PM belum ditemukan.</div>
							@endforelse
						</div>

						<div class="garis-turun"></div>

						<div class="cabang-pm">
							@foreach ($pmTeams as $team)
								<div class="cabang-tim">
									<div class="kotak-subjabatan">{{ $team["nama"] }}</div>
									<div class="garis-turun"></div>

									<div class="daftar-anggota">
										@forelse ($team["anggota"] as $member)
											@include("operasional.partials.struktur-kartu", ["member" => $member])
										@empty
											<div class="pesan-kosong">Belum ada anggota {{ $team["nama"] }}.</div>
										@endforelse
									</div>
								</div>
							@endforeach
						</div>
					</section>

					<section class="bagan-kolom" aria-label="Manager Branch">
						<div class="daftar-anggota">
							@forelse ($branchManager as $member)
								@include("operasional.partials.struktur-kartu", ["member" => $member])
							@empty
								<div class="pesan-kosong">Data Mgr Branch belum ditemukan.</div>
							@endforelse
						</div>

						<div class="garis-turun"></div>

						<div class="rantai-branch">
							@foreach ($branchTeams as $team)
								<div class="blok-jabatan">
									<div class="kotak-subjabatan">{{ $team["nama"] }}</div>
									<div class="garis-turun"></div>

									<div class="daftar-anggota">
										@forelse ($team["anggota"] as $member)
											@include("operasional.partials.struktur-kartu", ["member" => $member])
										@empty
											<div class="pesan-kosong">Belum ada anggota {{ $team["nama"] }}.</div>
										@endforelse
									</div>
								</div>
							@endforeach
						</div>
					</section>
				</div>
			</div>
		@else
			<div class="alert alert-warning">Belum ada data struktur organisasi yang dapat ditampilkan.</div>
		@endif

		<div class="struktur-info">Total data yang ditampilkan: {{ $totalMembers }} karyawan</div>
	</div>
@endsection

@push("scripts")
	<script src="{{ asset("assets/js/pages/operasional-struktur-organisasi.js") }}"></script>
@endpush
