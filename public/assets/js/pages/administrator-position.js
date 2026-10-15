$(function () {
    var $page = $('#position-page');
    var $form = $('#position-form');

    var formModal = new bootstrap.Modal(
        document.getElementById('position-form-modal')
    );

    var deleteModal = new bootstrap.Modal(
        document.getElementById('position-delete-modal')
    );

    var positionIdToDelete = null;

    // Tabel Position: mengikuti konfigurasi Data Role
    var positionTable = $('#position-table').DataTable({
        ajax: {
            url: $page.data('list-url'),
            type: 'POST'
        },
        columns: [
            {
                data: 'name',
                render: DataTable.render.text()
            },
            {
                data: null,
                orderable: false,
                className: 'text-end',
                render: renderActionButtons
            }
        ]
    });

    // Membuka modal Tambah/Edit
    function openFormModal(title) {
        $form[0].reset();
        $form.find('[name="id"]').val('');
        clearFormErrors($form);

        $('#position-form-title').text(title);

        formModal.show();
    }

    // Tombol Tambah Position
    $('#btn-add-position').on('click', function () {
        openFormModal('Tambah Position');
    });

    // Tombol Edit Position
    $('#position-table').on('click', '.btn-edit', function () {
        var position = positionTable
            .row($(this).closest('tr'))
            .data();

        if (!position) {
            return;
        }

        openFormModal('Ubah Position');

        $form.find('[name="id"]').val(position.id);
        $form.find('[name="name"]').val(position.name);
    });

    // Simpan Position
    $form.on('submit', function (event) {
        event.preventDefault();

        clearFormErrors($form);

        var $submitButton = $form
            .find('[type="submit"]')
            .prop('disabled', true);

        $.post(
            $page.data('save-url'),
            $form.serialize()
        )
            .done(function (response) {
                formModal.hide();

                showMessage(response.message, 'success');

                positionTable.ajax.reload(null, false);
            })
            .fail(function (xhr) {
                showFormErrors(xhr, $form);
            })
            .always(function () {
                $submitButton.prop('disabled', false);
            });
    });

    // Tombol Hapus Position
    $('#position-table').on('click', '.btn-delete', function () {
        var position = positionTable
            .row($(this).closest('tr'))
            .data();

        if (!position) {
            return;
        }

        positionIdToDelete = position.id;

        $('#position-delete-name').text(position.name);

        deleteModal.show();
    });

    // Konfirmasi Hapus Position
    $('#btn-confirm-delete-position').on('click', function () {
        if (positionIdToDelete === null) {
            return;
        }

        var $button = $(this).prop('disabled', true);

        $.post(
            $page.data('delete-url'),
            {
                id: positionIdToDelete
            }
        )
            .done(function (response) {
                deleteModal.hide();

                showMessage(response.message, 'success');

                positionIdToDelete = null;

                positionTable.ajax.reload(null, false);
            })
            .fail(function (xhr) {
                showMessage(readErrorMessage(xhr), 'error');
            })
            .always(function () {
                $button.prop('disabled', false);
            });
    });
});
