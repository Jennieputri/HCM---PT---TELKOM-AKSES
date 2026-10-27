$(function ()
{
    var $page = $('#sto-page');
    var $form = $('#sto-form');
    var $filterPanel = $('#sto-filter-panel');
    var formModal = new bootstrap.Modal('#sto-form-modal');
    var deleteModal = new bootstrap.Modal('#sto-delete-modal');
    var stoFieldNames = ['id', 'kode_sto', 'nama_sto', 'alamat', 'kecamatan', 'latitude', 'longitude', 'status'];
    var palangkarayaCenter = [-2.21, 113.92];
    var searchZoomLevel = 17;
    var popupLiftPixels = 80;
    var popupOptions = { className: 'sto-popup', minWidth: 220, maxWidth: 280, autoPanPaddingTopLeft: [16, 124], autoPanPaddingBottomRight: [64, 16] };
    var stoIdToDelete = null;
    var draftMarker = null;

    var stoMap = L.map('sto-map', { zoomControl: false, attributionControl: false }).setView(palangkarayaCenter, 11);
    var markerLayer = L.featureGroup().addTo(stoMap);

    L.control.zoom({ position: 'bottomright', zoomInTitle: 'Perbesar', zoomOutTitle: 'Perkecil' }).addTo(stoMap);

    function addElementAsMapControl(element, position)
    {
        var ElementControl = L.Control.extend(
        {
            onAdd: function ()
            {
                L.DomEvent.disableClickPropagation(element);
                L.DomEvent.disableScrollPropagation(element);

                return element;
            }
        });

        new ElementControl({ position: position }).addTo(stoMap);
    }

    addElementAsMapControl(document.getElementById('sto-coordinate-search'), 'topleft');
    addElementAsMapControl(document.querySelector('.sto-layer-toggle'), 'topright');

    var baseLayers =
    {
        map: L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            maxZoom: 19
        }),
        satellite: L.layerGroup(
        [
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            {
                maxNativeZoom: 18,
                maxZoom: 19
            }),
            L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}',
            {
                maxNativeZoom: 18,
                maxZoom: 19
            })
        ])
    };

    var activeBaseLayer = baseLayers.map.addTo(stoMap);

    new ResizeObserver(function ()
    {
        stoMap.invalidateSize();
    }).observe(document.getElementById('sto-map'));

    var stoTable = $('#sto-table').DataTable(
    {
        ajax: filteredDataTableAjax($page.data('list-url'), $filterPanel),
        columns:
        [
            { data: 'kode_sto', render: DataTable.render.text() },
            { data: 'nama_sto', render: DataTable.render.text() },
            { data: 'kecamatan', render: DataTable.render.text(), defaultContent: '-' },
            { data: 'alamat', className: 'cell-wrap', render: DataTable.render.text() },
            { data: 'latitude', render: renderCoordinate },
            { data: 'status', render: renderStatus },
            { data: null, orderable: false, className: 'text-end', render: renderActionButtons }
        ]
    });

    bindFilterPanel($filterPanel, reloadStoData);

    function coordinateLabel(sto)
    {
        return sto.latitude + ', ' + sto.longitude;
    }

    function renderCoordinate(latitude, type, sto)
    {
        return type === 'display' ? $('<span class="text-nowrap"></span>').text(coordinateLabel(sto))[0].outerHTML : latitude;
    }

    function renderStatus(status)
    {
        return renderStatusPill(status, status === 'Aktif' ? 'success' : 'muted');
    }

    function rowData(button)
    {
        return stoTable.row($(button).closest('tr')).data();
    }

    function showStoDetail(sto)
    {
        showRecordDetail(sto.kode_sto + ' - ' + sto.nama_sto,
        [
            ['Kode STO', sto.kode_sto],
            ['Nama STO', sto.nama_sto],
            ['Alamat', sto.alamat],
            ['Kecamatan', sto.kecamatan],
            ['Koordinat', coordinateLabel(sto)],
            ['Status', sto.status],
            ['Dibuat pada', formatDateTime(sto.created_at)],
            ['Diubah pada', formatDateTime(sto.updated_at)]
        ]);
    }

    function isValidCoordinate(latitude, longitude)
    {
        return Number.isFinite(latitude)
            && Number.isFinite(longitude)
            && Math.abs(latitude) <= 90
            && Math.abs(longitude) <= 180;
    }

    function createMarkerIcon(status)
    {
        var pinClass = status === 'Aktif' ? 'sto-marker-pin' : 'sto-marker-pin is-nonaktif';

        return L.divIcon(
        {
            className: '',
            html: '<div class="' + pinClass + '"><span>T</span></div>',
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -30]
        });
    }

    function createPopupButton(label, iconName, variant)
    {
        return $('<button type="button" class="btn btn-popup"></button>')
            .addClass('is-' + variant)
            .append($('<i aria-hidden="true"></i>').addClass('bi bi-' + iconName), $('<span></span>').text(label));
    }

    function createPopupMeta(iconName, text)
    {
        return $('<div class="sto-popup-meta"></div>')
            .append($('<i aria-hidden="true"></i>').addClass('bi bi-' + iconName), $('<span></span>').text(text));
    }

    function createPopupContent(sto)
    {
        var $header = $('<div class="sto-popup-header"></div>')
            .append($('<span class="sto-popup-eyebrow"></span>').text(sto.kode_sto), renderStatus(sto.status));
        var $title = $('<strong class="sto-popup-title"></strong>').text(sto.nama_sto);
        var $address = createPopupMeta('geo-alt', sto.alamat);
        var $coordinate = createPopupMeta('crosshair', coordinateLabel(sto));
        var $detailButton = createPopupButton('Detail', 'eye', 'outline');
        var $editButton = createPopupButton('Ubah', 'pencil', 'primary');

        $detailButton.on('click', function ()
        {
            stoMap.closePopup();
            showStoDetail(sto);
        });

        $editButton.on('click', function ()
        {
            stoMap.closePopup();
            openEditForm(sto);
        });

        var $actions = $('<div class="sto-popup-actions"></div>').append($detailButton, $editButton);

        return $('<div class="sto-popup-body"></div>').append($header, $title, $address, $coordinate, $actions)[0];
    }

    function switchBaseLayer(layerName)
    {
        stoMap.removeLayer(activeBaseLayer);
        activeBaseLayer = baseLayers[layerName].addTo(stoMap);

        $('[data-map-layer]').each(function ()
        {
            var isActive = $(this).data('map-layer') === layerName;

            $(this).toggleClass('active', isActive).attr('aria-pressed', String(isActive));
        });
    }

    function parseCoordinate(text)
    {
        var coordinateMatch = $.trim(text).match(/^(-?\d+(?:\.\d+)?)\s*[,\s]\s*(-?\d+(?:\.\d+)?)$/);

        if (! coordinateMatch)
        {
            return null;
        }

        var latitude = Number(coordinateMatch[1]);
        var longitude = Number(coordinateMatch[2]);

        return isValidCoordinate(latitude, longitude) ? { latitude: latitude, longitude: longitude } : null;
    }

    function showCoordinateError(message)
    {
        $('#sto-coordinate-input').toggleClass('is-invalid', message !== '');
        $('#sto-coordinate-error').text(message).toggleClass('d-none', message === '');
    }

    function createDraftIcon()
    {
        return L.divIcon(
        {
            className: '',
            html: '<div class="sto-draft-pin"></div>',
            iconSize: [22, 22],
            iconAnchor: [11, 11],
            popupAnchor: [0, -12]
        });
    }

    function openAddFormAt(position)
    {
        stoMap.closePopup();
        openFormModal('Tambah STO');

        $form.find('[name="latitude"]').val(position.lat.toFixed(7));
        $form.find('[name="longitude"]').val(position.lng.toFixed(7));
    }

    function createDraftPopupContent(position)
    {
        var $header = $('<div class="sto-popup-header"></div>').append('<span class="sto-popup-eyebrow is-draft">Titik baru</span>');
        var $coordinate = createPopupMeta('crosshair', position.lat.toFixed(7) + ', ' + position.lng.toFixed(7));
        var $hint = $('<p class="sto-popup-hint">Geser penanda untuk menyesuaikan posisi.</p>');
        var $addButton = createPopupButton('Tambah STO di sini', 'plus-lg', 'primary');

        $addButton.on('click', function ()
        {
            openAddFormAt(draftMarker.getLatLng());
        });

        return $('<div class="sto-popup-body"></div>').append($header, $coordinate, $hint, $('<div class="sto-popup-actions"></div>').append($addButton))[0];
    }

    function placeDraftMarker(position)
    {
        if (draftMarker === null)
        {
            draftMarker = L.marker(position, { icon: createDraftIcon(), draggable: true, zIndexOffset: 1000 }).addTo(stoMap);

            draftMarker.on('dragend', function ()
            {
                draftMarker.setPopupContent(createDraftPopupContent(draftMarker.getLatLng())).openPopup();
            });
        }

        draftMarker.setLatLng(position);
        draftMarker.unbindPopup().bindPopup(createDraftPopupContent(L.latLng(position)), popupOptions);

        return draftMarker;
    }

    function removeDraftMarker()
    {
        if (draftMarker !== null)
        {
            draftMarker.remove();
            draftMarker = null;
        }
    }

    function loadMarkers()
    {
        $.post($page.data('markers-url'), { filters: readFilterValues($filterPanel) })
            .done(function (response)
            {
                markerLayer.clearLayers();

                $.each(response.data, function (index, sto)
                {
                    var latitude = Number(sto.latitude);
                    var longitude = Number(sto.longitude);

                    if (! isValidCoordinate(latitude, longitude))
                    {
                        return;
                    }

                    L.marker([latitude, longitude], { icon: createMarkerIcon(sto.status) })
                        .bindPopup(createPopupContent(sto), popupOptions)
                        .addTo(markerLayer);
                });

                if (markerLayer.getLayers().length > 0)
                {
                    stoMap.fitBounds(markerLayer.getBounds(), { padding: [30, 30], maxZoom: 14 });
                }
            })
            .fail(function (xhr)
            {
                showMessage(readErrorMessage(xhr), 'error');
            });
    }

    function reloadStoData()
    {
        stoTable.ajax.reload(null, false);
        loadMarkers();
    }

    function openFormModal(title)
    {
        $form[0].reset();
        refreshSelect2($form);
        $form.find('[name="id"]').val('');
        clearFormErrors($form);

        $('#sto-form-title').text(title);

        formModal.show();
    }

    function openEditForm(sto)
    {
        openFormModal('Ubah STO');

        $.each(stoFieldNames, function (index, fieldName)
        {
            $form.find('[name="' + fieldName + '"]').val(sto[fieldName]);
        });

        refreshSelect2($form);
    }

    stoMap.on('click', function (event)
    {
        placeDraftMarker(event.latlng).openPopup();
    });

    $('[data-map-layer]').on('click', function ()
    {
        switchBaseLayer($(this).data('map-layer'));
    });

    $('#sto-coordinate-search').on('submit', function (event)
    {
        event.preventDefault();

        var coordinate = parseCoordinate($('#sto-coordinate-input').val());

        if (coordinate === null)
        {
            showCoordinateError('Format salah. Isi latitude (-90 s/d 90) dan longitude (-180 s/d 180), contoh: -2.2096, 113.9135');

            return;
        }

        showCoordinateError('');

        var searchedMarker = placeDraftMarker([coordinate.latitude, coordinate.longitude]);

        searchedMarker.getPopup().options.autoPan = false;

        stoMap.once('moveend', function ()
        {
            searchedMarker.openPopup();
        });

        var markerPoint = stoMap.project([coordinate.latitude, coordinate.longitude], searchZoomLevel);
        var centerAboveMarker = stoMap.unproject(markerPoint.subtract([0, popupLiftPixels]), searchZoomLevel);

        stoMap.flyTo(centerAboveMarker, searchZoomLevel);
    });

    $('#sto-coordinate-input').on('input', function ()
    {
        showCoordinateError('');
    });

    $('#btn-add-sto').on('click', function ()
    {
        openFormModal('Tambah STO');
    });

    $('#sto-table').on('click', '.btn-detail', function ()
    {
        showStoDetail(rowData(this));
    });

    $('#sto-table').on('click', '.btn-edit', function ()
    {
        openEditForm(rowData(this));
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
                removeDraftMarker();
                reloadStoData();
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

    $('#sto-table').on('click', '.btn-delete', function ()
    {
        var sto = rowData(this);

        stoIdToDelete = sto.id;
        $('#sto-delete-name').text(sto.kode_sto + ' - ' + sto.nama_sto);

        deleteModal.show();
    });

    $('#btn-confirm-delete-sto').on('click', function ()
    {
        if (stoIdToDelete === null)
        {
            return;
        }

        var $button = $(this).prop('disabled', true);

        $.post($page.data('delete-url'), { id: stoIdToDelete })
            .done(function (response)
            {
                deleteModal.hide();
                showMessage(response.message, 'success');
                stoIdToDelete = null;
                reloadStoData();
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

    loadMarkers();
});
