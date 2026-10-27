@extends("layouts")

@section("title", "Data Absen")

@section("content")
	<div id="absen-page" data-employee-label="{{ auth()->user()->name }} ({{ auth()->user()->nik }})">
		<div class="card table-card mb-4">
			<div class="card-header">
				<div class="table-card-header">
					<div>
						<h3 class="card-title">Daftar Absen</h3>
						<p class="table-card-subtitle">Data SPPD, sakit, dan cuti karyawan.</p>
					</div>

					<div class="table-card-actions">
						<button
							type="button"
							class="btn btn-filter-toggle"
							data-bs-toggle="collapse"
							data-bs-target="#absen-filter-panel"
							aria-expanded="false"
							aria-controls="absen-filter-panel"
						>
							<i class="bi bi-funnel me-1" aria-hidden="true"></i>
							Filter
							<span class="filter-count d-none" data-filter-count="absen-filter-panel">0</span>
						</button>

						<button type="button" id="btn-add-absen" class="btn btn-primary">
							<i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
							Tambah Absen
						</button>
					</div>
				</div>
			</div>

			<div class="collapse" id="absen-filter-panel">
				<div class="filter-panel">
					<div class="filter-row">
						<div class="filter-field">
							<label for="absen-filter-karyawan" class="form-label">Karyawan</label>
							<input
								type="search"
								class="form-control"
								id="absen-filter-karyawan"
								name="karyawan"
								placeholder="Nama / NIK"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="absen-filter-jenis" class="form-label">Jenis</label>
							<select class="form-select" id="absen-filter-jenis" name="jenis" data-filter>
								<option value="">Semua</option>
								<option value="SPPD">SPPD</option>
								<option value="Sakit">Sakit</option>
								<option value="Cuti">Cuti</option>
							</select>
						</div>

						<div class="filter-field">
							<label for="absen-filter-tanggal_mulai" class="form-label">Tanggal Mulai</label>
							<input type="date" class="form-control" id="absen-filter-tanggal_mulai" name="tanggal_mulai" data-filter />
						</div>

						<div class="filter-field">
							<label for="absen-filter-tanggal_selesai" class="form-label">Tanggal Selesai</label>
							<input type="date" class="form-control" id="absen-filter-tanggal_selesai" name="tanggal_selesai" data-filter />
						</div>

						<div class="filter-field">
							<label for="absen-filter-nomor_dokumen" class="form-label">No. Dokumen</label>
							<input
								type="search"
								class="form-control"
								id="absen-filter-nomor_dokumen"
								name="nomor_dokumen"
								placeholder="No. dokumen"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="absen-filter-status" class="form-label">Status</label>
							<select class="form-select" id="absen-filter-status" name="status" data-filter>
								<option value="">Semua</option>
								<option value="Menunggu">Menunggu</option>
								<option value="Disetujui">Disetujui</option>
								<option value="Ditolak">Ditolak</option>
							</select>
						</div>

						<div class="filter-actions">
							<button type="button" class="btn btn-filter-reset" data-filter-reset>
								<i class="bi bi-arrow-counterclockwise me-1" aria-hidden="true"></i>
								Reset
							</button>
						</div>
					</div>
				</div>
			</div>

			<div class="card-body">
				<div class="table-responsive">
					<table id="absen-table" class="table data-table align-middle w-100">
						<thead>
							<tr>
								<th>No.</th>
								<th>Karyawan</th>
								<th>Jenis</th>
								<th>Tanggal Mulai</th>
								<th>Tanggal Selesai</th>
								<th>No. Dokumen</th>
								<th>Status</th>
								<th class="text-end">Aksi</th>
							</tr>
						</thead>
						<tbody></tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="absen-form-modal" tabindex="-1" aria-labelledby="absen-form-title" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<form id="absen-form" novalidate>
					<div class="modal-header">
						<h5 class="modal-title" id="absen-form-title">Tambah Absen</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
					</div>

					<div class="modal-body">
						<div class="row g-3">
							<div class="col-md-6 form-field">
								<label for="absen-jenis" class="form-label">Jenis Absen</label>
								<select class="form-select" id="absen-jenis" name="jenis" required>
									<option value="">Pilih jenis</option>
									<option value="SPPD">SPPD</option>
									<option value="Sakit">Sakit</option>
									<option value="Cuti">Cuti</option>
								</select>
								<div class="invalid-feedback"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="absen-nomor-dokumen" class="form-label">Nomor Dokumen</label>
								<input type="text" class="form-control" id="absen-nomor-dokumen" name="nomor_dokumen" readonly />
							</div>

							<div class="col-md-6 form-field">
								<label for="absen-tanggal-mulai" class="form-label">Tanggal Mulai</label>
								<input type="date" class="form-control" id="absen-tanggal-mulai" name="tanggal_mulai" required />
								<div class="invalid-feedback"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="absen-tanggal-selesai" class="form-label">Tanggal Selesai</label>
								<input type="date" class="form-control" id="absen-tanggal-selesai" name="tanggal_selesai" required />
								<div class="invalid-feedback"></div>
							</div>

							<div class="col-12 form-field">
								<label for="absen-keterangan" class="form-label">Keterangan</label>
								<textarea
									class="form-control"
									id="absen-keterangan"
									name="keterangan"
									rows="3"
									maxlength="1000"
									placeholder="Masukkan keterangan SPPD, sakit, atau cuti"
									required
								></textarea>
								<div class="invalid-feedback"></div>
							</div>
						</div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
						<button type="submit" class="btn btn-primary">Simpan</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="modal fade" id="absen-delete-modal" tabindex="-1" aria-labelledby="absen-delete-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="absen-delete-title">Hapus Absen</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
				</div>

				<div class="modal-body">
					<p class="mb-0">
						Yakin ingin menghapus absen
						<strong id="absen-delete-name"></strong>
						?
					</p>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="button" id="btn-confirm-delete-absen" class="btn btn-danger">Hapus</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@push("scripts")
	<script src="{{ asset("assets/js/pages/operasional-absen.js") }}"></script>
@endpush
