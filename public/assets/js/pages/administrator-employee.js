$(function ()
{
    var $page = $('#employee-page');
    var $form = $('#employee-form');
    var formModal = new bootstrap.Modal('#employee-form-modal');
    var deleteModal = new bootstrap.Modal('#employee-delete-modal');
    var $roleSelect = $('#employee-role');
    var $statusSelect = $('#employee-status');
    var employeeFieldNames = ['id', 'nik', 'name', 'number_phone', 'email', 'address', 'status'];
    var employeeIdToDelete = null;

    var employeeTable = $('#employee-table').DataTable(
    {
        ajax:
        {
            url: $page.data('list-url'),
            type: 'POST'
        },
        columns:
        [
            { data: 'nik', type: 'string', render: DataTable.render.text() },
            { data: 'name', render: DataTable.render.text() },
            { data: 'email', render: DataTable.render.text(), defaultContent: '-' },
            { data: 'number_phone', render: DataTable.render.text(), defaultContent: '-' },
            { data: 'role_name', render: DataTable.render.text(), defaultContent: '-' },
            { data: 'status', render: renderStatusBadge },
            { data: null, orderable: false, className: 'text-end', render: renderActionButtons }
        ]
    });

    $roleSelect.select2(
    {
        placeholder: 'Pilih role',
        dropdownParent: $('#employee-form-modal'),
        ajax:
        {
            url: $page.data('role-options-url'),
            type: 'POST',
            delay: 300,
            data: function (params)
            {
                return { search: params.term, page: params.page || 1 };
            }
        }
    });

    $statusSelect.select2(
    {
        dropdownParent: $('#employee-form-modal'),
        minimumResultsForSearch: Infinity
    });

    function renderStatusBadge(status)
    {
        if (Number(status) === 1)
        {
            return '<span class="badge text-bg-success">Aktif</span>';
        }

        return '<span class="badge text-bg-secondary">Nonaktif</span>';
    }

    function openFormModal(title, isNewEmployee)
    {
        $form[0].reset();
        $form.find('[name="id"]').val('');
        $roleSelect.empty().trigger('change');
        $statusSelect.trigger('change');
        clearFormErrors($form);

        $('#employee-form-title').text(title);
        $('#employee-password-help').text(isNewEmployee ? 'Minimal 8 karakter.' : 'Kosongkan jika password tidak diganti.');

        formModal.show();
    }

    $('#btn-add-employee').on('click', function ()
    {
        openFormModal('Tambah Karyawan', true);
    });

    $('#employee-table').on('click', '.btn-edit', function ()
    {
        var employee = employeeTable.row($(this).closest('tr')).data();

        openFormModal('Ubah Karyawan', false);

        $.each(employeeFieldNames, function (index, fieldName)
        {
            $form.find('[name="' + fieldName + '"]').val(employee[fieldName]);
        });

        if (employee.role_id)
        {
            $roleSelect.append(new Option(employee.role_name, employee.role_id, true, true));
        }

        $roleSelect.trigger('change');
        $statusSelect.trigger('change');
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
                employeeTable.ajax.reload(null, false);
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

    $('#employee-table').on('click', '.btn-delete', function ()
    {
        var employee = employeeTable.row($(this).closest('tr')).data();

        employeeIdToDelete = employee.id;
        $('#employee-delete-name').text(employee.name);

        deleteModal.show();
    });

    $('#btn-confirm-delete-employee').on('click', function ()
    {
        deleteModal.hide();

        $.post($page.data('delete-url'), { id: employeeIdToDelete })
            .done(function (response)
            {
                showMessage(response.message, 'success');
                employeeTable.ajax.reload(null, false);
            })
            .fail(function (xhr)
            {
                showMessage(readErrorMessage(xhr), 'error');
            });
    });
});
