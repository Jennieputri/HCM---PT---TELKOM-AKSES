$(function ()
{
    var $page = $('#alat-kerja-page');
    var $form = $('#alat-kerja-form');
    var $filterPanel = $('#alat-kerja-filter-panel');
    var formModal = new bootstrap.Modal('#alat-kerja-form-modal');
    var deleteModal = new bootstrap.Modal('#alat-kerja-delete-modal');
    var alatKerjaFieldNames = ['id', 'kode_alat', 'nama_alat', 'tanggal_penyerahan', 'kondisi', 'status'];
    var alatKerjaIdToDelete = null;

    var alatKerjaTable = $('#alat-kerja-table').DataTable(
    {
        ajax: filteredDataTableAjax($page.data('list-url'), $filterPanel),
        columns:
        [
            { data: 'karyawan', render: DataTable.render.text(), defaultContent: '-' },
            { data: 'kode_alat', render: DataTable.render.text() },
            { data: 'nama_alat', render: DataTable.render.text() },
            { data: 'tanggal_penyerahan', render: renderDate },
            { data: 'kondisi', render: renderKondisi },
            { data: 'status', render: renderReturnStatus },
            { data: null, orderable: false, className: 'text-end', render: renderActionButtons }
        ]
    });

    bindFilterPanel($filterPanel, function ()
    {
        alatKerjaTable.ajax.reload();
    });

    function renderKondisi(kondisi)
    {
        return renderStatusPill(kondisi, kondisi === 'Baik' ? 'success' : 'danger');
    }

    function renderReturnStatus(status)
    {
        return renderStatusPill(status, status === 'Sudah Dikembalikan' ? 'info' : 'warning');
    }

    function rowData(button)
    {
        return alatKerjaTable.row($(button).closest('tr')).data();
    }

    function openFormModal(title)
    {
        $form[0].reset();
        refreshSelect2($form);
        $form.find('[name="id"]').val('');
        clearFormErrors($form);

        $('#alat-kerja-form-title').text(title);

        formModal.show();
    }

    $('#btn-add-alat-kerja').on('click', function ()
    {
        openFormModal('Tambah Alat Kerja');
    });

    $('#alat-kerja-table').on('click', '.btn-detail', function ()
    {
        var alatKerja = rowData(this);

        showRecordDetail(alatKerja.kode_alat + ' - ' + alatKerja.nama_alat,
        [
            ['Karyawan', alatKerja.karyawan],
            ['Kode Alat', alatKerja.kode_alat],
            ['Nama Alat', alatKerja.nama_alat],
            ['Tanggal Penyerahan', formatDate(alatKerja.tanggal_penyerahan)],
            ['Kondisi', alatKerja.kondisi],
            ['Status Pengembalian', alatKerja.status]
        ], alatKerja);
    });

    $('#alat-kerja-table').on('click', '.btn-edit', function ()
    {
        var alatKerja = rowData(this);

        if (! alatKerja)
        {
            return;
        }

        openFormModal('Ubah Alat Kerja');

        $.each(alatKerjaFieldNames, function (index, fieldName)
        {
            $form.find('[name="' + fieldName + '"]').val(alatKerja[fieldName]);
        });

        refreshSelect2($form);
    });

    $form.on('submit', function (event)
    {
        event.preventDefault();
        clearFormErrors($form);

        var $submitButton = $form.find('[type="submit"]').prop('disabled', true);

        $.post($page.data('save-url'), $form.serialize())
            .done(function (response)
            {
                formModal.hide();
                showMessage(response.message, 'success');
                alatKerjaTable.ajax.reload(null, false);
            })
            .fail(function (xhr)
            {
                showFormErrors(xhr, $form);
            })
            .always(function ()
            {
                $submitButton.prop('disabled', false);
            });
    });

    $('#alat-kerja-table').on('click', '.btn-delete', function ()
    {
        var alatKerja = rowData(this);

        if (! alatKerja)
        {
            return;
        }

        alatKerjaIdToDelete = alatKerja.id;
        $('#alat-kerja-delete-name').text(alatKerja.nama_alat);

        deleteModal.show();
    });

    $('#btn-confirm-delete-alat-kerja').on('click', function ()
    {
        if (alatKerjaIdToDelete === null)
        {
            return;
        }

        var $button = $(this).prop('disabled', true);

        $.post($page.data('delete-url'), { id: alatKerjaIdToDelete })
            .done(function (response)
            {
                deleteModal.hide();
                showMessage(response.message, 'success');
                alatKerjaIdToDelete = null;
                alatKerjaTable.ajax.reload(null, false);
            })
            .fail(function (xhr)
            {
                showMessage(readErrorMessage(xhr), 'error');
            })
            .always(function ()
            {
                $button.prop('disabled', false);
            });
    });
});
