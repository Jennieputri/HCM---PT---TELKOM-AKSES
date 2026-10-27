@extends("layouts")

@section("title", "Data STO")

@section("content")
	<div
		id="sto-page"
		data-list-url="{{ route("ajax.operasional.sto.list") }}"
		data-markers-url="{{ route("ajax.operasional.sto.markers") }}"
		data-save-url="{{ route("ajax.operasional.sto.save") }}"
		data-delete-url="{{ route("ajax.operasional.sto.delete") }}"
	>
		<div class="card table-card mb-4">
			<div class="card-header">
				<div class="table-card-header">
					<div>
						<h3 class="card-title">Data STO</h3>
						<p class="table-card-subtitle">
							Sebaran lokasi STO Telkom Akses Palangkaraya. Klik peta atau cari koordinat untuk menandai titik baru.
						</p>
					</div>

					<div class="table-card-actions">
						<button
							type="button"
							class="btn btn-filter-toggle"
							data-bs-toggle="collapse"
							data-bs-target="#sto-filter-panel"
							aria-expanded="false"
							aria-controls="sto-filter-panel"
						>
							<i class="bi bi-funnel me-1" aria-hidden="true"></i>
							Filter
							<span class="filter-count d-none" data-filter-count="sto-filter-panel">0</span>
						</button>

						<button type="button" id="btn-add-sto" class="btn btn-primary">
							<i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
							Tambah STO
						</button>
					</div>
				</div>
			</div>

			<div class="collapse" id="sto-filter-panel">
				<div class="filter-panel">
					<div class="filter-row">
						<div class="filter-field">
							<label for="sto-filter-kode_sto" class="form-label">Kode</label>
							<input
								type="search"
								class="form-control"
								id="sto-filter-kode_sto"
								name="kode_sto"
								placeholder="Kode"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="sto-filter-nama_sto" class="form-label">Nama STO</label>
							<input
								type="search"
								class="form-control"
								id="sto-filter-nama_sto"
								name="nama_sto"
								placeholder="Nama STO"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="sto-filter-kecamatan" class="form-label">Kecamatan</label>
							<input
								type="search"
								class="form-control"
								id="sto-filter-kecamatan"
								name="kecamatan"
								placeholder="Kecamatan"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="sto-filter-alamat" class="form-label">Alamat</label>
							<input
								type="search"
								class="form-control"
								id="sto-filter-alamat"
								name="alamat"
								placeholder="Alamat"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="sto-filter-koordinat" class="form-label">Koordinat</label>
							<input
								type="search"
								class="form-control"
								id="sto-filter-koordinat"
								name="koordinat"
								placeholder="Lat / Long"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="sto-filter-status" class="form-label">Status</label>
							<select class="form-select" id="sto-filter-status" name="status" data-filter>
								<option value="">Semua</option>
								<option value="Aktif">Aktif</option>
								<option value="Nonaktif">Nonaktif</option>
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
				<form id="sto-coordinate-search" class="sto-map-control sto-coordinate-search" role="search" novalidate>
					<label for="sto-coordinate-input" class="visually-hidden">Cari titik berdasarkan latitude dan longitude</label>
					<div class="input-group">
						<span class="input-group-text">
							<i class="bi bi-crosshair" aria-hidden="true"></i>
						</span>
						<input
							type="search"
							class="form-control"
							id="sto-coordinate-input"
							placeholder="Cari latitude, longitude"
							autocomplete="off"
							maxlength="60"
							aria-describedby="sto-coordinate-error"
						/>
						<button type="submit" class="btn" aria-label="Cari koordinat">
							<i class="bi bi-search" aria-hidden="true"></i>
						</button>
					</div>
					<p class="sto-coordinate-error d-none" id="sto-coordinate-error" role="alert"></p>
				</form>

				<div class="sto-map-control sto-layer-toggle" role="group" aria-label="Mode tampilan peta">
					<button type="button" class="btn active" data-map-layer="map" aria-pressed="true">
						<i class="bi bi-map" aria-hidden="true"></i>
						Peta
					</button>
					<button type="button" class="btn" data-map-layer="satellite" aria-pressed="false">
						<i class="bi bi-globe-asia-australia" aria-hidden="true"></i>
						Satelit
					</button>
				</div>

				<div id="sto-map" class="sto-map mb-4" role="region" aria-label="Peta lokasi STO"></div>

				<table id="sto-table" class="table data-table align-middle w-100">
					<thead>
						<tr>
							<th>Kode</th>
							<th>Nama STO</th>
							<th>Kecamatan</th>
							<th>Alamat</th>
							<th>Koordinat</th>
							<th>Status</th>
							<th class="text-end">Aksi</th>
						</tr>
					</thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="modal fade" id="sto-form-modal" tabindex="-1" aria-labelledby="sto-form-title" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<form id="sto-form" novalidate>
					<div class="modal-header">
						<h5 class="modal-title" id="sto-form-title">Tambah STO</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
					</div>

					<div class="modal-body">
						<input type="hidden" name="id" />

						<div class="row g-3">
							<div class="col-md-6 form-field">
								<label for="sto-kode" class="form-label">Kode STO</label>
								<input
									type="text"
									class="form-control"
									id="sto-kode"
									name="kode_sto"
									maxlength="20"
									aria-describedby="sto-kode-error"
									required
								/>
								<div class="invalid-feedback" id="sto-kode-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="sto-nama" class="form-label">Nama STO</label>
								<input
									type="text"
									class="form-control"
									id="sto-nama"
									name="nama_sto"
									maxlength="100"
									aria-describedby="sto-nama-error"
									required
								/>
								<div class="invalid-feedback" id="sto-nama-error"></div>
							</div>

							<div class="col-12 form-field">
								<label for="sto-alamat" class="form-label">Alamat</label>
								<input
									type="text"
									class="form-control"
									id="sto-alamat"
									name="alamat"
									maxlength="255"
									aria-describedby="sto-alamat-error"
									required
								/>
								<div class="invalid-feedback" id="sto-alamat-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="sto-kecamatan" class="form-label">Kecamatan</label>
								<input
									type="text"
									class="form-control"
									id="sto-kecamatan"
									name="kecamatan"
									maxlength="100"
									aria-describedby="sto-kecamatan-error"
								/>
								<div class="invalid-feedback" id="sto-kecamatan-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="sto-status" class="form-label">Status</label>
								<select class="form-select" id="sto-status" name="status" aria-describedby="sto-status-error" required>
									<option value="Aktif">Aktif</option>
									<option value="Nonaktif">Nonaktif</option>
								</select>
								<div class="invalid-feedback" id="sto-status-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="sto-latitude" class="form-label">Latitude</label>
								<input
									type="number"
									class="form-control"
									id="sto-latitude"
									name="latitude"
									step="any"
									min="-90"
									max="90"
									aria-describedby="sto-latitude-error sto-coordinate-help"
									required
								/>
								<div class="invalid-feedback" id="sto-latitude-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="sto-longitude" class="form-label">Longitude</label>
								<input
									type="number"
									class="form-control"
									id="sto-longitude"
									name="longitude"
									step="any"
									min="-180"
									max="180"
									aria-describedby="sto-longitude-error sto-coordinate-help"
									required
								/>
								<div class="invalid-feedback" id="sto-longitude-error"></div>
							</div>
						</div>

						<p class="form-text mb-0 mt-3" id="sto-coordinate-help">
							Klik peta atau cari koordinat, lalu pilih "Tambah STO di sini" agar latitude dan longitude terisi otomatis.
						</p>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
						<button type="submit" class="btn btn-primary">Simpan</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="modal fade" id="sto-delete-modal" tabindex="-1" aria-labelledby="sto-delete-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="sto-delete-title">Hapus STO</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
				</div>

				<div class="modal-body">
					<p class="mb-0">
						Yakin ingin menghapus STO
						<strong id="sto-delete-name"></strong>
						?
					</p>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="button" id="btn-confirm-delete-sto" class="btn btn-danger">Hapus</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@push("styles")
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@push("scripts")
	<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
	<script src="{{ asset("assets/js/pages/operasional-sto.js") }}"></script>
@endpush
