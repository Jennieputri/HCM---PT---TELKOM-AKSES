@extends("layouts")

@section("title", "Data Karyawan")

@section("content")
	<div
		id="employee-page"
		data-list-url="{{ route("ajax.employee.list") }}"
		data-save-url="{{ route("ajax.employee.save") }}"
		data-delete-url="{{ route("ajax.employee.delete") }}"
		data-role-options-url="{{ route("ajax.role.options") }}"
	>
		<div class="card table-card mb-4">
			<div class="card-header">
				<div class="table-card-header">
					<div>
						<h3 class="card-title">Daftar Karyawan</h3>
						<p class="table-card-subtitle">Kelola akun karyawan beserta role dan status aksesnya.</p>
					</div>

					<div class="table-card-actions">
						<button
							type="button"
							class="btn btn-filter-toggle"
							data-bs-toggle="collapse"
							data-bs-target="#employee-filter-panel"
							aria-expanded="false"
							aria-controls="employee-filter-panel"
						>
							<i class="bi bi-funnel me-1" aria-hidden="true"></i>
							Filter
							<span class="filter-count d-none" data-filter-count="employee-filter-panel">0</span>
						</button>

						<button type="button" id="btn-add-employee" class="btn btn-primary">
							<i class="bi bi-person-plus-fill me-1" aria-hidden="true"></i>
							Tambah Karyawan
						</button>
					</div>
				</div>
			</div>

			<div class="collapse" id="employee-filter-panel">
				<div class="filter-panel">
					<div class="filter-row">
						<div class="filter-field">
							<label for="employee-filter-karyawan" class="form-label">Karyawan</label>
							<input
								type="search"
								class="form-control"
								id="employee-filter-karyawan"
								name="karyawan"
								placeholder="Nama / NIK"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="employee-filter-email" class="form-label">Email</label>
							<input
								type="search"
								class="form-control"
								id="employee-filter-email"
								name="email"
								placeholder="Email"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="employee-filter-number_phone" class="form-label">No. HP</label>
							<input
								type="search"
								class="form-control"
								id="employee-filter-number_phone"
								name="number_phone"
								placeholder="No. HP"
								data-filter
							/>
						</div>

						<div class="filter-field">
							<label for="employee-filter-role" class="form-label">Role</label>
							<select class="form-select" id="employee-filter-role" name="role_id" data-filter data-select2-manual>
								<option></option>
							</select>
						</div>

						<div class="filter-field">
							<label for="employee-filter-status" class="form-label">Status</label>
							<select class="form-select" id="employee-filter-status" name="status" data-filter>
								<option value="">Semua</option>
								<option value="1">Aktif</option>
								<option value="0">Nonaktif</option>
								<option value="2">Menunggu Persetujuan</option>
								<option value="3">Ditolak</option>
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
				<table id="employee-table" class="table data-table align-middle w-100">
					<thead>
						<tr>
							<th>Karyawan</th>
							<th>Email</th>
							<th>No. HP</th>
							<th>Role</th>
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
		id="employee-form-modal"
		tabindex="-1"
		aria-labelledby="employee-form-title"
		aria-hidden="true"
	>
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<form id="employee-form" novalidate>
					<div class="modal-header">
						<h5 class="modal-title" id="employee-form-title">Tambah Karyawan</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
					</div>

					<div class="modal-body">
						<input type="hidden" name="id" />

						<div class="row g-3">
							<div class="col-md-6 form-field">
								<label for="employee-nik" class="form-label">NIK</label>
								<input
									type="text"
									inputmode="numeric"
									class="form-control"
									id="employee-nik"
									name="nik"
									maxlength="10"
									aria-describedby="employee-nik-error"
									required
								/>
								<div class="invalid-feedback" id="employee-nik-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="employee-name" class="form-label">Nama Lengkap</label>
								<input
									type="text"
									class="form-control"
									id="employee-name"
									name="name"
									maxlength="100"
									aria-describedby="employee-name-error"
									required
								/>
								<div class="invalid-feedback" id="employee-name-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="employee-phone" class="form-label">No. HP</label>
								<input
									type="tel"
									class="form-control"
									id="employee-phone"
									name="number_phone"
									maxlength="15"
									aria-describedby="employee-phone-error"
								/>
								<div class="invalid-feedback" id="employee-phone-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="employee-email" class="form-label">Email</label>
								<input
									type="email"
									class="form-control"
									id="employee-email"
									name="email"
									maxlength="50"
									aria-describedby="employee-email-error"
								/>
								<div class="invalid-feedback" id="employee-email-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="employee-role" class="form-label">Role</label>
								<select
									data-select2-manual
									class="form-select"
									id="employee-role"
									name="role_id"
									aria-describedby="employee-role-error"
									required
								></select>
								<div class="invalid-feedback" id="employee-role-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="employee-status" class="form-label">Status</label>
								<select
									class="form-select"
									id="employee-status"
									name="status"
									aria-describedby="employee-status-error"
									required
								>
									<option value="1">Aktif</option>
									<option value="0">Nonaktif</option>
								</select>
								<div class="invalid-feedback" id="employee-status-error"></div>
							</div>

							<div class="col-12 form-field">
								<label for="employee-address" class="form-label">Alamat</label>
								<textarea
									class="form-control"
									id="employee-address"
									name="address"
									rows="2"
									maxlength="1000"
									aria-describedby="employee-address-error"
								></textarea>
								<div class="invalid-feedback" id="employee-address-error"></div>
							</div>

							<div class="col-md-6 form-field">
								<label for="employee-password" class="form-label">Password</label>
								<input
									type="password"
									class="form-control"
									id="employee-password"
									name="password"
									maxlength="255"
									autocomplete="new-password"
									aria-describedby="employee-password-help employee-password-error"
								/>
								<div class="form-text" id="employee-password-help"></div>
								<div class="invalid-feedback" id="employee-password-error"></div>
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
		id="employee-delete-modal"
		tabindex="-1"
		aria-labelledby="employee-delete-title"
		aria-hidden="true"
	>
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="employee-delete-title">Hapus Karyawan</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
				</div>

				<div class="modal-body">
					<p class="mb-0">
						Yakin ingin menghapus karyawan
						<strong id="employee-delete-name"></strong>
						?
					</p>
					<p class="text-secondary mb-0">Data yang dihapus tidak bisa dikembalikan.</p>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="button" id="btn-confirm-delete-employee" class="btn btn-danger">Hapus</button>
				</div>
			</div>
		</div>
	</div>
@endsection

@push("scripts")
	<script src="{{ asset("assets/js/pages/administrator-employee.js") }}"></script>
@endpush
