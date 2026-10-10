$(function ()
{
    var $page = $('#role-page');
    var $form = $('#role-form');
    var formModal = new bootstrap.Modal('#role-form-modal');
    var deleteModal = new bootstrap.Modal('#role-delete-modal');
    var roleIdToDelete = null;

    var roleTable = $('#role-table').DataTable(
    {
        ajax:
        {
            url: $page.data('list-url'),
            type: 'POST'
        },
        columns:
        [
            { data: 'name', render: DataTable.render.text() },
            { data: null, orderable: false, className: 'text-end', render: renderActionButtons }
        ]
    });

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

    $('#role-table').on('click', '.btn-edit', function ()
    {
        var role = roleTable.row($(this).closest('tr')).data();

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
        var role = roleTable.row($(this).closest('tr')).data();

        roleIdToDelete = role.id;
        $('#role-delete-name').text(role.name);

        deleteModal.show();
    });

    $('#btn-confirm-delete-role').on('click', function ()
    {
        deleteModal.hide();

        $.post($page.data('delete-url'), { id: roleIdToDelete })
            .done(function (response)
            {
                showMessage(response.message, 'success');
                roleTable.ajax.reload(null, false);
            })
            .fail(function (xhr)
            {
                showMessage(readErrorMessage(xhr), 'error');
            });
    });
});
