@extends("layouts")

@section("title", "Data Absen")

@section("content")
	<div
		id="absen-page"
		data-is-hcm="{{ auth()->user()->isHcm() ? "1" : "0" }}"
		data-list-url="{{ route("ajax.operasional.absen.list") }}"
		data-nomor-dokumen-url="{{ route("ajax.operasional.absen.nomor-dokumen") }}"
		data-save-url="{{ route("ajax.operasional.absen.save") }}"
		data-delete-url="{{ route("ajax.operasional.absen.delete") }}"
		data-approve-url="{{ route("ajax.operasional.absen.approve") }}"
		data-reject-url="{{ route("ajax.operasional.absen.reject") }}"
	>
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

						@unless (auth()->user()->isHcm())
							<button type="button" id="btn-add-absen" class="btn btn-primary">
								<i class="bi bi-plus-lg me-1" aria-hidden="true"></i>
								Tambah Absen
							</button>
						@endunless
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
								<th>NIK</th>
								<th>Nama</th>
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
		<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<form id="absen-form" enctype="multipart/form-data" novalidate>
					<div class="modal-header">
						<h5 class="modal-title fw-bold" id="absen-form-title">Tambah Absen</h5>

						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
					</div>

					<div class="modal-body">
						<div class="form-field mb-4">
							<label for="absen-nomor-dokumen" class="form-label fw-semibold">Nomor Dokumen</label>
							<input
								type="text"
								class="form-control"
								id="absen-nomor-dokumen"
								name="nomor_dokumen"
								placeholder="Nomor dokumen dibuat otomatis"
								readonly
							/>
							<small class="text-muted">Nomor dokumen dibuat otomatis oleh sistem.</small>
						</div>

						<div id="cuti-jenis-wrapper" class="form-field mb-4 d-none">
							<label for="cuti-jenis" class="form-label fw-semibold">Jenis Cuti</label>
							<select class="form-select" id="cuti-jenis" name="jenis_cuti" required>
								<option value="">Pilih jenis cuti</option>
								<option value="Tahunan">Tahunan</option>
								<option value="Besar">Besar</option>
								<option value="CAP">CAP</option>
								<option value="Sakit">Sakit</option>
								<option value="Bersalin">Bersalin</option>
								<option value="Haid">Haid</option>
							</select>
							<div class="invalid-feedback">Pilih jenis cuti.</div>
						</div>

						<div id="absen-step-pilih">
							<div class="form-field">
								<label for="absen-jenis" class="form-label fw-semibold">Jenis Absen</label>

								<select class="form-select" id="absen-jenis" name="jenis" required>
									<option value="">Pilih jenis absen</option>
									<option value="Cuti">Cuti</option>
									<option value="SPPD">SPPD</option>
									<option value="Sakit">Sakit</option>
								</select>

								<div class="invalid-feedback">Silakan pilih jenis absen terlebih dahulu.</div>
							</div>

							<div class="alert alert-light border mt-3 mb-0">
								<i class="bi bi-info-circle me-2"></i>
								Pilih jenis absen, kemudian tekan
								<strong>Selanjutnya</strong>
								untuk mengisi formulir.
							</div>
						</div>

						<div id="absen-step-form" class="d-none">
							<section id="absen-form-cuti" class="d-none">
								<div class="alert alert-danger py-2">
									<i class="bi bi-calendar-check me-2"></i>
									<strong>FORM PENGAJUAN CUTI</strong>
								</div>

								<div class="row g-3">
									<div class="col-12">
										<h6 class="fw-bold border-bottom pb-2">Data Karyawan</h6>
									</div>

									<div class="col-md-6 form-field">
										<label class="form-label">NIK</label>
										<input type="text" class="form-control" value="{{ auth()->user()->nik }}" readonly />
									</div>

									<div class="col-md-6 form-field">
										<label class="form-label">Nama Karyawan</label>
										<input type="text" class="form-control" value="{{ auth()->user()->name }}" readonly />
									</div>

									<div class="col-md-6 form-field">
										<label for="cuti-band" class="form-label">Band</label>
										<input
											type="text"
											class="form-control"
											id="cuti-band"
											name="band"
											maxlength="100"
											placeholder="Masukkan band"
										/>
									</div>

									<div class="col-md-6 form-field">
										<label for="cuti-jabatan" class="form-label">Jabatan</label>
										<input
											type="text"
											class="form-control"
											id="cuti-jabatan"
											name="jabatan"
											maxlength="150"
											placeholder="Masukkan jabatan"
										/>
									</div>

									<div class="col-12 form-field">
										<label for="cuti-unit-kerja" class="form-label">Unit Kerja</label>
										<input
											type="text"
											class="form-control"
											id="cuti-unit-kerja"
											name="unit_kerja"
											maxlength="150"
											placeholder="Masukkan unit kerja"
										/>
									</div>

									<div class="col-12">
										<h6 class="fw-bold border-bottom pb-2">Data Pengajuan Cuti</h6>
									</div>

									<div class="col-md-4 form-field">
										<label for="cuti-lama" class="form-label">Lama Cuti (Hari)</label>
										<input
											type="number"
											class="form-control"
											id="cuti-lama"
											name="lama_cuti"
											min="1"
											placeholder="Terhitung otomatis"
											readonly
											required
										/>
										<small class="text-muted">Dihitung otomatis berdasarkan tanggal mulai dan selesai.</small>
									</div>

									<div class="col-md-4 form-field">
										<label for="cuti-mulai" class="form-label">Mulai Tanggal</label>
										<input type="date" class="form-control" id="cuti-mulai" name="cuti_tanggal_mulai" required />
										<div class="invalid-feedback">Pilih tanggal mulai cuti.</div>
									</div>

									<div class="col-md-4 form-field">
										<label for="cuti-selesai" class="form-label">Sampai Dengan</label>
										<input type="date" class="form-control" id="cuti-selesai" name="cuti_tanggal_selesai" required />
										<div class="invalid-feedback">Pilih tanggal selesai cuti.</div>
									</div>

									<div class="col-md-6 form-field">
										<label for="cuti-alamat" class="form-label">Alamat Selama Cuti</label>
										<textarea class="form-control" id="cuti-alamat" name="alamat_selama_cuti" rows="2" required></textarea>
										<div class="invalid-feedback">Masukkan alamat selama cuti.</div>
									</div>

									<div class="col-md-6 form-field">
										<label for="cuti-telepon" class="form-label">Telepon</label>
										<input type="text" class="form-control" id="cuti-telepon" name="telepon_cuti" maxlength="20" required />
										<div class="invalid-feedback">Masukkan nomor telepon.</div>
									</div>

									<div class="col-12">
										<h6 class="fw-bold border-bottom pb-2">Perjalanan Cuti Berangkat</h6>
										<small class="text-muted">Isi tanggal perjalanan jika ada.</small>
									</div>

									<div class="col-md-6 form-field">
										<label for="cuti-berangkat-mulai" class="form-label">Mulai Tanggal</label>
										<input type="date" class="form-control" id="cuti-berangkat-mulai" name="perjalanan_berangkat_mulai" />
									</div>

									<div class="col-md-6 form-field">
										<label for="cuti-berangkat-selesai" class="form-label">Sampai Dengan</label>
										<input type="date" class="form-control" id="cuti-berangkat-selesai" name="perjalanan_berangkat_selesai" />
									</div>

									<div class="col-12">
										<h6 class="fw-bold border-bottom pb-2">Perjalanan Cuti Pulang</h6>
									</div>

									<div class="col-md-6 form-field">
										<label for="cuti-pulang-mulai" class="form-label">Mulai Tanggal</label>
										<input type="date" class="form-control" id="cuti-pulang-mulai" name="perjalanan_pulang_mulai" />
									</div>

									<div class="col-md-6 form-field">
										<label for="cuti-pulang-selesai" class="form-label">Sampai Dengan</label>
										<input type="date" class="form-control" id="cuti-pulang-selesai" name="perjalanan_pulang_selesai" />
									</div>

									<div class="col-12 form-field">
										<label for="cuti-alasan" class="form-label">Alasan Cuti</label>
										<textarea
											class="form-control"
											id="cuti-alasan"
											name="alasan_cuti"
											rows="3"
											maxlength="1000"
											required
										></textarea>
										<div class="invalid-feedback">Masukkan alasan cuti.</div>
									</div>
								</div>
							</section>

							<section id="absen-form-sppd" class="d-none">
								<div class="alert alert-danger py-2">
									<i class="bi bi-briefcase me-2"></i>
									<strong>FORM SURAT TUGAS / SPPD</strong>
								</div>

								<div class="row g-3">
									<div class="col-md-6 form-field">
										<label for="sppd-tanggal-surat" class="form-label">Tanggal Surat</label>
										<input type="date" class="form-control" id="sppd-tanggal-surat" name="tanggal_surat" required />
										<div class="invalid-feedback">Pilih tanggal surat.</div>
									</div>

									<div class="col-md-6 form-field">
										<label for="sppd-perihal" class="form-label">Perihal Pekerjaan</label>
										<input type="text" class="form-control" id="sppd-perihal" name="perihal_pekerjaan" maxlength="255" required />
										<div class="invalid-feedback">Masukkan perihal pekerjaan.</div>
									</div>

									<div class="col-12 form-field">
										<label for="sppd-lokasi" class="form-label">Dilokasi</label>
										<input type="text" class="form-control" id="sppd-lokasi" name="lokasi_pekerjaan" maxlength="255" required />
										<div class="invalid-feedback">Masukkan lokasi pekerjaan.</div>
									</div>

									<div class="col-12">
										<h6 class="fw-bold border-bottom pb-2">Daftar Karyawan yang Ditugaskan</h6>
										<small class="text-muted">Isi satu sampai tiga karyawan sesuai daftar pada surat tugas.</small>
									</div>

									<div class="col-12">
										<div class="table-responsive">
											<table class="table table-bordered align-middle">
												<thead class="table-light">
													<tr>
														<th>No.</th>
														<th>NIK</th>
														<th>Nama</th>
														<th>Jabatan / Position</th>
														<th>Kota Asal</th>
														<th>Kota Tujuan</th>
													</tr>
												</thead>
												<tbody>
													@for ($i = 0; $i < 3; $i++)
														<tr>
															<td>{{ $i + 1 }}</td>
															<td>
																<input type="text" class="form-control" name="sppd_karyawan[{{ $i }}][nik]" maxlength="30" />
															</td>
															<td>
																<input type="text" class="form-control" name="sppd_karyawan[{{ $i }}][nama]" maxlength="150" />
															</td>
															<td>
																<input type="text" class="form-control" name="sppd_karyawan[{{ $i }}][position]" maxlength="150" />
															</td>
															<td>
																<input type="text" class="form-control" name="sppd_karyawan[{{ $i }}][kota_asal]" maxlength="150" />
															</td>
															<td>
																<input type="text" class="form-control" name="sppd_karyawan[{{ $i }}][kota_tujuan]" maxlength="150" />
															</td>
														</tr>
													@endfor
												</tbody>
											</table>
										</div>
									</div>

									<div class="col-md-6 form-field">
										<label for="sppd-mulai" class="form-label">Tanggal Mulai Penugasan</label>
										<input type="date" class="form-control" id="sppd-mulai" name="sppd_tanggal_mulai" required />
										<div class="invalid-feedback">Pilih tanggal mulai penugasan.</div>
									</div>

									<div class="col-md-6 form-field">
										<label for="sppd-selesai" class="form-label">Tanggal Selesai Penugasan</label>
										<input type="date" class="form-control" id="sppd-selesai" name="sppd_tanggal_selesai" required />
										<div class="invalid-feedback">Pilih tanggal selesai penugasan.</div>
									</div>
								</div>
							</section>

							<section id="absen-form-sakit" class="d-none">
								<div class="alert alert-danger py-2">
									<i class="bi bi-heart-pulse me-2"></i>
									<strong>FORM PENGAJUAN SAKIT</strong>
								</div>

								<div class="row g-3">
									<div class="col-md-6 form-field">
										<label for="absen-tanggal-mulai" class="form-label">Tanggal Mulai</label>
										<input type="date" class="form-control" id="absen-tanggal-mulai" name="tanggal_mulai" required />
										<div class="invalid-feedback">Pilih tanggal mulai.</div>
									</div>

									<div class="col-md-6 form-field">
										<label for="absen-tanggal-selesai" class="form-label">Tanggal Selesai</label>
										<input type="date" class="form-control" id="absen-tanggal-selesai" name="tanggal_selesai" required />
										<div class="invalid-feedback">Pilih tanggal selesai.</div>
									</div>

									<div class="col-12 form-field">
										<label for="absen-keterangan" class="form-label">Keterangan</label>
										<textarea
											class="form-control"
											id="absen-keterangan"
											name="keterangan"
											rows="3"
											maxlength="1000"
											placeholder="Masukkan keterangan sakit"
											required
										></textarea>
										<div class="invalid-feedback">Masukkan keterangan.</div>
									</div>

									<div class="col-12 form-field">
										<label for="absen-surat-dokter" class="form-label">
											Surat Dokter
											<span class="text-muted fw-normal">(Opsional)</span>
										</label>
										<input
											type="file"
											class="form-control"
											id="absen-surat-dokter"
											name="surat_dokter"
											accept=".pdf,.jpg,.jpeg,.png"
										/>
										<small class="text-muted">Format PDF, JPG, atau PNG. Maksimal 2 MB.</small>
									</div>
								</div>
							</section>
						</div>
					</div>

					<div class="modal-footer" id="absen-footer-pilih">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>

						<button type="button" class="btn btn-primary" id="btn-absen-selanjutnya">
							Selanjutnya
							<i class="bi bi-arrow-right ms-1"></i>
						</button>
					</div>

					<div class="modal-footer d-none" id="absen-footer-form">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>

						<button type="submit" class="btn btn-primary" id="btn-absen-ajukan">
							<i class="bi bi-send me-1"></i>
							Ajukan
						</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<div class="modal fade" id="absen-approval-modal" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
				</div>

				<div class="modal-body">
					<div class="form-field">
						<label for="absen-approval-keterangan" class="form-label">Keterangan</label>

						<textarea
							class="form-control"
							id="absen-approval-keterangan"
							rows="4"
							maxlength="1000"
							placeholder="Masukkan keterangan persetujuan atau penolakan..."
						></textarea>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" id="btn-absen-setujui" class="btn btn-success">
						<i class="bi bi-check-lg me-1"></i>
						Setujui
					</button>

					<button type="button" id="btn-absen-tolak" class="btn btn-danger">
						<i class="bi bi-x-lg me-1"></i>
						Tolak
					</button>
				</div>
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
