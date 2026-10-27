$(function ()
{
    var $page = $('#employee-page');
    var $form = $('#employee-form');
    var $filterPanel = $('#employee-filter-panel');
    var formModal = new bootstrap.Modal('#employee-form-modal');
    var deleteModal = new bootstrap.Modal('#employee-delete-modal');
    var $roleSelect = $('#employee-role');
    var $statusSelect = $('#employee-status');
    var employeeFieldNames = ['id', 'nik', 'name', 'number_phone', 'email', 'address', 'status'];
    var employeeIdToDelete = null;

    var employeeStatuses =
    {
        0: { label: 'Nonaktif', tone: 'muted' },
        1: { label: 'Aktif', tone: 'success' },
        2: { label: 'Menunggu Persetujuan', tone: 'warning' },
        3: { label: 'Ditolak', tone: 'danger' }
    };

    var employeeTable = $('#employee-table').DataTable(
    {
        ajax: filteredDataTableAjax($page.data('list-url'), $filterPanel),
        columns:
        [
            { data: 'employee_label', render: DataTable.render.text() },
            { data: 'email', render: DataTable.render.text(), defaultContent: '-' },
            { data: 'number_phone', render: DataTable.render.text(), defaultContent: '-' },
            { data: 'role_name', render: DataTable.render.text(), defaultContent: '-' },
            { data: 'status', render: renderStatus },
            { data: null, orderable: false, className: 'text-end', render: renderActionButtons }
        ]
    });

    function roleOptionsAjax()
    {
        return {
            url: $page.data('role-options-url'),
            type: 'POST',
            delay: 300,
            data: function (params)
            {
                return { search: params.term, page: params.page || 1 };
            }
        };
    }

    $roleSelect.select2(
    {
        placeholder: 'Pilih role',
        dropdownParent: $('#employee-form-modal'),
        ajax: roleOptionsAjax()
    });

    $('#employee-filter-role').select2(
    {
        placeholder: 'Semua role',
        allowClear: true,
        ajax: roleOptionsAjax()
    });

    bindFilterPanel($filterPanel, function ()
    {
        employeeTable.ajax.reload();
    });

    function employeeStatus(status)
    {
        return employeeStatuses[Number(status)] || employeeStatuses[0];
    }

    function statusLabel(status)
    {
        return employeeStatus(status).label;
    }

    function renderStatus(status)
    {
        return renderStatusPill(statusLabel(status), employeeStatus(status).tone);
    }

    function rowData(button)
    {
        return employeeTable.row($(button).closest('tr')).data();
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

    $('#employee-table').on('click', '.btn-detail', function ()
    {
        var employee = rowData(this);

        showRecordDetail(employee.employee_label,
        [
            ['NIK', employee.nik],
            ['Nama', employee.name],
            ['Email', employee.email],
            ['No. HP', employee.number_phone],
            ['Alamat', employee.address],
            ['Role', employee.role_name],
            ['Status', statusLabel(employee.status)]
        ], employee);
    });

    $('#employee-table').on('click', '.btn-edit', function ()
    {
        var employee = rowData(this);

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
        var employee = rowData(this);

        employeeIdToDelete = employee.id;
        $('#employee-delete-name').text(employee.employee_label);

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
