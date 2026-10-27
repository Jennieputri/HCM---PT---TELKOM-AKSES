$(function ()
{
    var $page = $('#absen-page');
    var $form = $('#absen-form');
    var $filterPanel = $('#absen-filter-panel');
    var $tableBody = $('#absen-table tbody');
    var formModal = new bootstrap.Modal('#absen-form-modal');
    var deleteModal = new bootstrap.Modal('#absen-delete-modal');
    var absenFieldNames = ['jenis', 'tanggal_mulai', 'tanggal_selesai', 'nomor_dokumen', 'keterangan'];
    var statusTones = { Menunggu: 'warning', Disetujui: 'success', Ditolak: 'danger' };
    var absenList = [];
    var editedAbsenIndex = null;
    var absenIndexToDelete = null;
    var nextDocumentSequence = 1;

    function padNumber(number, length)
    {
        return String(number).padStart(length, '0');
    }

    function createDocumentNumber()
    {
        var today = new Date();
        var datePart = today.getFullYear() + padNumber(today.getMonth() + 1, 2) + padNumber(today.getDate(), 2);

        return 'ABS-' + datePart + '-' + padNumber(nextDocumentSequence, 3);
    }

    function createTextCell(value)
    {
        return $('<td></td>').text(value);
    }

    function createAbsenRow(absen, absenIndex, rowNumber)
    {
        return $('<tr></tr>').append(
            createTextCell(rowNumber),
            createTextCell(absen.karyawan),
            createTextCell(absen.jenis),
            createTextCell(formatDate(absen.tanggal_mulai)),
            createTextCell(formatDate(absen.tanggal_selesai)),
            createTextCell(absen.nomor_dokumen),
            $('<td></td>').html(renderStatusPill(absen.status, statusTones[absen.status])),
            $('<td class="text-end"></td>').html(renderActionButtons()).attr('data-absen-index', absenIndex)
        );
    }

    function containsText(value, keyword)
    {
        return ! keyword || value.toLowerCase().includes(keyword.toLowerCase());
    }

    function matchesFilters(absen, filters)
    {
        return containsText(absen.karyawan, filters.karyawan)
            && containsText(absen.nomor_dokumen, filters.nomor_dokumen)
            && (! filters.jenis || absen.jenis === filters.jenis)
            && (! filters.status || absen.status === filters.status)
            && (! filters.tanggal_mulai || absen.tanggal_mulai >= filters.tanggal_mulai)
            && (! filters.tanggal_selesai || absen.tanggal_selesai <= filters.tanggal_selesai);
    }

    function renderAbsenTable()
    {
        var filters = readFilterValues($filterPanel);
        var rowNumber = 0;

        $tableBody.empty();

        $.each(absenList, function (absenIndex, absen)
        {
            if (matchesFilters(absen, filters))
            {
                rowNumber++;
                $tableBody.append(createAbsenRow(absen, absenIndex, rowNumber));
            }
        });

        if (rowNumber === 0)
        {
            $tableBody.append('<tr><td colspan="8" class="table-empty">Belum ada data absen.</td></tr>');
        }
    }

    function selectedAbsenIndex(button)
    {
        return Number($(button).closest('td').attr('data-absen-index'));
    }

    function openFormModal(title)
    {
        $form[0].reset();
        refreshSelect2($form);
        clearFormErrors($form);

        $('#absen-form-title').text(title);

        formModal.show();
    }

    function readAbsenForm()
    {
        var absen = { karyawan: String($page.data('employee-label')), status: 'Menunggu', created_at: new Date().toISOString() };

        $.each(absenFieldNames, function (index, fieldName)
        {
            absen[fieldName] = $.trim($form.find('[name="' + fieldName + '"]').val());
        });

        return absen;
    }

    function findAbsenErrors(absen)
    {
        var errors = {};

        if (absen.jenis === '')
        {
            errors.jenis = ['Jenis absen wajib dipilih!'];
        }

        if (absen.tanggal_mulai === '')
        {
            errors.tanggal_mulai = ['Tanggal mulai wajib diisi!'];
        }

        if (absen.tanggal_selesai === '')
        {
            errors.tanggal_selesai = ['Tanggal selesai wajib diisi!'];
        }
        else if (absen.tanggal_selesai < absen.tanggal_mulai)
        {
            errors.tanggal_selesai = ['Tanggal selesai tidak boleh sebelum tanggal mulai!'];
        }

        if (absen.keterangan === '')
        {
            errors.keterangan = ['Keterangan wajib diisi!'];
        }

        return errors;
    }

    bindFilterPanel($filterPanel, renderAbsenTable);

    $('#btn-add-absen').on('click', function ()
    {
        editedAbsenIndex = null;
        openFormModal('Tambah Absen');

        $form.find('[name="nomor_dokumen"]').val(createDocumentNumber());
    });

    $tableBody.on('click', '.btn-detail', function ()
    {
        var absen = absenList[selectedAbsenIndex(this)];

        showRecordDetail(absen.nomor_dokumen,
        [
            ['Karyawan', absen.karyawan],
            ['Jenis Absen', absen.jenis],
            ['Tanggal Mulai', formatDate(absen.tanggal_mulai)],
            ['Tanggal Selesai', formatDate(absen.tanggal_selesai)],
            ['Nomor Dokumen', absen.nomor_dokumen],
            ['Keterangan', absen.keterangan],
            ['Status', absen.status],
            ['Diajukan pada', formatDateTime(absen.created_at)]
        ]);
    });

    $tableBody.on('click', '.btn-edit', function ()
    {
        editedAbsenIndex = selectedAbsenIndex(this);
        openFormModal('Ubah Absen');

        $.each(absenFieldNames, function (index, fieldName)
        {
            $form.find('[name="' + fieldName + '"]').val(absenList[editedAbsenIndex][fieldName]);
        });

        refreshSelect2($form);
    });

    $form.on('submit', function (event)
    {
        event.preventDefault();
        clearFormErrors($form);

        var absen = readAbsenForm();
        var errors = findAbsenErrors(absen);

        if (! $.isEmptyObject(errors))
        {
            showFieldErrors(errors, $form);

            return;
        }

        if (editedAbsenIndex === null)
        {
            absenList.push(absen);
            nextDocumentSequence++;
        }
        else
        {
            absen.status = absenList[editedAbsenIndex].status;
            absen.created_at = absenList[editedAbsenIndex].created_at;
            absenList[editedAbsenIndex] = absen;
        }

        formModal.hide();
        renderAbsenTable();
        showMessage('Data absen ditampilkan sementara dan belum tersimpan di database.', 'success');
    });

    $tableBody.on('click', '.btn-delete', function ()
    {
        absenIndexToDelete = selectedAbsenIndex(this);
        $('#absen-delete-name').text(absenList[absenIndexToDelete].nomor_dokumen);

        deleteModal.show();
    });

    $('#btn-confirm-delete-absen').on('click', function ()
    {
        if (absenIndexToDelete === null)
        {
            return;
        }

        absenList.splice(absenIndexToDelete, 1);
        absenIndexToDelete = null;

        deleteModal.hide();
        renderAbsenTable();
    });

    renderAbsenTable();
});
