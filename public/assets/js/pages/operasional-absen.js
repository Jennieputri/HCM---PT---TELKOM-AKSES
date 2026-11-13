$(function ()
{
    var $page = $('#absen-page');
    var $form = $('#absen-form');
    var $filterPanel = $('#absen-filter-panel');
    var isHcm = String($page.data('is-hcm')) === '1';
    var formModal = new bootstrap.Modal('#absen-form-modal');
    var deleteModal = new bootstrap.Modal('#absen-delete-modal');
    var approvalModal = new bootstrap.Modal('#absen-approval-modal');
    var statusTones = { Menunggu: 'warning', Disetujui: 'success', Ditolak: 'danger' };
    var jenisSections = { Cuti: '#absen-form-cuti', SPPD: '#absen-form-sppd', Sakit: '#absen-form-sakit' };
    var sppdEmployeeFields = ['nik', 'nama', 'position', 'kota_asal', 'kota_tujuan'];
    var formFieldsByJenis =
    {
        Cuti:
        {
            jenis_cuti: 'jenis_cuti',
            band: 'band',
            jabatan: 'jabatan',
            unit_kerja: 'unit_kerja',
            lama_cuti: 'lama_cuti',
            cuti_tanggal_mulai: 'tanggal_mulai',
            cuti_tanggal_selesai: 'tanggal_selesai',
            alamat_selama_cuti: 'alamat_selama_cuti',
            telepon_cuti: 'telepon_cuti',
            perjalanan_berangkat_mulai: 'perjalanan_berangkat_mulai',
            perjalanan_berangkat_selesai: 'perjalanan_berangkat_selesai',
            perjalanan_pulang_mulai: 'perjalanan_pulang_mulai',
            perjalanan_pulang_selesai: 'perjalanan_pulang_selesai',
            alasan_cuti: 'keterangan'
        },
        SPPD:
        {
            tanggal_surat: 'tanggal_surat',
            perihal_pekerjaan: 'keterangan',
            lokasi_pekerjaan: 'lokasi_pekerjaan',
            sppd_tanggal_mulai: 'tanggal_mulai',
            sppd_tanggal_selesai: 'tanggal_selesai'
        },
        Sakit:
        {
            tanggal_mulai: 'tanggal_mulai',
            tanggal_selesai: 'tanggal_selesai',
            keterangan: 'keterangan'
        }
    };
    var editedAbsenId = null;
    var absenIdToDelete = null;
    var approvalAbsenId = null;

    var absenTable = $('#absen-table').DataTable(
    {
        ajax: filteredDataTableAjax($page.data('list-url'), $filterPanel),
        order: [[0, 'desc']],
        columns:
        [
            { data: null, orderable: false, render: renderRowNumber },
            { data: 'nik_karyawan', render: DataTable.render.text() },
            { data: 'nama_karyawan', render: DataTable.render.text(), defaultContent: '-' },
            { data: 'jenis', render: DataTable.render.text() },
            { data: 'tanggal_mulai', render: renderDate },
            { data: 'tanggal_selesai', render: renderDate },
            { data: 'nomor_dokumen', render: DataTable.render.text() },
            { data: 'status', render: renderAbsenStatus },
            { data: null, orderable: false, className: 'text-end', render: renderAbsenActions }
        ]
    });

    bindFilterPanel($filterPanel, function ()
    {
        absenTable.ajax.reload();
    });

    function renderRowNumber(data, type, row, meta)
    {
        return meta.settings._iDisplayStart + meta.row + 1;
    }

    function renderAbsenStatus(status)
    {
        return renderStatusPill(status, statusTones[status] || 'secondary');
    }

    function actionButton(buttonClass, iconName, label)
    {
        return $('<button type="button" class="btn btn-icon btn-table-action"></button>')
            .addClass(buttonClass)
            .attr('aria-label', label)
            .append($('<i aria-hidden="true"></i>').addClass('bi ' + iconName));
    }

    function renderAbsenActions(data, type, absen)
    {
        var $actions = $('<div class="table-actions"></div>');
        var isPending = absen.status === 'Menunggu';

        $actions.append(actionButton('btn-detail', 'bi-eye', 'Lihat detail'));

        if (absen.status === 'Disetujui')
        {
            $actions.append(
                $('<a class="btn btn-icon btn-table-action btn-download-pdf" download></a>')
                    .attr('href', '/operasional/absen/' + encodeURIComponent(absen.id) + '/download')
                    .attr('aria-label', 'Unduh PDF')
                    .append('<i class="bi bi-file-earmark-pdf" aria-hidden="true"></i>')
            );
        }

        if (isPending && isHcm)
        {
            $actions.append(actionButton('btn-approval', 'bi-pencil-square', 'Proses persetujuan'));
        }

        if (isPending && ! isHcm)
        {
            $actions.append(
                actionButton('btn-edit', 'bi-pencil', 'Ubah data'),
                actionButton('btn-delete is-danger', 'bi-trash', 'Hapus data')
            );
        }

        return $actions[0].outerHTML;
    }

    function rowData(button)
    {
        return absenTable.row($(button).closest('tr')).data();
    }

    function readAbsenError(xhr)
    {
        if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors)
        {
            return Object.values(xhr.responseJSON.errors).flat().join('\n');
        }

        return readErrorMessage(xhr);
    }

    function updateCutiDuration()
    {
        var startDate = Date.parse($('#cuti-mulai').val());
        var endDate = Date.parse($('#cuti-selesai').val());
        var isValidRange = ! Number.isNaN(startDate) && ! Number.isNaN(endDate) && endDate >= startDate;

        $('#cuti-lama').val(isValidRange ? Math.round((endDate - startDate) / 86400000) + 1 : '');
    }

    function showJenisStep()
    {
        $('#cuti-jenis-wrapper').addClass('d-none');
        $('#absen-step-pilih, #absen-footer-pilih').removeClass('d-none');
        $('#absen-step-form, #absen-footer-form').addClass('d-none');
        $('#absen-form-cuti, #absen-form-sppd, #absen-form-sakit').addClass('d-none');
    }

    function showJenisForm(jenis)
    {
        $('#cuti-jenis-wrapper').toggleClass('d-none', jenis !== 'Cuti');
        $('#absen-step-pilih, #absen-footer-pilih').addClass('d-none');
        $('#absen-step-form, #absen-footer-form').removeClass('d-none');
        $('#absen-form-cuti, #absen-form-sppd, #absen-form-sakit').addClass('d-none');
        $(jenisSections[jenis]).removeClass('d-none');
        $('#absen-form-title').text('Form Pengajuan ' + jenis);
    }

    function fillSppdEmployees(assignedEmployees)
    {
        $.each(assignedEmployees, function (rowIndex, employee)
        {
            $.each(sppdEmployeeFields, function (index, fieldName)
            {
                $form.find('[name="sppd_karyawan[' + rowIndex + '][' + fieldName + ']"]').val(employee[fieldName] || '');
            });
        });
    }

    function openFormModal(title, nomorDokumen)
    {
        $form[0].reset();
        clearFormErrors($form);
        refreshFormControls($form);
        showJenisStep();

        $('#absen-form-title').text(title);
        $('#absen-nomor-dokumen').val(nomorDokumen);

        formModal.show();
    }

    function selectedDateRange(jenis)
    {
        var dateFieldsByJenis =
        {
            Cuti: ['cuti_tanggal_mulai', 'cuti_tanggal_selesai'],
            SPPD: ['sppd_tanggal_mulai', 'sppd_tanggal_selesai'],
            Sakit: ['tanggal_mulai', 'tanggal_selesai']
        };

        return dateFieldsByJenis[jenis].map(function (fieldName)
        {
            return $form.find('[name="' + fieldName + '"]').val();
        });
    }

    function invalidDateMessage(jenis)
    {
        var dateRange = selectedDateRange(jenis);

        if (! dateRange[0] || ! dateRange[1])
        {
            return 'Tanggal mulai dan tanggal selesai wajib diisi.';
        }

        if (dateRange[1] < dateRange[0])
        {
            return 'Tanggal selesai tidak boleh sebelum tanggal mulai.';
        }

        return null;
    }

    $('#btn-add-absen').on('click', function ()
    {
        var $button = $(this).prop('disabled', true);

        editedAbsenId = null;

        $.post($page.data('nomor-dokumen-url'))
            .done(function (response)
            {
                openFormModal('Tambah Absen', response.nomor_dokumen);
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

    $('#btn-absen-selanjutnya').on('click', function ()
    {
        var $jenisSelect = $('#absen-jenis');
        var jenis = $jenisSelect.val();

        $jenisSelect.toggleClass('is-invalid', ! jenis);

        if (! jenis)
        {
            $jenisSelect.trigger('focus');
            showMessage('Silakan pilih jenis absen terlebih dahulu.', 'error');

            return;
        }

        showJenisForm(jenis);
    });

    $form.on('change', '#cuti-mulai, #cuti-selesai', updateCutiDuration);

    $form.on('submit', function (event)
    {
        event.preventDefault();
        clearFormErrors($form);

        var jenis = $('#absen-jenis').val();
        var dateError = jenis ? invalidDateMessage(jenis) : 'Silakan pilih jenis absen terlebih dahulu.';

        if (dateError)
        {
            showMessage(dateError, 'error');

            return;
        }

        var formData = new FormData($form[0]);
        var $submitButton = $('#btn-absen-ajukan').prop('disabled', true);

        if (editedAbsenId !== null)
        {
            formData.append('id', editedAbsenId);
        }

        $.ajax(
        {
            url: $page.data('save-url'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false
        })
            .done(function (response)
            {
                formModal.hide();
                editedAbsenId = null;
                showMessage(response.message, 'success');
                absenTable.ajax.reload(null, false);
            })
            .fail(function (xhr)
            {
                showMessage(readAbsenError(xhr), 'error');
            })
            .always(function ()
            {
                $submitButton.prop('disabled', false);
            });
    });

    $('#absen-table').on('click', '.btn-detail', function ()
    {
        var absen = rowData(this);
        var detailItems =
        [
            ['Karyawan', absen.nama_karyawan + ' (' + absen.nik_karyawan + ')'],
            ['Jenis Absen', absen.jenis],
            ['Tanggal Mulai', formatDate(absen.tanggal_mulai)],
            ['Tanggal Selesai', formatDate(absen.tanggal_selesai)],
            ['Nomor Dokumen', absen.nomor_dokumen],
            ['Keterangan Pengajuan', absen.keterangan],
            ['Status', absen.status]
        ];

        if (absen.jenis === 'Cuti')
        {
            detailItems.splice(2, 0,
                ['Jenis Cuti', absen.jenis_cuti],
                ['Lama Cuti', absen.lama_cuti ? absen.lama_cuti + ' hari' : null],
                ['Band', absen.band],
                ['Jabatan', absen.jabatan],
                ['Unit Kerja', absen.unit_kerja]
            );
        }

        if (absen.jenis === 'SPPD')
        {
            var assignedNames = JSON.parse(absen.sppd_karyawan || '[]').map(function (employee)
            {
                return employee.nama;
            });

            detailItems.splice(2, 0,
                ['Tanggal Surat', formatDate(absen.tanggal_surat)],
                ['Lokasi Pekerjaan', absen.lokasi_pekerjaan],
                ['Karyawan Ditugaskan', assignedNames.join(', ')]
            );
        }

        if (absen.keterangan_admin)
        {
            detailItems.push(['Keterangan HCM', absen.keterangan_admin]);
        }

        detailItems.push(['Diajukan pada', formatDateTime(absen.created_at)]);

        showRecordDetail(absen.nomor_dokumen, detailItems);
    });

    $('#absen-table').on('click', '.btn-edit', function ()
    {
        var absen = rowData(this);

        if (! absen || absen.status !== 'Menunggu')
        {
            return;
        }

        editedAbsenId = absen.id;
        openFormModal('Ubah Absen', absen.nomor_dokumen);

        $('#absen-jenis').val(absen.jenis);

        $.each(formFieldsByJenis[absen.jenis], function (formFieldName, columnName)
        {
            $form.find('[name="' + formFieldName + '"]').val(absen[columnName] || '');
        });

        if (absen.jenis === 'SPPD')
        {
            fillSppdEmployees(JSON.parse(absen.sppd_karyawan || '[]'));
        }

        refreshFormControls($form);
        showJenisForm(absen.jenis);
    });

    $('#absen-table').on('click', '.btn-delete', function ()
    {
        var absen = rowData(this);

        if (! absen || absen.status !== 'Menunggu')
        {
            return;
        }

        absenIdToDelete = absen.id;
        $('#absen-delete-name').text(absen.nomor_dokumen);

        deleteModal.show();
    });

    $('#btn-confirm-delete-absen').on('click', function ()
    {
        if (absenIdToDelete === null)
        {
            return;
        }

        var $button = $(this).prop('disabled', true);

        $.post($page.data('delete-url'), { id: absenIdToDelete })
            .done(function (response)
            {
                deleteModal.hide();
                absenIdToDelete = null;
                showMessage(response.message, 'success');
                absenTable.ajax.reload(null, false);
            })
            .fail(function (xhr)
            {
                showMessage(readAbsenError(xhr), 'error');
            })
            .always(function ()
            {
                $button.prop('disabled', false);
            });
    });

    $('#absen-table').on('click', '.btn-approval', function ()
    {
        var absen = rowData(this);

        if (! absen || absen.status !== 'Menunggu')
        {
            return;
        }

        approvalAbsenId = absen.id;
        $('#absen-approval-keterangan').val('');

        approvalModal.show();
    });

    function processApproval(decisionUrl)
    {
        var $keterangan = $('#absen-approval-keterangan');
        var keteranganAdmin = String($keterangan.val() || '').trim();

        if (approvalAbsenId === null)
        {
            return;
        }

        if (! keteranganAdmin)
        {
            $keterangan.trigger('focus');
            showMessage('Keterangan wajib diisi sebelum memberikan keputusan.', 'error');

            return;
        }

        var $decisionButtons = $('#btn-absen-setujui, #btn-absen-tolak').prop('disabled', true);

        $.post(decisionUrl, { id: approvalAbsenId, keterangan_admin: keteranganAdmin })
            .done(function (response)
            {
                approvalModal.hide();
                showMessage(response.message, 'success');
                absenTable.ajax.reload(null, false);
            })
            .fail(function (xhr)
            {
                showMessage(readAbsenError(xhr), 'error');
            })
            .always(function ()
            {
                $decisionButtons.prop('disabled', false);
            });
    }

    $('#btn-absen-setujui').on('click', function ()
    {
        processApproval($page.data('approve-url'));
    });

    $('#btn-absen-tolak').on('click', function ()
    {
        processApproval($page.data('reject-url'));
    });

    $('#absen-approval-modal').on('hidden.bs.modal', function ()
    {
        approvalAbsenId = null;
        $('#absen-approval-keterangan').val('');
    });
});
