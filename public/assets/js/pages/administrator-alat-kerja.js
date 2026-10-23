
$(function () {
    const $page = $('#alat-kerja-page');
    if (!$page.length) return;

    const formElement = document.getElementById('alat-kerja-form');
    const formModal = new bootstrap.Modal(
        document.getElementById('alat-kerja-form-modal')
    );
    const deleteModal = new bootstrap.Modal(
        document.getElementById('alat-kerja-delete-modal')
    );

    let deleteId = null;

    function showMessage(message, type = 'success') {
        $('#alat-kerja-message')
            .removeClass('d-none alert-success alert-danger alert-warning')
            .addClass('alert-' + type)
            .text(message);
    }

    function clearMessage() {
        $('#alat-kerja-message')
            .addClass('d-none')
            .removeClass('alert-success alert-danger alert-warning')
            .text('');
    }

    function clearErrors() {
        $('#alat-kerja-form .is-invalid').removeClass('is-invalid');
        $('#alat-kerja-form [data-error]').text('');
    }

    function showErrors(errors) {
        clearErrors();

        Object.keys(errors || {}).forEach(function (field) {
            const input = $('#alat-kerja-form [name="' + field + '"]');
            input.addClass('is-invalid');
            $('#alat-kerja-form [data-error="' + field + '"]')
                .text(errors[field][0]);
        });
    }

    function requestError(xhr) {
        const response = xhr.responseJSON || {};

        if (response.errors) {
            showErrors(response.errors);
            return;
        }

        showMessage(
            response.message || 'Terjadi kesalahan. Silakan coba lagi.',
            'danger'
        );
    }

    function resetForm() {
        formElement.reset();
        $('#alat-id').val('');
        clearErrors();
    }

    function escapeHtml(value) {
        return $('<div>').text(value ?? '').html();
    }

    const table = $('#alat-kerja-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: $page.data('list-url'),
            type: 'POST',
            data: function (data) {
                data._token = $('meta[name="csrf-token"]').attr('content');
            },
            error: function (xhr) {
                requestError(xhr);
            }
        },
            columns: [
        {data: 'karyawan',defaultContent: '-'},
        {data: 'kode_alat',defaultContent: ''},
        {data: 'nama_alat',defaultContent: ''},
        {data: 'tanggal_penyerahan',defaultContent: ''},
        {data: 'kondisi',
            render: function(data, type) {
                if (type !== 'display') return data;

                const color = data === 'Baik' ? 'success' : 'danger';

                return '<span class="badge bg-' + color + '">' +
                    escapeHtml(data) +
                    '</span>';
            }
        },
        {data: null,
            orderable: false,
            searchable: false,
            className: 'text-end text-nowrap',
            render: function(data, type, row) {
                if (type !== 'display') return '';

                return `
                    <button type="button"
                        class="btn btn-sm btn-warning btn-edit-alat"
                        title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button"
                        class="btn btn-sm btn-danger btn-delete-alat"
                        data-id="${row.id}"
                        data-name="${escapeHtml(row.nama_alat)}"
                        title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
            }
        }
    ],
            order: [[0, 'asc']],
            language: {
                processing: 'Memuat data...',
                search: 'Cari:',
                lengthMenu: 'Tampilkan _MENU_ data',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ data',
                infoEmpty: 'Belum ada data',
                zeroRecords: 'Data tidak ditemukan',
                emptyTable: 'Belum ada data alat kerja',        
                paginate: {
                    first: '«',
                    previous: '‹',
                    next: '›',
                    last: '»'
                }
            }
        });

    $('#btn-add-alat-kerja').on('click', function () {
        resetForm();
        clearMessage();
        $('#alat-kerja-form-title').text('Tambah Alat Kerja');
        formModal.show();
    });

    $('#alat-kerja-table').on('click', '.btn-edit-alat', function () {
        const row = table.row($(this).closest('tr')).data();
        if (!row) return;

        resetForm();
        clearMessage();

        $('#alat-kerja-form-title').text('Edit Alat Kerja');
        $('#alat-id').val(row.id);
        $('#alat-kode').val(row.kode_alat);
        $('#alat-nama').val(row.nama_alat);
        $('#alat-tanggal').val(row.tanggal_penyerahan);
        $('#alat-kondisi').val(row.kondisi);
        
        formModal.show();
    });

    $('#alat-kerja-form').on('submit', function (event) {
        event.preventDefault();
        clearErrors();

        const $button = $('#btn-save-alat-kerja');
        $button.prop('disabled', true);
        $('#alat-save-spinner').removeClass('d-none');

        $.ajax({
            url: $page.data('save-url'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (response) {
                formModal.hide();
                table.ajax.reload(null, false);
                showMessage(response.message || 'Data berhasil disimpan.');
            },
            error: requestError,
            complete: function () {
                $button.prop('disabled', false);
                $('#alat-save-spinner').addClass('d-none');
            }
        });
    });

    $('#alat-kerja-table').on('click', '.btn-delete-alat', function () {
        deleteId = $(this).data('id');
        $('#alat-kerja-delete-name').text($(this).data('name'));
        deleteModal.show();
    });

    $('#btn-confirm-delete-alat-kerja').on('click', function () {
        if (!deleteId) return;

        const $button = $(this);
        $button.prop('disabled', true);

        $.ajax({
            url: $page.data('delete-url'),
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                id: deleteId
            },
            success: function (response) {
                deleteModal.hide();
                table.ajax.reload(null, false);
                showMessage(response.message || 'Data berhasil dihapus.');
                deleteId = null;
            },
            error: requestError,
            complete: function () {
                $button.prop('disabled', false);
            }
        });
    });
});
