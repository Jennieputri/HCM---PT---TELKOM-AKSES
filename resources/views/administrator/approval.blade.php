@extends("layouts")

@section("title", "Persetujuan Akun")

@section("content")
	<div
		id="approval-page"
		data-list-url="{{ route("ajax.approval.list") }}"
		data-approve-url="{{ route("ajax.approval.approve") }}"
		data-reject-url="{{ route("ajax.approval.reject") }}"
	>
		<div class="card table-card mb-4">
			<div class="card-header">
				<div class="table-card-header">
					<div>
						<h3 class="card-title">Daftar Pendaftar</h3>
						<p class="table-card-subtitle">Akun baru dari halaman Register perlu disetujui HCM sebelum bisa login.</p>
					</div>

					<div class="table-card-actions">
						<button
							type="button"
							class="btn btn-filter-toggle"
							data-bs-toggle="collapse"
							data-bs-target="#approval-filter-panel"
							aria-expanded="false"
							aria-controls="approval-filter-panel"
						>
							<i class="bi bi-funnel me-1" aria-hidden="true"></i>
							Filter
							<span class="filter-count d-none" data-filter-count="approval-filter-panel">0</span>
						</button>
					</div>
				</div>
			</div>

			<div class="collapse" id="approval-filter-panel">
				<div class="filter-panel">
					<div class="filter-row">
						<div class="filter-field">
							<label for="approval-filter-pendaftar" class="form-label">Pendaftar</label>
							<input
								type="search"
								class="form-control"
								id="approval-filter-pendaftar"
								name="pendaftar"
								placeholder="Nama / NIK"
								data-filter
							/>
						</div>

						<div class="filter-field is-range">
							<label for="approval-filter-registered_from" class="form-label">Tanggal Daftar</label>
							<div class="filter-range">
								<input
									type="date"
									class="form-control"
									id="approval-filter-registered_from"
									name="registered_from"
									aria-label="Tanggal Daftar dari"
									data-filter
								/>
								<span class="filter-range-separator" aria-hidden="true">–</span>
								<input
									type="date"
									class="form-control"
									id="approval-filter-registered_to"
									name="registered_to"
									aria-label="Tanggal Daftar sampai"
									data-filter
								/>
							</div>
						</div>

						<div class="filter-field">
							<label for="approval-filter-status" class="form-label">Status</label>
							<select class="form-select" id="approval-filter-status" name="status" data-filter>
								<option value="">Semua</option>
								<option value="2">Menunggu</option>
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
				<table id="approval-table" class="table data-table align-middle w-100">
					<thead>
						<tr>
							<th>Pendaftar</th>
							<th>Tanggal Daftar</th>
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
		id="approval-confirm-modal"
		tabindex="-1"
		aria-labelledby="approval-confirm-title"
		aria-hidden="true"
	>
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="approval-confirm-title"></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
				</div>

				<div class="modal-body">
					<p class="mb-0" id="approval-confirm-message"></p>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
					<button type="button" id="btn-confirm-approval" class="btn"></button>
				</div>
			</div>
		</div>
	</div>
@endsection

@push("scripts")
	<script src="{{ asset("assets/js/pages/administrator-approval.js") }}"></script>
@endpush
