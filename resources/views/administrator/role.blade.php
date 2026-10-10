@extends("layouts")

@section("title", "Data Role")

@section("content")
	<div
		id="role-page"
		data-list-url="{{ route("ajax.role.list") }}"
		data-save-url="{{ route("ajax.role.save") }}"
		data-delete-url="{{ route("ajax.role.delete") }}"
	>
		<div class="card mb-4">
			<div class="card-header">
				<div class="row g-2 align-items-center">
					<div class="col-12 col-md-6">
						<h3 class="card-title">Daftar Role</h3>
					</div>

					<div class="col-12 col-md-6 text-md-end">
						<button type="button" id="btn-add-role" class="btn btn-primary">
							<i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
							Tambah Role
						</button>
					</div>
				</div>
			</div>

			<div class="card-body">
				<table id="role-table" class="table table-hover align-middle w-100">
					<thead>
						<tr>
							<th>Nama Role</th>
							<th class="text-end">Aksi</th>
						</tr>
					</thead>
				</table>
			</div>
		</div>
	</div>

	<div class="modal fade" id="role-form-modal" tabindex="-1" aria-labelledby="role-form-title" aria-hidden="true">
		<div class="modal-dialog">
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
		<div class="modal-dialog">
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
