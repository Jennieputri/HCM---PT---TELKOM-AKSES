$(function ()
{
    var $page = $('#approval-page');
    var $filterPanel = $('#approval-filter-panel');
    var confirmModal = new bootstrap.Modal('#approval-confirm-modal');
    var $confirmButton = $('#btn-confirm-approval');
    var STATUS_PENDING = 2;
    var pendingDecision = null;

    var decisionTypes =
    {
        approve:
        {
            title: 'Setujui Akun',
            message: 'Setujui pendaftaran {name}? Akun akan aktif dan bisa login.',
            buttonLabel: 'Setujui',
            buttonClass: 'btn-success',
            url: $page.data('approve-url')
        },
        reject:
        {
            title: 'Tolak Pendaftaran',
            message: 'Tolak pendaftaran {name}? Akun tidak akan bisa login.',
            buttonLabel: 'Tolak',
            buttonClass: 'btn-danger',
            url: $page.data('reject-url')
        }
    };

    var approvalTable = $('#approval-table').DataTable(
    {
        ajax: filteredDataTableAjax($page.data('list-url'), $filterPanel),
        order: [[1, 'desc']],
        columns:
        [
            { data: 'employee_label', render: DataTable.render.text() },
            { data: 'created_at', render: renderDateTime },
            { data: 'status', render: renderStatus },
            { data: null, orderable: false, className: 'text-end', render: renderApprovalButtons }
        ]
    });

    bindFilterPanel($filterPanel, function ()
    {
        approvalTable.ajax.reload();
    });

    function isPending(status)
    {
        return Number(status) === STATUS_PENDING;
    }

    function statusLabel(status)
    {
        return isPending(status) ? 'Menunggu' : 'Ditolak';
    }

    function renderStatus(status)
    {
        return renderStatusPill(statusLabel(status), isPending(status) ? 'warning' : 'danger');
    }

    function renderApprovalButtons(data, type, registrant)
    {
        var rejectButton = isPending(registrant.status)
            ? '<button type="button" class="btn btn-icon btn-table-action is-danger btn-reject" aria-label="Tolak pendaftaran">'
                + '<i class="bi bi-x-lg" aria-hidden="true"></i>'
                + '</button>'
            : '';

        return '<div class="table-actions">'
            + '<button type="button" class="btn btn-icon btn-table-action btn-detail" aria-label="Lihat detail">'
            + '<i class="bi bi-eye" aria-hidden="true"></i>'
            + '</button>'
            + '<button type="button" class="btn btn-icon btn-table-action is-success btn-approve" aria-label="Setujui akun">'
            + '<i class="bi bi-check-lg" aria-hidden="true"></i>'
            + '</button>'
            + rejectButton
            + '</div>';
    }

    function rowData(button)
    {
        return approvalTable.row($(button).closest('tr')).data();
    }

    function openDecisionModal(decisionName, registrant)
    {
        var decision = decisionTypes[decisionName];

        pendingDecision = { url: decision.url, id: registrant.id };

        $('#approval-confirm-title').text(decision.title);
        $('#approval-confirm-message').text(decision.message.replace('{name}', registrant.employee_label));
        $confirmButton
            .text(decision.buttonLabel)
            .removeClass('btn-success btn-danger')
            .addClass(decision.buttonClass);

        confirmModal.show();
    }

    $('#approval-table').on('click', '.btn-detail', function ()
    {
        var registrant = rowData(this);

        showRecordDetail(registrant.employee_label,
        [
            ['NIK', registrant.nik],
            ['Nama', registrant.name],
            ['Role', registrant.role_name],
            ['Tanggal Daftar', formatDateTime(registrant.created_at)],
            ['Status', statusLabel(registrant.status)]
        ], registrant);
    });

    $('#approval-table').on('click', '.btn-approve', function ()
    {
        openDecisionModal('approve', rowData(this));
    });

    $('#approval-table').on('click', '.btn-reject', function ()
    {
        openDecisionModal('reject', rowData(this));
    });

    $confirmButton.on('click', function ()
    {
        if (pendingDecision === null)
        {
            return;
        }

        $confirmButton.prop('disabled', true);

        $.post(pendingDecision.url, { id: pendingDecision.id })
            .done(function (response)
            {
                confirmModal.hide();
                showMessage(response.message, 'success');
                pendingDecision = null;
                approvalTable.ajax.reload(null, false);
            })
            .fail(function (xhr)
            {
                showMessage(readErrorMessage(xhr), 'error');
            })
            .always(function ()
            {
                $confirmButton.prop('disabled', false);
            });
    });
});
