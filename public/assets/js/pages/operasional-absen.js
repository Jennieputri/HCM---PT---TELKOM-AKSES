$(function () {
    var $page = $('#absen-page');
    var $form = $('#absen-form');
    var $filterPanel = $('#absen-filter-panel');
    var $tableBody = $('#absen-table tbody');

    // ROLE PENGGUNA
    var isHcm = String($page.attr('data-is-hcm')) === '1';

    // MODAL
    var formModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('absen-form-modal')
    );

    var deleteModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('absen-delete-modal')
    );

    var approvalModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('absen-approval-modal')
    );

    // DATA
    var absenList = [];
    var editedAbsenId = null;
    var absenIdToDelete = null;
    var approvalAbsenId = null;

    var statusTones = {
        Menunggu: 'warning',
        Disetujui: 'success',
        Ditolak: 'danger'
    };

    // URL AJAX
    var urls = {
        list: $page.data('ajax-list') || '/ajax/operasional/absen/list',
        save: $page.data('ajax-save') || '/ajax/operasional/absen/save',
        delete: $page.data('ajax-delete') || '/ajax/operasional/absen/delete',
        approve: $page.data('ajax-approve') || '/ajax/operasional/absen/approve',
        reject: $page.data('ajax-reject') || '/ajax/operasional/absen/reject'
    };

    // HCM TIDAK BOLEH MENAMBAH ABSEN
    if (isHcm) {
        $('#btn-add-absen').hide();
    }

    // REQUEST AJAX
    function sendRequest(url, data) {
        return $.ajax({
            url: url,
            type: 'POST',
            data: data,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            }
        });
    }

    // PESAN ERROR
    function showAjaxError(xhr) {
        console.error(
            'Error Data Absen:',
            xhr.status,
            xhr.responseJSON || xhr.responseText
        );

        var message = 'Gagal memproses data absen.';

        if (xhr.responseJSON) {
            if (xhr.responseJSON.errors) {
                message = Object.values(xhr.responseJSON.errors)
                    .flat()
                    .join('\n');
            } else if (xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
        }

        showMessage(message, 'danger');
    }

    // FORMAT TANGGAL
    function formatDate(value) {
        if (!value) return '-';

        var parts = String(value).substring(0, 10).split('-');

        if (parts.length !== 3) return value;

        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }

    // CELL TABEL
    function createTextCell(value) {
        return $('<td></td>').text(value == null ? '-' : value);
    }

    // RENDER BARIS ABSEN
    function renderAbsenRow(absen, rowNumber) {
        var $row = $('<tr></tr>');

        $row.append(
            createTextCell(rowNumber),
            createTextCell(absen.nik_karyawan),
            createTextCell(absen.nama_karyawan),
            createTextCell(absen.jenis),
            createTextCell(formatDate(absen.tanggal_mulai)),
            createTextCell(formatDate(absen.tanggal_selesai)),
            createTextCell(absen.nomor_dokumen)
        );

        // STATUS
        var $status = $('<td></td>').html(
            renderStatusPill(
                absen.status,
                statusTones[absen.status] || 'secondary'
            )
        );

        // AKSI
        var $action = $('<td class="text-end"></td>')
            .attr('data-absen-id', absen.id);

        // DETAIL
        var $detailButton = $(`
            <button type="button"
                class="btn btn-sm btn-outline-info btn-detail"
                title="Detail">
                <i class="bi bi-eye"></i>
            </button>
        `);

        $action.append($detailButton);

        // AKSI HCM
        if (isHcm) {

            if (absen.status === 'Menunggu') {

                var $editButton = $(`
                    <button type="button"
                        class="btn btn-sm btn-outline-primary btn-approval-edit ms-1"
                        title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                `);

                $action.append($editButton);
            }

        } else {

            // AKSI KARYAWAN
            if (absen.status === 'Menunggu') {

                var $editButton = $(`
                    <button type="button"
                        class="btn btn-sm btn-outline-primary btn-edit ms-1"
                        title="Edit">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                `);

                var $deleteButton = $(`
                    <button type="button"
                        class="btn btn-sm btn-outline-danger btn-delete ms-1"
                        title="Hapus">
                        <i class="bi bi-trash"></i>
                    </button>
                `);

                $action.append($editButton, $deleteButton);
            }
        }

        $row.append($status, $action);

        return $row;
    }

    // FILTER
    function matchesFilters(absen, filters) {
        function contains(value, keyword) {
            return !keyword ||
                String(value || '').toLowerCase().includes(
                    String(keyword).toLowerCase()
                );
        }

        return contains(absen.nik_karyawan, filters.karyawan) ||
            false;
    }

    function matchesFiltersComplete(absen, filters) {
        function contains(value, keyword) {
            return !keyword ||
                String(value || '').toLowerCase().includes(
                    String(keyword).toLowerCase()
                );
        }

        return contains(absen.nik_karyawan, filters.karyawan) ||
            false;
    }

    // MENAMPILKAN TABEL
    function renderAbsenTable() {
        var filters = readFilterValues($filterPanel);
        var rowNumber = 0;

        $tableBody.empty();

        $.each(absenList, function (_, absen) {

            var cocok = (
                (!filters.karyawan ||
                    containsEmployee(absen, filters.karyawan)) &&

                (!filters.nomor_dokumen ||
                    String(absen.nomor_dokumen || '').toLowerCase()
                        .includes(filters.nomor_dokumen.toLowerCase())) &&

                (!filters.jenis || absen.jenis === filters.jenis) &&

                (!filters.status || absen.status === filters.status) &&

                (!filters.tanggal_mulai ||
                    absen.tanggal_mulai >= filters.tanggal_mulai) &&

                (!filters.tanggal_selesai ||
                    absen.tanggal_selesai <= filters.tanggal_selesai)
            );

            if (cocok) {
                rowNumber++;

                $tableBody.append(
                    renderAbsenRow(absen, rowNumber)
                );
            }
        });

        if (rowNumber === 0) {
            $tableBody.append(`
                <tr>
                    <td colspan="9" class="table-empty">
                        Belum ada data absen.
                    </td>
                </tr>
            `);
        }
    }

    function containsEmployee(absen, keyword) {
        var search = String(keyword || '').toLowerCase();

        return String(absen.nama_karyawan || '')
            .toLowerCase().includes(search) ||
            String(absen.nik_karyawan || '')
                .toLowerCase().includes(search);
    }

    // LOAD DATA
    function loadAbsen() {
        sendRequest(urls.list, {
            draw: 1,
            start: 0,
            length: 100,
            'search[value]': ''
        })
        .done(function (response) {
            absenList = response.data || [];
            renderAbsenTable();
        })
        .fail(function (xhr) {
            showAjaxError(xhr);

            $tableBody.html(`
                <tr>
                    <td colspan="9" class="table-empty">
                        Gagal mengambil data dari database.
                    </td>
                </tr>
            `);
        });
    }

    // MODAL TAMBAH / EDIT KARYAWAN
    function openFormModal(title) {
        if (isHcm) return;

        $form[0].reset();

        clearFormErrors($form);

        $('#absen-form-title').text(title);

        if (title === 'Tambah Absen') {

            var today = new Date();

            var datePart = today.getFullYear()
                + String(today.getMonth() + 1).padStart(2, '0')
                + String(today.getDate()).padStart(2, '0');

            var sequence = 1;

            var existingNumbers = absenList
                .map(function (item) {
                    return item.nomor_dokumen || '';
                })
                .filter(function (number) {
                    return number.startsWith('ABS-' + datePart + '-');
                });

            existingNumbers.forEach(function (number) {
                var parts = number.split('-');
                var currentSequence = parseInt(parts[2], 10);

                if (!isNaN(currentSequence) && currentSequence >= sequence) {
                    sequence = currentSequence + 1;
                }
            });

            var nomorDokumen = 'ABS-' + datePart + '-'
                + String(sequence).padStart(3, '0');

            $form.find('[name="nomor_dokumen"]').val(nomorDokumen);
        }

        formModal.show();
    }

    // FILTER PANEL
    bindFilterPanel($filterPanel, renderAbsenTable);

    // TAMBAH ABSEN
    $('#btn-add-absen').on('click', function () {
        if (isHcm) return;

        editedAbsenId = null;

        openFormModal('Tambah Absen');
    });

    // SIMPAN ABSEN KARYAWAN
    $form.on('submit', function (event) {
        event.preventDefault();

        if (isHcm) return;

        clearFormErrors($form);

        var data = {
            jenis: $form.find('[name="jenis"]').val(),
            tanggal_mulai: $form.find('[name="tanggal_mulai"]').val(),
            tanggal_selesai: $form.find('[name="tanggal_selesai"]').val(),
            keterangan: $form.find('[name="keterangan"]').val()
        };

        var errors = {};

        if (!data.jenis) {
            errors.jenis = ['Jenis absen wajib dipilih!'];
        }

        if (!data.tanggal_mulai) {
            errors.tanggal_mulai = ['Tanggal mulai wajib diisi!'];
        }

        if (!data.tanggal_selesai) {
            errors.tanggal_selesai = ['Tanggal selesai wajib diisi!'];
        } else if (
            data.tanggal_mulai &&
            data.tanggal_selesai < data.tanggal_mulai
        ) {
            errors.tanggal_selesai = [
                'Tanggal selesai tidak boleh sebelum tanggal mulai!'
            ];
        }

        if (!String(data.keterangan || '').trim()) {
            errors.keterangan = ['Keterangan wajib diisi!'];
        }

        if (!$.isEmptyObject(errors)) {
            showFieldErrors(errors, $form);
            return;
        }

        if (editedAbsenId !== null) {
            data.id = editedAbsenId;
        }

        var $button = $form.find('[type="submit"]');

        $button.prop('disabled', true);

        sendRequest(urls.save, data)
            .done(function (response) {
                formModal.hide();

                editedAbsenId = null;

                showMessage(
                    response.message || 'Data absen berhasil disimpan.',
                    'success'
                );

                loadAbsen();
            })
            .fail(showAjaxError)
            .always(function () {
                $button.prop('disabled', false);
            });
    });

    // DETAIL ABSEN
    $tableBody.on('click', '.btn-detail', function () {

        var id = Number(
            $(this).closest('td').attr('data-absen-id')
        );

        var absen = absenList.find(function (item) {
            return Number(item.id) === id;
        });

        if (!absen) return;

        var detailRows = [
            ['Karyawan', absen.nama_karyawan || '-'],
            ['NIK', absen.nik_karyawan || '-'],
            ['Jenis Absen', absen.jenis],
            ['Tanggal Mulai', formatDate(absen.tanggal_mulai)],
            ['Tanggal Selesai', formatDate(absen.tanggal_selesai)],
            ['Nomor Dokumen', absen.nomor_dokumen],
            ['Keterangan Pengajuan', absen.keterangan],
            ['Status', absen.status]
        ];

        if (absen.keterangan_admin) {
            detailRows.push([
                'Keterangan Admin',
                absen.keterangan_admin
            ]);
        }

        detailRows.push([
            'Diajukan pada',
            absen.created_at || '-'
        ]);

        showRecordDetail(absen.nomor_dokumen, detailRows);
    });

    // ======================================
    // EDIT KHUSUS HCM - BUKA MODAL PERSETUJUAN
    // ======================================

    $tableBody.on('click', '.btn-approval-edit', function () {

        if (!isHcm) return;

        var id = Number(
            $(this).closest('td').attr('data-absen-id')
        );

        var absen = absenList.find(function (item) {
            return Number(item.id) === id;
        });

        if (!absen || absen.status !== 'Menunggu') {
            return;
        }

        approvalAbsenId = id;

        $('#absen-approval-id').val(id);

        // Kosongkan keterangan keputusan sebelumnya
        $('#absen-approval-keterangan').val('');

        // Buka pop-up
        approvalModal.show();
    });

    // ======================================
    // PROSES SETUJUI / TOLAK
    // ======================================

    function processApproval(decision) {

        if (!isHcm || approvalAbsenId === null) return;

        var keterangan = String(
            $('#absen-approval-keterangan').val() || ''
        ).trim();

        // Keterangan wajib diisi
        if (!keterangan) {
            showMessage(
                'Keterangan wajib diisi sebelum memberikan keputusan.',
                'danger'
            );

            $('#absen-approval-keterangan').trigger('focus');

            return;
        }

        var url = decision === 'Disetujui'
            ? urls.approve
            : urls.reject;

        var $approveButton = $('#btn-absen-setujui');
        var $rejectButton = $('#btn-absen-tolak');

        $approveButton.prop('disabled', true);
        $rejectButton.prop('disabled', true);

        sendRequest(url, {
            id: approvalAbsenId,
            keterangan_admin: keterangan
        })
        .done(function (response) {

            approvalModal.hide();

            approvalAbsenId = null;

            $('#absen-approval-id').val('');
            $('#absen-approval-keterangan').val('');

            showMessage(
                response.message ||
                (decision === 'Disetujui'
                    ? 'Pengajuan berhasil disetujui.'
                    : 'Pengajuan berhasil ditolak.'),
                'success'
            );

            // Muat ulang status dari database
            loadAbsen();
        })
        .fail(showAjaxError)
        .always(function () {
            $approveButton.prop('disabled', false);
            $rejectButton.prop('disabled', false);
        });
    }

    // TOMBOL SETUJUI
    $('#btn-absen-setujui').on('click', function () {
        processApproval('Disetujui');
    });

    // TOMBOL TOLAK
    $('#btn-absen-tolak').on('click', function () {
        processApproval('Ditolak');
    });

    // RESET MODAL PERSETUJUAN SAAT DITUTUP
    $('#absen-approval-modal').on('hidden.bs.modal', function () {
        approvalAbsenId = null;

        $('#absen-approval-id').val('');
        $('#absen-approval-keterangan').val('');
    });

    // ======================================
    // EDIT PENGAJUAN OLEH KARYAWAN
    // ======================================

    $tableBody.on('click', '.btn-edit', function () {

        if (isHcm) return;

        var id = Number(
            $(this).closest('td').attr('data-absen-id')
        );

        var absen = absenList.find(function (item) {
            return Number(item.id) === id;
        });

        if (!absen || absen.status !== 'Menunggu') return;

        editedAbsenId = id;

        openFormModal('Ubah Absen');

        [
            'jenis',
            'tanggal_mulai',
            'tanggal_selesai',
            'keterangan'
        ].forEach(function (field) {
            $form.find('[name="' + field + '"]')
                .val(absen[field] || '');
        });
    });

    // HAPUS ABSEN
    $tableBody.on('click', '.btn-delete', function () {

        if (isHcm) return;

        var id = Number(
            $(this).closest('td').attr('data-absen-id')
        );

        var absen = absenList.find(function (item) {
            return Number(item.id) === id;
        });

        if (!absen || absen.status !== 'Menunggu') return;

        absenIdToDelete = id;

        $('#absen-delete-name').text(absen.nomor_dokumen);

        deleteModal.show();
    });

    // KONFIRMASI HAPUS
    $('#btn-confirm-delete-absen').on('click', function () {

        if (isHcm || absenIdToDelete === null) return;

        var $button = $(this);

        $button.prop('disabled', true);

        sendRequest(urls.delete, {
            id: absenIdToDelete
        })
        .done(function (response) {

            deleteModal.hide();

            showMessage(
                response.message || 'Data absen berhasil dihapus.',
                'success'
            );

            absenIdToDelete = null;

            loadAbsen();
        })
        .fail(showAjaxError)
        .always(function () {
            $button.prop('disabled', false);
        });
    });

    // LOAD PERTAMA
    loadAbsen();

});
