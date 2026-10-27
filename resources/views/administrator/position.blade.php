@extends("layouts")

@section("title", "Data Position")

@section("content")
	<div
		id="position-page"
		data-list-url="{{ route("ajax.position.list") }}"
		data-save-url="{{ route("ajax.position.save") }}"
		data-delete-url="{{ route("ajax.position.delete") }}"
	>
		<div class="card table-card mb-4">
			<div class="card-header">
				<div class="table-card-header">
					<div>
						<h3 class="card-title">Daftar Position</h3>
						<p class="table-card-subtitle">Kelola daftar jabatan yang tersedia.</p>
					</div>

					<div class="table-card-actions">
						<button
							type="button"
							class="btn btn-filter-toggle"
							data-bs-toggle="collapse"
							data-bs-target="#position-filter-panel"
							aria-expanded="false"
							aria-controls="position-filter-panel"
						>
							<i class="bi bi-funnel me-1" aria-hidden="true"></i>
							Filter
							<span class="filter-count d-none" data-filter-count="position-filter-panel">0</span>
						</button>

						<button type="button" id="btn-add-position" class="btn btn-primary">
							<i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
							Tambah Position
						</button>
					</div>
				</div>
			</div>

			<div class="collapse" id="position-filter-panel">
				<div class="filter-panel">
					<div class="filter-row">
						<div class="filter-field">
							<label for="position-filter-name" class="form-label">Nama Position</label>
							<input
								type="search"
								class="form-control"
								id="position-filter-name"
								name="name"
								placeholder="Nama position"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="position-filter-created_by" class="form-label">Dibuat Oleh</label>
							<input
								type="search"
								class="form-control"
								id="position-filter-created_by"
								name="created_by"
								placeholder="Nama / NIK"
								data-filter
							/>
						</div>

						<div class="filter-field is-range">
							<label for="position-filter-updated_from" class="form-label">Terakhir Diubah</label>
							<div class="filter-range">
								<input
									type="date"
									class="form-control"
									id="position-filter-updated_from"
									name="updated_from"
									aria-label="Terakhir Diubah dari"
									data-filter
								/>
								<span class="filter-range-separator" aria-hidden="true">–</span>
								<input
									type="date"
									class="form-control"
									id="position-filter-updated_to"
									name="updated_to"
									aria-label="Terakhir Diubah sampai"
									data-filter
								/>
							</div>
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
				<table id="position-table" class="table data-table align-middle w-100">
					<thead>
						<tr>
							<th>Nama Position</th>
							<th>Dibuat Oleh</th>
							<th>Terakhir Diubah</th>
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
		id="position-form-modal"
		tabindex="-1"
		aria-labelledby="position-form-title"
		aria-hidden="true"
	>
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form id="position-form" novalidate>
					<div class="modal-header">
						<h5 class="modal-title" id="position-form-title">Tambah Position</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
					</div>

					<div class="modal-body">
						<input type="hidden" name="id" />

						<div class="form-field">
							<label for="position-name" class="form-label">Nama Position</label>
							<input
								type="text"
								class="form-control"
								id="position-name"
								name="name"
								maxlength="100"
								aria-describedby="position-name-error"
								required
							/>
							<div class="invalid-feedback" id="position-name-error"></div>
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
		id="position-delete-modal"
		tabindex="-1"
		aria-labelledby="position-delete-title"
		aria-hidden="true"
	>
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="position-delete-title">Hapus Position</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
				</div>

				<div class="modal-body">
					<p class="mb-0">
						Yakin ingin menghapus position
						<strong id="position-delete-name"></strong>
						?
					</p>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="button" id="btn-confirm-delete-position" class="btn btn-danger">Hapus</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@push("scripts")
	<script src="{{ asset("assets/js/pages/administrator-position.js") }}"></script>
@endpush
