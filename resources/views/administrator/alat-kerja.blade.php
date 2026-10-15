@extends("layouts")

@section("title", "Data Alat Kerja")

@section("content")
<div
    id="alat-kerja-page"
    data-list-url="{{ route('ajax.alat-kerja.list') }}"
    data-save-url="{{ route('ajax.alat-kerja.save') }}"
    data-delete-url="{{ route('ajax.alat-kerja.delete') }}"
>
    {{-- Card Daftar Alat Kerja --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Daftar Alat Kerja </h3>

            <button
                type="button"
                id="btn-add-alat-kerja"
                class="btn btn-primary ms-auto"
            >
                <i class="bi bi-plus-lg me-1"></i>
                Tambah Alat Kerja
            </button>
        </div>

        <div class="card-body">
            <div
                id="alat-kerja-message"
                class="alert d-none"
                role="alert"
            ></div>

            <div class="table-responsive">
                <table
                    id="alat-kerja-table"
                    class="table table-bordered table-hover align-middle w-100"
                >
                    <thead class="table-light">
                        <tr>
                            <th>NIK Karyawan</th>
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
</div>

{{-- Modal Tambah/Edit --}}
<div class="modal fade" id="alat-kerja-form-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="alat-kerja-form">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="alat-kerja-form-title">Tambah Alat Kerja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="id" id="alat-id">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="alat-nik" class="form-label">NIK Karyawan</label>
                            <input type="number" class="form-control" name="nik_karyawan" id="alat-nik" required>
                            <div class="invalid-feedback" data-error="nik_karyawan"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="alat-kode" class="form-label">Kode Alat</label>
                            <input type="text" class="form-control" name="kode_alat" id="alat-kode" maxlength="50" required>
                            <div class="invalid-feedback" data-error="kode_alat"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="alat-nama" class="form-label">Nama Alat</label>
                            <input type="text" class="form-control" name="nama_alat" id="alat-nama" maxlength="100" required>
                            <div class="invalid-feedback" data-error="nama_alat"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="alat-tanggal" class="form-label">Tanggal Penyerahan</label>
                            <input type="date" class="form-control" name="tanggal_penyerahan" id="alat-tanggal" required>
                            <div class="invalid-feedback" data-error="tanggal_penyerahan"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="alat-kondisi" class="form-label">Kondisi Alat</label>
                            <select class="form-select" name="kondisi" id="alat-kondisi" required>
                                <option value="">-- Pilih Kondisi --</option>
                                <option value="Baik">Baik</option>
                                <option value="Rusak">Rusak</option>
                            </select>
                            <div class="invalid-feedback" data-error="kondisi"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="alat-status" class="form-label">Status Pengembalian</label>
                            <select class="form-select" name="status" id="alat-status" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="Belum Dikembalikan">Belum Dikembalikan</option>
                                <option value="Sudah Dikembalikan">Sudah Dikembalikan</option>
                            </select>
                            <div class="invalid-feedback" data-error="status"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn-save-alat-kerja">
                        <span class="save-label">Simpan</span>
                        <span class="spinner-border spinner-border-sm d-none" id="alat-save-spinner"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Hapus --}}
<div class="modal fade" id="alat-kerja-delete-modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Alat Kerja</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                Yakin ingin menghapus data alat kerja <strong id="alat-kerja-delete-name"></strong>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="btn-confirm-delete-alat-kerja">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push("scripts")
<script src="{{ asset('assets/js/pages/administrator-alat-kerja.js') }}"></script>
@endpush
