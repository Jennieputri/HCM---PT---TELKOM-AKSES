@extends("layouts")

@section("title", "Data Alat Kerja")

@section("content")
	<div
		id="alat-kerja-page"
		data-list-url="{{ route("ajax.operasional.alat-kerja.list") }}"
		data-save-url="{{ route("ajax.operasional.alat-kerja.save") }}"
		data-delete-url="{{ route("ajax.operasional.alat-kerja.delete") }}"
	>
		<div class="card table-card mb-4">
			<div class="card-header">
				<div class="table-card-header">
					<div>
						<h3 class="card-title">Daftar Alat Kerja</h3>
						<p class="table-card-subtitle">Pantau alat kerja yang diserahkan ke karyawan beserta kondisinya.</p>
					</div>

					<div class="table-card-actions">
						<button
							type="button"
							class="btn btn-filter-toggle"
							data-bs-toggle="collapse"
							data-bs-target="#alat-kerja-filter-panel"
							aria-expanded="false"
							aria-controls="alat-kerja-filter-panel"
						>
							<i class="bi bi-funnel me-1" aria-hidden="true"></i>
							Filter
							<span class="filter-count d-none" data-filter-count="alat-kerja-filter-panel">0</span>
						</button>

						<button type="button" id="btn-add-alat-kerja" class="btn btn-primary">
							<i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
							Tambah Alat Kerja
						</button>
					</div>
				</div>
			</div>

			<div class="collapse" id="alat-kerja-filter-panel">
				<div class="filter-panel">
					<div class="filter-row">
						<div class="filter-field">
							<label for="alat-kerja-filter-karyawan" class="form-label">Karyawan</label>
							<input
								type="search"
								class="form-control"
								id="alat-kerja-filter-karyawan"
								name="karyawan"
								placeholder="Nama / NIK"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="alat-kerja-filter-kode_alat" class="form-label">Kode Alat</label>
							<input
								type="search"
								class="form-control"
								id="alat-kerja-filter-kode_alat"
								name="kode_alat"
								placeholder="Kode"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="alat-kerja-filter-nama_alat" class="form-label">Nama Alat</label>
							<input
								type="search"
								class="form-control"
								id="alat-kerja-filter-nama_alat"
								name="nama_alat"
								placeholder="Nama alat"
								data-filter
							/>
						</div>

						<div class="filter-field is-range">
							<label for="alat-kerja-filter-tanggal_from" class="form-label">Tanggal Penyerahan</label>
							<div class="filter-range">
								<input
									type="date"
									class="form-control"
									id="alat-kerja-filter-tanggal_from"
									name="tanggal_from"
									aria-label="Tanggal Penyerahan dari"
									data-filter
								/>
								<span class="filter-range-separator" aria-hidden="true">–</span>
								<input
									type="date"
									class="form-control"
									id="alat-kerja-filter-tanggal_to"
									name="tanggal_to"
									aria-label="Tanggal Penyerahan sampai"
									data-filter
								/>
							</div>
						</div>

						<div class="filter-field">
							<label for="alat-kerja-filter-kondisi" class="form-label">Kondisi</label>
							<select class="form-select" id="alat-kerja-filter-kondisi" name="kondisi" data-filter>
								<option value="">Semua</option>
								<option value="Baik">Baik</option>
								<option value="Rusak">Rusak</option>
							</select>
						</div>

						<div class="filter-field">
							<label for="alat-kerja-filter-status" class="form-label">Status</label>
							<select class="form-select" id="alat-kerja-filter-status" name="status" data-filter>
								<option value="">Semua</option>
								<option value="Belum Dikembalikan">Belum Dikembalikan</option>
								<option value="Sudah Dikembalikan">Sudah Dikembalikan</option>
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
				<table id="alat-kerja-table" class="table data-table align-middle w-100">
					<thead>
						<tr>
							<th>Karyawan</th>
							<th>Kode Alat</th>
							<th>Nama Alat</th>
							<th>Tanggal Penyerahan</th>
							<th>Kondisi</th>
							<th>Status</th>
							<th class="text-end">Aksi</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>

	<div
		class="modal fade"
		id="alat-kerja-form-modal"
		tabindex="-1"
		aria-labelledby="alat-kerja-form-title"
		aria-hidden="true"
	>
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<form id="alat-kerja-form" novalidate>
					<div class="modal-header">
						<h5 class="modal-title" id="alat-kerja-form-title">Tambah Alat Kerja</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
					</div>

					<div class="modal-body">
						<input type="hidden" name="id" />

						<div class="row g-3">
							<div class="col-md-6 form-field">
								<label for="alat-kerja-kode" class="form-label">Kode Alat</label>
								<input
									type="text"
									class="form-control"
									id="alat-kerja-kode"
									name="kode_alat"
									maxlength="50"
									aria-describedby="alat-kerja-kode-error"
									required
								/>
								<div class="invalid-feedback" id="alat-kerja-kode-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="alat-kerja-nama" class="form-label">Nama Alat</label>
								<input
									type="text"
									class="form-control"
									id="alat-kerja-nama"
									name="nama_alat"
									maxlength="100"
									aria-describedby="alat-kerja-nama-error"
									required
								/>
								<div class="invalid-feedback" id="alat-kerja-nama-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="alat-kerja-tanggal" class="form-label">Tanggal Penyerahan</label>
								<input
									type="date"
									class="form-control"
									id="alat-kerja-tanggal"
									name="tanggal_penyerahan"
									aria-describedby="alat-kerja-tanggal-error"
									required
								/>
								<div class="invalid-feedback" id="alat-kerja-tanggal-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="alat-kerja-kondisi" class="form-label">Kondisi Alat</label>
								<select
									class="form-select"
									id="alat-kerja-kondisi"
									name="kondisi"
									aria-describedby="alat-kerja-kondisi-error"
									required
								>
									<option value="">Pilih kondisi</option>
									<option value="Baik">Baik</option>
									<option value="Rusak">Rusak</option>
								</select>
								<div class="invalid-feedback" id="alat-kerja-kondisi-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="alat-kerja-status" class="form-label">Status Pengembalian</label>
								<select
									class="form-select"
									id="alat-kerja-status"
									name="status"
									aria-describedby="alat-kerja-status-error"
									required
								>
									<option value="Belum Dikembalikan">Belum Dikembalikan</option>
									<option value="Sudah Dikembalikan">Sudah Dikembalikan</option>
								</select>
								<div class="invalid-feedback" id="alat-kerja-status-error"></div>
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

	<div
		class="modal fade"
		id="alat-kerja-delete-modal"
		tabindex="-1"
		aria-labelledby="alat-kerja-delete-title"
		aria-hidden="true"
	>
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="alat-kerja-delete-title">Hapus Alat Kerja</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
				</div>

				<div class="modal-body">
					<p class="mb-0">
						Yakin ingin menghapus alat kerja
						<strong id="alat-kerja-delete-name"></strong>
						?
					</p>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="button" id="btn-confirm-delete-alat-kerja" class="btn btn-danger">Hapus</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@push("scripts")
	<script src="{{ asset("assets/js/pages/operasional-alat-kerja.js") }}"></script>
@endpush
