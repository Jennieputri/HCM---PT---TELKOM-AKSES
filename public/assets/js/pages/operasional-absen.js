$(function () {
    var $page = $('#absen-page');
    var $form = $('#absen-form');
    var $filterPanel = $('#absen-filter-panel');
    var $tableBody = $('#absen-table tbody');

    var formModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('absen-form-modal')
    );
    var deleteModal = bootstrap.Modal.getOrCreateInstance(
        document.getElementById('absen-delete-modal')
    );

    var absenList = [];
    var editedAbsenId = null;
    var absenIdToDelete = null;

    var statusTones = {
        Menunggu: 'warning',
        Disetujui: 'success',
        Ditolak: 'danger'
    };

    var urls = {
        list: $page.data('ajax-list') || '/ajax/operasional/absen/list',
        save: $page.data('ajax-save') || '/ajax/operasional/absen/save',
        delete: $page.data('ajax-delete') || '/ajax/operasional/absen/delete'
    };

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

    function showAjaxError(xhr) {
        console.error('Error Data Absen:', xhr.status, xhr.responseJSON || xhr.responseText);

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

    function formatDate(value) {
        if (!value) return '-';

        var parts = String(value).substring(0, 10).split('-');

        if (parts.length !== 3) return value;

        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }

    function createTextCell(value) {
        return $('<td></td>').text(value == null ? '-' : value);
    }

    function renderAbsenRow(absen, rowNumber) {
        var $row = $('<tr></tr>');

        $row.append(
            createTextCell(rowNumber),
            createTextCell(absen.karyawan),
            createTextCell(absen.jenis),
            createTextCell(formatDate(absen.tanggal_mulai)),
            createTextCell(formatDate(absen.tanggal_selesai)),
            createTextCell(absen.nomor_dokumen)
        );

        $row.append(
            $('<td></td>').html(
                renderStatusPill(
                    absen.status,
                    statusTones[absen.status] || 'secondary'
                )
            ),
            $('<td class="text-end"></td>')
                .html(renderActionButtons())
                .attr('data-absen-id', absen.id)
        );

        return $row;
    }

    function matchesFilters(absen, filters) {
        function contains(value, keyword) {
            return !keyword ||
                String(value || '').toLowerCase().includes(
                    String(keyword).toLowerCase()
                );
        }

        return contains(absen.karyawan, filters.karyawan)
            && contains(absen.nomor_dokumen, filters.nomor_dokumen)
            && (!filters.jenis || absen.jenis === filters.jenis)
            && (!filters.status || absen.status === filters.status)
            && (!filters.tanggal_mulai ||
                absen.tanggal_mulai >= filters.tanggal_mulai)
            && (!filters.tanggal_selesai ||
                absen.tanggal_selesai <= filters.tanggal_selesai);
    }

    function renderAbsenTable() {
        var filters = readFilterValues($filterPanel);
        var rowNumber = 0;

        $tableBody.empty();

        $.each(absenList, function (_, absen) {
            if (matchesFilters(absen, filters)) {
                rowNumber++;
                $tableBody.append(renderAbsenRow(absen, rowNumber));
            }
        });

        if (rowNumber === 0) {
            $tableBody.append(
                '<tr><td colspan="8" class="table-empty">' +
                'Belum ada data absen.</td></tr>'
            );
        }
    }

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
            $tableBody.html(
                '<tr><td colspan="8" class="table-empty">' +
                'Gagal mengambil data dari database.</td></tr>'
            );
        });
    }

    function openFormModal(title) {
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

            $form.find('[name="nomor_dokumen"]')
                .val(nomorDokumen);
        }

        formModal.show();
    }

    bindFilterPanel($filterPanel, renderAbsenTable);

    $('#btn-add-absen').on('click', function () {
        editedAbsenId = null;
        openFormModal('Tambah Absen');
    });

    $form.on('submit', function (event) {
        event.preventDefault();
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

    $tableBody.on('click', '.btn-detail', function () {
        var id = Number($(this).closest('td').attr('data-absen-id'));
        var absen = absenList.find(function (item) {
            return Number(item.id) === id;
        });

        if (!absen) return;

        showRecordDetail(absen.nomor_dokumen, [
            ['Karyawan', absen.karyawan],
            ['Jenis Absen', absen.jenis],
            ['Tanggal Mulai', formatDate(absen.tanggal_mulai)],
            ['Tanggal Selesai', formatDate(absen.tanggal_selesai)],
            ['Nomor Dokumen', absen.nomor_dokumen],
            ['Keterangan', absen.keterangan],
            ['Status', absen.status],
            ['Diajukan pada', absen.created_at || '-']
        ]);
    });

    $tableBody.on('click', '.btn-edit', function () {
        var id = Number($(this).closest('td').attr('data-absen-id'));
        var absen = absenList.find(function (item) {
            return Number(item.id) === id;
        });

        if (!absen) return;

        editedAbsenId = id;
        openFormModal('Ubah Absen');

        ['jenis', 'tanggal_mulai', 'tanggal_selesai', 'keterangan']
            .forEach(function (field) {
                $form.find('[name="' + field + '"]').val(absen[field] || '');
            });
    });

    $tableBody.on('click', '.btn-delete', function () {
        absenIdToDelete = Number(
            $(this).closest('td').attr('data-absen-id')
        );

        var absen = absenList.find(function (item) {
            return Number(item.id) === absenIdToDelete;
        });

        if (!absen) return;

        $('#absen-delete-name').text(absen.nomor_dokumen);
        deleteModal.show();
    });

    $('#btn-confirm-delete-absen').on('click', function () {
        if (absenIdToDelete === null) return;

        sendRequest(urls.delete, { id: absenIdToDelete })
            .done(function (response) {
                deleteModal.hide();
                showMessage(
                    response.message || 'Data absen berhasil dihapus.',
                    'success'
                );
                absenIdToDelete = null;
                loadAbsen();
            })
            .fail(showAjaxError);
    });

    loadAbsen();
});
