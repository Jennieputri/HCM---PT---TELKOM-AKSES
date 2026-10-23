@extends('layouts')

@section('title', 'Data STO Map')

@section('content')
<div class="container-fluid">

    {{-- Card Peta STO --}}
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h4 class="fw-bold mb-1">Data STO Map</h4>
                <small class="text-muted">Kelola lokasi STO Telkom Akses Palangkaraya</small>
            </div>

            <button type="button" class="btn btn-danger" onclick="bukaFormSTO()">
                <i class="fas fa-plus me-1"></i> Tambah STO
            </button>
        </div>

        <div class="card-body">
            <div id="stoMap"
                style="width: 100%; height: 350px; border-radius: 12px; overflow: hidden; background: #e8eee8;">
            </div>
        </div>
    </div>

    {{-- Card Tabel STO --}}
    <div class="card border-0 shadow-sm" style="border-radius: 14px;">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Daftar Data STO</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Kode</th>
                            <th>Nama STO</th>
                            <th>Alamat</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabelSTO">
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Memuat data...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>


<div class="modal fade" id="modalSTO" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0" style="border-radius: 14px;">
            <form id="formSTO">
                @csrf
                <input type="hidden" id="sto_id" name="id">

                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="judulModalSTO">Tambah Data STO</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Kode STO</label>
                            <input type="text" name="kode_sto" id="kode_sto" class="form-control" maxlength="20" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama STO</label>
                            <input type="text" name="nama_sto" id="nama_sto" class="form-control" maxlength="100" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Alamat</label>
                            <input type="text" name="alamat" id="alamat_sto" class="form-control" maxlength="255" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kecamatan</label>
                            <input type="text" name="kecamatan" id="kecamatan_sto" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Latitude</label>
                            <input type="number" name="latitude" id="latitude_sto" class="form-control" step="any" min="-90" max="90" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Longitude</label>
                            <input type="number" name="longitude" id="longitude_sto" class="form-control" step="any" min="-180" max="180" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select name="status" id="status_sto" class="form-select" required>
                                <option value="Aktif">Aktif</option>
                                <option value="Nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3 mb-0">
                        Klik lokasi pada peta untuk mengisi latitude dan longitude.
                    </div>
                    <div id="pesanSTO" class="text-danger mt-2"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger" id="btnSimpanSTO">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('#formSTO input[name="_token"]').value;

    const modalElement = document.getElementById('modalSTO');
    const modalSTO = new bootstrap.Modal(modalElement);
    const tabel = document.getElementById('tabelSTO');
    const form = document.getElementById('formSTO');
    let daftarSTO = [];
    let markerSTO = [];

    const map = L.map('stoMap', {
        zoomControl: true
    }).setView([-2.25, 113.92], 11);

    const layerPeta = L.tileLayer(
        'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19
        }
    );

    layerPeta.addTo(map);

    map.whenReady(function () {
        setTimeout(function () {
            map.invalidateSize(true);
        }, 500);
    });

    layerPeta.on('tileerror', function (event) {
        console.error('Gagal memuat ubin peta:', event);
    });

    map.on('click', function (e) {
        document.getElementById('latitude_sto').value = e.latlng.lat.toFixed(7);
        document.getElementById('longitude_sto').value = e.latlng.lng.toFixed(7);
    });

    function aman(teks) {
        const el = document.createElement('span');
        el.textContent = teks ?? '';
        return el.innerHTML;
    }

    
    function gambarMarker() {
        markerSTO.forEach(marker => map.removeLayer(marker));
        markerSTO = [];

        daftarSTO.forEach(sto => {
            const lat = Number(sto.latitude);
            const lng = Number(sto.longitude);

            if (
                !Number.isFinite(lat) ||
                !Number.isFinite(lng) ||
                lat < -90 || lat > 90 ||
                lng < -180 || lng > 180
            ) {
                return;
            }

            const ikonSTO = L.divIcon({
                className: 'sto-marker-wrapper',
                html: `
                    <div style="
                        width: 34px;
                        height: 34px;
                        background: #d71920;
                        border: 3px solid white;
                        border-radius: 50% 50% 50% 0;
                        transform: rotate(-45deg);
                        box-shadow: 0 2px 7px rgba(0,0,0,.35);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    ">
                        <span style="
                            color: white;
                            font-size: 15px;
                            font-weight: bold;
                            transform: rotate(45deg);
                        ">T</span>
                    </div>
                `,
                iconSize: [34, 34],
                iconAnchor: [17, 34],
                popupAnchor: [0, -32]
            });

            const marker = L.marker([lat, lng], {
                icon: ikonSTO
            }).addTo(map);

            marker.bindPopup(
                '<b>' + aman(sto.kode_sto) + ' - ' +
                aman(sto.nama_sto) + '</b><br>' +
                aman(sto.alamat) + '<br>' +
                'Status: ' + aman(sto.status) +
                '<br><button class="btn btn-sm btn-danger mt-2" ' +
                'onclick="editSTO(' + Number(sto.id) + ')">' +
                'Edit STO</button>'
            );

            markerSTO.push(marker);
        });

        if (markerSTO.length > 0) {
            const grupMarker = L.featureGroup(markerSTO);
            map.fitBounds(grupMarker.getBounds(), {
                padding: [30, 30],
                maxZoom: 14
            });
        }
    }
    
