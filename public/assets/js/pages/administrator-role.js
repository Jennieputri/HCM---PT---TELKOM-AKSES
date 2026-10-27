$(function ()
{
    var $page = $('#role-page');
    var $form = $('#role-form');
    var $filterPanel = $('#role-filter-panel');
    var formModal = new bootstrap.Modal('#role-form-modal');
    var deleteModal = new bootstrap.Modal('#role-delete-modal');
    var roleIdToDelete = null;

    var roleTable = $('#role-table').DataTable(
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
        roleTable.ajax.reload();
    });

    function rowData(button)
    {
        return roleTable.row($(button).closest('tr')).data();
    }

    function openFormModal(title)
    {
        $form[0].reset();
        $form.find('[name="id"]').val('');
        clearFormErrors($form);

        $('#role-form-title').text(title);

        formModal.show();
    }

    $('#btn-add-role').on('click', function ()
    {
        openFormModal('Tambah Role');
    });

    $('#role-table').on('click', '.btn-detail', function ()
    {
        var role = rowData(this);

        showRecordDetail(role.name, [['Nama Role', role.name]], role);
    });

    $('#role-table').on('click', '.btn-edit', function ()
    {
        var role = rowData(this);

        openFormModal('Ubah Role');

        $form.find('[name="id"]').val(role.id);
        $form.find('[name="name"]').val(role.name);
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
                roleTable.ajax.reload(null, false);
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

    $('#role-table').on('click', '.btn-delete', function ()
    {
        var role = rowData(this);

        roleIdToDelete = role.id;
        $('#role-delete-name').text(role.name);

        deleteModal.show();
    });

    $('#btn-confirm-delete-role').on('click', function ()
    {
        if (roleIdToDelete === null)
        {
            return;
        }

        var $button = $(this).prop('disabled', true);

        $.post($page.data('delete-url'), { id: roleIdToDelete })
            .done(function (response)
            {
                deleteModal.hide();
                showMessage(response.message, 'success');
                roleIdToDelete = null;
                roleTable.ajax.reload(null, false);
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
