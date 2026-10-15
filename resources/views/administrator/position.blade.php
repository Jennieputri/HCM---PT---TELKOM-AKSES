@extends("layouts")

@section("title", "Data Position")

@section("content")
    <div
        id="position-page"
        data-list-url="{{ route('ajax.position.list') }}"
        data-save-url="{{ route('ajax.position.save') }}"
        data-delete-url="{{ route('ajax.position.delete') }}"
    >
        <div class="card position-card mb-4">
            <div class="card-header">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-6">
                        <h3 class="card-title mb-0">
                            Daftar Position
                        </h3>
                    </div>

                    <div class="col-12 col-md-6 text-md-end">
                        <button
                            type="button"
                            id="btn-add-position"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-plus-lg me-1"
                               aria-hidden="true"></i>
                            Tambah Position
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row align-items-center mb-3">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6"></div>
                </div>

                <table
                    id="position-table"
                    class="table align-middle w-100"
                >
                    <thead>
                        <tr>
                            <th>Nama Position</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Tambah dan Edit Position --}}
    <div
        class="modal fade"
        id="position-form-modal"
        tabindex="-1"
        aria-labelledby="position-form-title"
        aria-hidden="true"
    >
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="position-form" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title" id="position-form-title">
                            Tambah Position
                        </h5>
                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Tutup"
                        ></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="id">

                        <div class="mb-3">
                            <label for="position-name" class="form-label">
                                Nama Position
                            </label>
                            <input
                                type="text"
                                class="form-control"
                                id="position-name"
                                name="name"
                                maxlength="100"
                                aria-describedby="position-name-error"
                                required
                            >
                            <div
                                class="invalid-feedback"
                                id="position-name-error"
                            ></div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal"
                        >
                            Batal
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Simpan
                        </button>
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="position-delete-title">
                        Hapus Position
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Tutup"
                    ></button>
                </div>

                <div class="modal-body">
                    <p class="mb-2">
                        Yakin ingin menghapus position
                        <strong id="position-delete-name"></strong>?
                    </p>
                    <p class="text-secondary mb-0">
                        Position yang masih digunakan karyawan
                        tidak bisa dihapus.
                    </p>
                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Batal
                    </button>
                    <button
                        type="button"
                        id="btn-confirm-delete-position"
                        class="btn btn-danger"
                    >
                        Hapus
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push("scripts")
    <script src="{{ asset('assets/js/pages/administrator-position.js') }}"></script>
@endpush