async function muatSTO() {
    tabel.innerHTML =
        '<tr><td colspan="8" class="text-center">Memuat data...</td></tr>';

    try {
        const response = await fetch("{{ route('ajax.sto.list') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf
            },
            body: JSON.stringify({})
        });

        // Baca respons sebagai teks agar error HTML pun bisa diketahui
        const teks = await response.text();

        console.log('Status HTTP STO:', response.status);
        console.log('Respons server STO:', teks);

        let hasil;

        try {
            hasil = JSON.parse(teks);
        } catch {
            throw new Error(
                'Server tidak mengirim JSON. Status HTTP: ' +
                response.status +
                '. Periksa Console browser.'
            );
        }

        if (!response.ok) {
            throw new Error(
                hasil.message ||
                'Gagal mengambil data STO. Status HTTP: ' +
                response.status
            );
        }

        daftarSTO = hasil.data || [];
        tabel.innerHTML = '';

        if (!daftarSTO.length) {
            tabel.innerHTML =
                '<tr><td colspan="8" class="text-center text-muted">' +
                'Belum ada data STO.</td></tr>';
        }

        daftarSTO.forEach((sto, i) => {
            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td>${i + 1}</td>
                <td><span class="fw-bold">${aman(sto.kode_sto)}</span></td>
                <td>${aman(sto.nama_sto)}</td>
                <td>${aman(sto.alamat)}</td>
                <td>${aman(sto.latitude)}</td>
                <td>${aman(sto.longitude)}</td>
                <td>
                    <span class="badge ${
                        sto.status === 'Aktif'
                            ? 'bg-success'
                            : 'bg-secondary'
                    }">${aman(sto.status)}</span>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-warning"
                        onclick="editSTO(${Number(sto.id)})">Edit</button>
                    <button type="button"
                        class="btn btn-sm btn-outline-danger"
                        onclick="hapusSTO(${Number(sto.id)})">Hapus</button>
                </td>`;

            tabel.appendChild(tr);
        });

        gambarMarker();

    } catch (error) {
        console.error('Error memuat STO:', error);

        tabel.innerHTML =
            '<tr><td colspan="8" class="text-center text-danger">' +
            aman(error.message) +
            '</td></tr>';
    }
}


    window.bukaFormSTO = function () {
        form.reset();
        document.getElementById('sto_id').value = '';
        document.getElementById('judulModalSTO').textContent = 'Tambah Data STO';
        document.getElementById('pesanSTO').textContent = '';
        modalSTO.show();
    };

    window.editSTO = function (id) {
        const sto = daftarSTO.find(item => Number(item.id) === Number(id));
        if (!sto) return;

        form.reset();
        document.getElementById('sto_id').value = sto.id;
        document.getElementById('kode_sto').value = sto.kode_sto;
        document.getElementById('nama_sto').value = sto.nama_sto;
        document.getElementById('alamat_sto').value = sto.alamat;
        document.getElementById('kecamatan_sto').value = sto.kecamatan || '';
        document.getElementById('latitude_sto').value = sto.latitude;
        document.getElementById('longitude_sto').value = sto.longitude;
        document.getElementById('status_sto').value = sto.status;
        document.getElementById('judulModalSTO').textContent = 'Edit Data STO';
        document.getElementById('pesanSTO').textContent = '';
        modalSTO.show();
    };

    window.hapusSTO = async function (id) {
        const sto = daftarSTO.find(item => Number(item.id) === Number(id));
        if (!sto || !confirm('Yakin ingin menghapus STO ' + sto.kode_sto + '?')) return;

        try {
            const response = await fetch("{{ route('ajax.sto.delete') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify({ id: id })
            });

            const hasil = await response.json();
            if (!response.ok) throw new Error(hasil.message || 'Gagal menghapus data STO.');

            await muatSTO();
            alert(hasil.message || 'Data STO berhasil dihapus.');
        } catch (error) {
            alert(error.message);
        }
    };

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const tombol = document.getElementById('btnSimpanSTO');
        tombol.disabled = true;
        tombol.textContent = 'Menyimpan...';
        document.getElementById('pesanSTO').textContent = '';

        try {
            const response = await fetch("{{ route('ajax.sto.save') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: new FormData(form)
            });

            const hasil = await response.json();
            if (!response.ok) {
                const detail = hasil.errors
                    ? Object.values(hasil.errors).flat().join(' ')
                    : (hasil.message || 'Gagal menyimpan data STO.');
                throw new Error(detail);
            }

            modalSTO.hide();
            await muatSTO();
            alert(hasil.message || 'Data STO berhasil disimpan.');
        } catch (error) {
            document.getElementById('pesanSTO').textContent = error.message;
        } finally {
            tombol.disabled = false;
            tombol.textContent = 'Simpan';
        }
    });

    muatSTO();
});
</script>
@endpush