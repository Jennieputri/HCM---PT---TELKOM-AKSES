$(function ()
{
    var $page = $('#position-page');
    var $form = $('#position-form');
    var $filterPanel = $('#position-filter-panel');
    var formModal = new bootstrap.Modal('#position-form-modal');
    var deleteModal = new bootstrap.Modal('#position-delete-modal');
    var positionIdToDelete = null;

    var positionTable = $('#position-table').DataTable(
    {
        ajax: filteredDataTableAjax($page.data('list-url'), $filterPanel),
        columns:
        [
            { data: 'name', render: DataTable.render.text() },
            { data: 'created_by_label', render: DataTable.render.text(), defaultContent: '-' },
            { data: 'updated_at', render: renderDateTime },
            { data: null, orderable: false, className: 'text-end', render: renderActionButtons }
        ]
    });

    bindFilterPanel($filterPanel, function ()
    {
        positionTable.ajax.reload();
    });

    function rowData(button)
    {
        return positionTable.row($(button).closest('tr')).data();
    }

    function openFormModal(title)
    {
        $form[0].reset();
        $form.find('[name="id"]').val('');
        clearFormErrors($form);

        $('#position-form-title').text(title);

        formModal.show();
    }

    $('#btn-add-position').on('click', function ()
    {
        openFormModal('Tambah Position');
    });

    $('#position-table').on('click', '.btn-detail', function ()
    {
        var position = rowData(this);

        showRecordDetail(position.name, [['Nama Position', position.name]], position);
    });

    $('#position-table').on('click', '.btn-edit', function ()
    {
        var position = rowData(this);

        openFormModal('Ubah Position');

        $form.find('[name="id"]').val(position.id);
        $form.find('[name="name"]').val(position.name);
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
                positionTable.ajax.reload(null, false);
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

    $('#position-table').on('click', '.btn-delete', function ()
    {
        var position = rowData(this);

        positionIdToDelete = position.id;
        $('#position-delete-name').text(position.name);

        deleteModal.show();
    });

    $('#btn-confirm-delete-position').on('click', function ()
    {
        if (positionIdToDelete === null)
        {
            return;
        }

        var $button = $(this).prop('disabled', true);

        $.post($page.data('delete-url'), { id: positionIdToDelete })
            .done(function (response)
            {
                deleteModal.hide();
                showMessage(response.message, 'success');
                positionIdToDelete = null;
                positionTable.ajax.reload(null, false);
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
