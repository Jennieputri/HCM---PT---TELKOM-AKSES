@extends("layouts")

@section("title", "Data Role")

@section("content")
	<div
		id="role-page"
		data-list-url="{{ route("ajax.role.list") }}"
		data-save-url="{{ route("ajax.role.save") }}"
		data-delete-url="{{ route("ajax.role.delete") }}"
	>
		<div class="card table-card mb-4">
			<div class="card-header">
				<div class="table-card-header">
					<div>
						<h3 class="card-title">Daftar Role</h3>
						<p class="table-card-subtitle">Atur role yang menentukan hak akses karyawan.</p>
					</div>

					<div class="table-card-actions">
						<button
							type="button"
							class="btn btn-filter-toggle"
							data-bs-toggle="collapse"
							data-bs-target="#role-filter-panel"
							aria-expanded="false"
							aria-controls="role-filter-panel"
						>
							<i class="bi bi-funnel me-1" aria-hidden="true"></i>
							Filter
							<span class="filter-count d-none" data-filter-count="role-filter-panel">0</span>
						</button>

						<button type="button" id="btn-add-role" class="btn btn-primary">
							<i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
							Tambah Role
						</button>
					</div>
				</div>
			</div>

			<div class="collapse" id="role-filter-panel">
				<div class="filter-panel">
					<div class="filter-row">
						<div class="filter-field">
							<label for="role-filter-name" class="form-label">Nama Role</label>
							<input
								type="search"
								class="form-control"
								id="role-filter-name"
								name="name"
								placeholder="Nama role"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="role-filter-created_by" class="form-label">Dibuat Oleh</label>
							<input
								type="search"
								class="form-control"
								id="role-filter-created_by"
								name="created_by"
								placeholder="Nama / NIK"
								data-filter
							/>
						</div>

						<div class="filter-field is-range">
							<label for="role-filter-updated_from" class="form-label">Terakhir Diubah</label>
							<div class="filter-range">
								<input
									type="date"
									class="form-control"
									id="role-filter-updated_from"
									name="updated_from"
									aria-label="Terakhir Diubah dari"
									data-filter
								/>
								<span class="filter-range-separator" aria-hidden="true">–</span>
								<input
									type="date"
									class="form-control"
									id="role-filter-updated_to"
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
				<table id="role-table" class="table data-table align-middle w-100">
					<thead>
						<tr>
							<th>Nama Role</th>
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

	<div class="modal fade" id="role-form-modal" tabindex="-1" aria-labelledby="role-form-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<form id="role-form" novalidate>
					<div class="modal-header">
						<h5 class="modal-title" id="role-form-title">Tambah Role</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
					</div>

					<div class="modal-body">
						<input type="hidden" name="id" />

						<div class="form-field">
							<label for="role-name" class="form-label">Nama Role</label>
							<input
								type="text"
								class="form-control"
								id="role-name"
								name="name"
								maxlength="100"
								aria-describedby="role-name-error"
								required
							/>
							<div class="invalid-feedback" id="role-name-error"></div>
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

	<div class="modal fade" id="role-delete-modal" tabindex="-1" aria-labelledby="role-delete-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="role-delete-title">Hapus Role</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
				</div>

				<div class="modal-body">
					<p class="mb-0">
						Yakin ingin menghapus role
						<strong id="role-delete-name"></strong>
						?
					</p>
					<p class="text-secondary mb-0">Role yang masih dipakai karyawan tidak bisa dihapus.</p>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="button" id="btn-confirm-delete-role" class="btn btn-danger">Hapus</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@push("scripts")
	<script src="{{ asset("assets/js/pages/administrator-role.js") }}"></script>
@endpush
