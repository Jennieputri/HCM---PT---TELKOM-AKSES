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
        search: 'Cari:',
        lengthMenu: 'Tampilkan _MENU_ data',
        info: 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
        infoEmpty: 'Tidak ada data',
        infoFiltered: '(disaring dari _MAX_ data)',
        zeroRecords: 'Data tidak ditemukan',
        emptyTable: 'Belum ada data',
        processing: 'Memuat data...'
    }
});

$.fn.select2.defaults.set('theme', 'bootstrap-5');
$.fn.select2.defaults.set('language', 'id');
$.fn.select2.defaults.set('width', '100%');

function renderActionButtons()
{
    return '<div class="btn-group">'
        + '<button type="button" class="btn btn-outline-secondary btn-icon btn-edit" aria-label="Ubah data">'
        + '<i class="bi bi-pencil" aria-hidden="true"></i>'
        + '</button>'
        + '<button type="button" class="btn btn-outline-danger btn-icon btn-delete" aria-label="Hapus data">'
        + '<i class="bi bi-trash" aria-hidden="true"></i>'
        + '</button>'
        + '</div>';
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

function showFormErrors(xhr, $form)
{
    if (xhr.status !== 422 || ! xhr.responseJSON.errors)
    {
        showMessage(readErrorMessage(xhr), 'error');

        return;
    }

    $.each(xhr.responseJSON.errors, function (fieldName, messages)
    {
        var $field = $form.find('[name="' + fieldName + '"]');

        $field.addClass('is-invalid');
        $field.closest('.form-field').find('.invalid-feedback').text(messages[0]);
    });

    $form.find('.is-invalid').first().trigger('focus');
}
