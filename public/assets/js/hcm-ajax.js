$.ajaxSetup(
{
    headers:
    {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ajaxError(function (event, xhr)
{
    if (xhr.status === 401 || xhr.status === 419)
    {
        window.location.reload();
    }
});

$.extend(true, DataTable.defaults,
{
    serverSide: true,
    processing: true,
    pageLength: 10,
    lengthMenu: [10, 25, 50, 100],
    language:
    {
        search: '',
        searchPlaceholder: 'Cari data...',
        lengthMenu: 'Tampilkan _MENU_ baris',
        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
        infoEmpty: 'Tidak ada data',
        infoFiltered: '(disaring dari _MAX_ data)',
        zeroRecords: 'Data tidak ditemukan',
        emptyTable: 'Belum ada data',
        processing: 'Memuat data...',
        paginate:
        {
            first: '«',
            previous: '‹',
            next: '›',
            last: '»'
        }
    }
});

$.fn.select2.defaults.set('theme', 'bootstrap-5');
$.fn.select2.defaults.set('language', 'id');
$.fn.select2.defaults.set('width', '100%');

var SEARCHABLE_OPTION_THRESHOLD = 8;

function initSelect2($scope)
{
    $scope.find('select.form-select')
        .not('.select2-hidden-accessible')
        .not('[data-select2-manual]')
        .each(function ()
        {
            var $select = $(this);
            var $modal = $select.closest('.modal');
            var hasManyOptions = $select.find('option').length > SEARCHABLE_OPTION_THRESHOLD;

            $select.select2(
            {
                dropdownParent: $modal.length ? $modal : $(document.body),
                minimumResultsForSearch: hasManyOptions ? 0 : Infinity
            });
        });
}

function refreshSelect2($scope)
{
    $scope.find('select.select2-hidden-accessible').trigger('change.select2');
}

$(function ()
{
    initSelect2($(document.body));
});

$(document).on('init.dt', function (event, settings)
{
    $(settings.nTableWrapper).find('.dt-length select').select2(
    {
        width: 'auto',
        dropdownAutoWidth: true,
        minimumResultsForSearch: Infinity
    });
});

var dateFormatter = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
var dateTimeFormatter = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

function renderActionButtons()
{
    return '<div class="table-actions">'
        + '<button type="button" class="btn btn-icon btn-table-action btn-detail" aria-label="Lihat detail">'
        + '<i class="bi bi-eye" aria-hidden="true"></i>'
        + '</button>'
        + '<button type="button" class="btn btn-icon btn-table-action btn-edit" aria-label="Ubah data">'
        + '<i class="bi bi-pencil" aria-hidden="true"></i>'
        + '</button>'
        + '<button type="button" class="btn btn-icon btn-table-action is-danger btn-delete" aria-label="Hapus data">'
        + '<i class="bi bi-trash" aria-hidden="true"></i>'
        + '</button>'
        + '</div>';
}

function renderStatusPill(label, tone)
{
    return $('<span class="status-pill"></span>').addClass('is-' + tone).text(label)[0].outerHTML;
}

function parseDateValue(value)
{
    return new Date(String(value).replace(' ', 'T'));
}

function formatDate(value)
{
    return value ? dateFormatter.format(parseDateValue(value)) : '-';
}

function formatDateTime(value)
{
    return value ? dateTimeFormatter.format(parseDateValue(value)) : '-';
}

function renderDate(value, type)
{
    return type === 'display' ? formatDate(value) : value;
}

function renderDateTime(value, type)
{
    return type === 'display' ? formatDateTime(value) : value;
}

function readFilterValues($filterPanel)
{
    var filters = {};

    $filterPanel.find('[data-filter]').each(function ()
    {
        var value = $(this).val();

        if (value !== null && value !== '')
        {
            filters[$(this).attr('name')] = value;
        }
    });

    return filters;
}

function bindFilterPanel($filterPanel, onFilterChange)
{
    var $activeFilterCount = $('[data-filter-count="' + $filterPanel.attr('id') + '"]');
    var typingTimer = null;

    function applyFilters()
    {
        var activeFilterTotal = Object.keys(readFilterValues($filterPanel)).length;

        $activeFilterCount.text(activeFilterTotal).toggleClass('d-none', activeFilterTotal === 0);
        onFilterChange();
    }

    $filterPanel.on('change', 'select[data-filter], input[type="date"][data-filter]', applyFilters);

    $filterPanel.on('input', 'input[type="search"][data-filter]', function ()
    {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(applyFilters, 400);
    });

    $filterPanel.on('click', '[data-filter-reset]', function ()
    {
        $filterPanel.find('[data-filter]').val('').trigger('change.select2');
        applyFilters();
    });
}

function filteredDataTableAjax(listUrl, $filterPanel)
{
    return {
        url: listUrl,
        type: 'POST',
        data: function (request)
        {
            request.filters = readFilterValues($filterPanel);
        }
    };
}

function appendDetailItems($list, detailItems)
{
    $.each(detailItems, function (index, detailItem)
    {
        var detailValue = detailItem[1];

        $list.append(
            $('<dt></dt>').text(detailItem[0]),
            $('<dd></dd>').text(detailValue === null || detailValue === undefined || detailValue === '' ? '-' : detailValue)
        );
    });
}

function showRecordDetail(title, detailItems, auditRecord)
{
    var $auditSection = $('#record-detail-audit');

    $('#record-detail-title').text(title);
    appendDetailItems($('#record-detail-list').empty(), detailItems);

    $auditSection.toggleClass('d-none', ! auditRecord);

    if (auditRecord)
    {
        appendDetailItems($('#record-detail-audit-list').empty(),
        [
            ['Dibuat oleh', auditRecord.created_by_label],
            ['Dibuat pada', formatDateTime(auditRecord.created_at)],
            ['Diubah oleh', auditRecord.updated_by_label],
            ['Diubah pada', formatDateTime(auditRecord.updated_at)]
        ]);
    }

    bootstrap.Offcanvas.getOrCreateInstance('#record-detail-panel').show();
}

function showMessage(message, type)
{
    var pageStyle = getComputedStyle(document.body);

    Swal.fire(
    {
        icon: type,
        title: type === 'success' ? 'Berhasil' : 'Gagal',
        text: message,
        target: document.querySelector('.modal.show') || 'body',
        confirmButtonText: 'OK',
        confirmButtonColor: pageStyle.getPropertyValue('--bs-primary').trim(),
        background: pageStyle.getPropertyValue('--bs-body-bg').trim(),
        color: pageStyle.getPropertyValue('--bs-body-color').trim()
    });
}

function readErrorMessage(xhr)
{
    if (xhr.responseJSON && xhr.responseJSON.message)
    {
        return xhr.responseJSON.message;
    }

    return 'Terjadi kesalahan. Silakan coba lagi.';
}

function clearFormErrors($form)
{
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.invalid-feedback').text('');
}

function showFieldErrors(errors, $form)
{
    $.each(errors, function (fieldName, messages)
    {
        var $field = $form.find('[name="' + fieldName + '"]');

        $field.addClass('is-invalid');
        $field.closest('.form-field').find('.invalid-feedback').text(messages[0]);
    });

    $form.find('.is-invalid').first().trigger('focus');
}

function showFormErrors(xhr, $form)
{
    if (xhr.status !== 422 || ! xhr.responseJSON.errors)
    {
        showMessage(readErrorMessage(xhr), 'error');

        return;
    }

    showFieldErrors(xhr.responseJSON.errors, $form);
}
