@extends('layouts')

@section('title', 'Data Absen')

@section('content')

<div class="row">
    <div class="col-12">

        <div class="card border-0 shadow-sm"
             style="border-radius: 16px; overflow: hidden;">

            <div class="card-header bg-white py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold">Daftar Data Absen</h4>

                    <button type="button"
                            class="btn btn-danger"
                            onclick="formAbsenBaru()">
                        <i class="bi bi-plus-lg me-1"></i>
                        Tambah Absen
                    </button>
                </div>
            </div>
            
            <div class="card-body">

                {{-- FILTER --}}
                <div class="row g-3 mb-4">

                    <div class="col-md-5">
                        <input type="search"
                               id="cariAbsen"
                               class="form-control"
                               placeholder="Cari NIK karyawan...">
                    </div>

                    <div class="col-md-3">
                        <select id="filterJenis" class="form-select">
                            <option value="">Semua Jenis</option>
                            <option value="Izin">Izin</option>
                            <option value="Sakit">Sakit</option>
                            <option value="Cuti">Cuti</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <input type="text"
                               class="form-control"
                               placeholder="Filter tanggal mulai - selesai"
                               disabled>
                    </div>

                </div>

                {{-- TABEL --}}
                <div class="table-responsive">
                    <table class="table table-hover align-middle"
                           id="tabelAbsen">

                        <thead class="table-light">
                            <tr>
                                <th>No.</th>
                                <th>NIK</th>
                                <th>Jenis</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Keterangan</th>
                                <th>No. Dokumen</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td colspan="9"
                                    class="text-center text-muted py-4">
                                    Belum ada data absen.
                                </td>
                            </tr>
                        </tbody>

                    </table>
                </div>

                <div class="text-muted small">
                    Data izin, sakit, dan cuti karyawan
                </div>

            </div>
        </div>
    </div>
</div>


{{-- MODAL TAMBAH / EDIT ABSEN --}}
<div class="modal fade"
     id="modalAbsen"
     tabindex="-1"
     aria-labelledby="judulFormAbsen"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0"
             style="border-radius: 16px;">

            <div class="modal-header bg-white">
                <h5 class="modal-title fw-bold text-danger"
                    id="judulFormAbsen">
                    Tambah Data Absen
                </h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Tutup"></button>
            </div>

            <div class="modal-body">

                <form id="formAbsen" onsubmit="simpanAbsen(event)">

                    <div class="row g-3">

                        {{-- JENIS ABSEN --}}
                        <div class="col-md-6">
                            <label for="absenJenis" class="form-label">
                                Jenis Absen
                            </label>

                            <select name="jenis"
                                    id="absenJenis"
                                    class="form-select"
                                    required>
                                <option value="">Pilih Jenis</option>
                                <option value="Izin">Izin</option>
                                <option value="Sakit">Sakit</option>
                                <option value="Cuti">Cuti</option>
                            </select>
                        </div>

                        {{-- TANGGAL MULAI --}}
                        <div class="col-md-6">
                            <label for="tanggalMulai" class="form-label">
                                Tanggal Mulai
                            </label>

                            <input type="date"
                                name="tanggal_mulai"
                                id="tanggalMulai"
                                class="form-control"
                                required>
                        </div>

                        {{-- TANGGAL SELESAI --}}
                        <div class="col-md-6">
                            <label for="tanggalSelesai" class="form-label">
                                Tanggal Selesai
                            </label>

                            <input type="date"
                                name="tanggal_selesai"
                                id="tanggalSelesai"
                                class="form-control"
                                required>
                        </div>

                        {{-- NOMOR DOKUMEN --}}
                        <div class="col-md-6">
                            <label for="nomorDokumen" class="form-label">
                                Nomor Dokumen
                            </label>

                            <input type="text"
                                name="nomor_dokumen"
                                id="nomorDokumen"
                                class="form-control"
                                readonly
                                required>
                        </div>

                        {{-- KETERANGAN --}}
                        <div class="col-12">
                            <label for="absenKeterangan" class="form-label">
                                Keterangan
                            </label>

                            <textarea name="keterangan"
                                    id="absenKeterangan"
                                    class="form-control"
                                    rows="3"
                                    placeholder="Masukkan keterangan izin, sakit, atau cuti"
                                    required></textarea>
                        </div>

                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <button type="button"
                                class="btn btn-outline-secondary"
                                data-bs-dismiss="modal">
                            Batal
                        </button>

                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-save me-1"></i>
                            Simpan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

@endsection


@push('scripts')
<script>
    let daftarAbsen = [];
    let nomorEditAbsen = null;
    let modalAbsen;

    // Nomor sementara untuk tampilan browser.
    // Belum menjamin keunikan antar pengguna/perangkat.
    let urutanDokumen = 1;

    document.addEventListener('DOMContentLoaded', function () {
        modalAbsen = new bootstrap.Modal(
            document.getElementById('modalAbsen')
        );

        document.getElementById('cariAbsen')
            .addEventListener('input', tampilkanAbsen);

        document.getElementById('filterJenis')
            .addEventListener('change', tampilkanAbsen);
    });

    function buatNomorDokumen() {
        const tanggal = new Date();
        const tahun = tanggal.getFullYear();
        const bulan = String(tanggal.getMonth() + 1).padStart(2, '0');
        const hari = String(tanggal.getDate()).padStart(2, '0');
        const urutan = String(urutanDokumen).padStart(3, '0');

        return `ABS-${tahun}${bulan}${hari}-${urutan}`;
    }

    function formAbsenBaru() {
        resetFormAbsen();

        nomorEditAbsen = null;

        document.getElementById('judulFormAbsen').textContent =
            'Tambah Data Absen';

        document.getElementById('nomorDokumen').value =
            buatNomorDokumen();

        modalAbsen.show();
    }

    function resetFormAbsen() {
        document.getElementById('formAbsen').reset();

        nomorEditAbsen = null;

        document.getElementById('judulFormAbsen').textContent =
            'Tambah Data Absen';
    }

    function simpanAbsen(event) {
        event.preventDefault();

        const form = document.getElementById('formAbsen');
        const data = Object.fromEntries(
            new FormData(form).entries()
        );

        data.nik = @json(auth()->user()->nik ?? '');

        data.status = 'Menunggu';

        if (data.tanggal_selesai < data.tanggal_mulai) {
            alert('Tanggal selesai tidak boleh sebelum tanggal mulai.');
            return;
        }

        if (nomorEditAbsen === null) {
            daftarAbsen.push(data);
            urutanDokumen++;
        } else {
            daftarAbsen[nomorEditAbsen] = data;
        }

        tampilkanAbsen();
        modalAbsen.hide();
        resetFormAbsen();

        alert(
            'Data berhasil ditampilkan sementara. ' +
            'Data belum tersimpan di database Laravel.'
        );
    }

    function tampilkanAbsen() {
        const tbody = document.querySelector('#tabelAbsen tbody');
        tbody.innerHTML = '';

        const pencarian = document.getElementById('cariAbsen')
            .value.toLowerCase();

        const jenisFilter = document.getElementById('filterJenis').value;

        const hasil = daftarAbsen
            .map((data, index) => ({ ...data, index }))
            .filter(data =>
                String(data.nik ?? '').toLowerCase().includes(pencarian) &&
                (!jenisFilter || data.jenis === jenisFilter)
            );

        if (hasil.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        Belum ada data absen.
                        Silakan tambahkan data.
                    </td>
                </tr>`;
            return;
        }

        hasil.forEach((data, nomor) => {
            const statusClass = data.status === 'Disetujui'
                ? 'success'
                : data.status === 'Ditolak'
                    ? 'danger'
                    : 'secondary';

            const tr = document.createElement('tr');

            [
                nomor + 1,
                data.nik,
                data.jenis,
                data.tanggal_mulai,
                data.tanggal_selesai,
                data.keterangan,
                data.nomor_dokumen
            ].forEach(nilai => {
                const td = document.createElement('td');
                td.textContent = nilai;
                tr.appendChild(td);
            });

            const tdStatus = document.createElement('td');
            const badge = document.createElement('span');

            badge.className = `badge text-bg-${statusClass}`;
            badge.textContent = data.status;

            tdStatus.appendChild(badge);
            tr.appendChild(tdStatus);

            const tdAksi = document.createElement('td');
            tdAksi.className = 'text-nowrap';

            const tombolEdit = document.createElement('button');
            tombolEdit.type = 'button';
            tombolEdit.className = 'btn btn-sm btn-danger me-1';
            tombolEdit.innerHTML = '<i class="bi bi-pencil"></i>';
            tombolEdit.setAttribute('aria-label', 'Edit data');
            tombolEdit.addEventListener(
                'click',
                () => editAbsen(data.index)
            );

            const tombolHapus = document.createElement('button');
            tombolHapus.type = 'button';
            tombolHapus.className = 'btn btn-sm btn-outline-danger';
            tombolHapus.innerHTML = '<i class="bi bi-trash"></i>';
            tombolHapus.setAttribute('aria-label', 'Hapus data');
            tombolHapus.addEventListener(
                'click',
                () => hapusAbsen(data.index)
            );

            tdAksi.append(tombolEdit, tombolHapus);
            tr.appendChild(tdAksi);
            tbody.appendChild(tr);
        });
    }

    function editAbsen(index) {
        const data = daftarAbsen[index];

        nomorEditAbsen = index;

        document.getElementById('absenKaryawan').value = data.nik;
        document.getElementById('absenJenis').value = data.jenis;
        document.getElementById('tanggalMulai').value =
            data.tanggal_mulai;
        document.getElementById('tanggalSelesai').value =
            data.tanggal_selesai;
        document.getElementById('nomorDokumen').value =
            data.nomor_dokumen;
        document.getElementById('absenStatus').value = data.status;
        document.getElementById('absenKeterangan').value =
            data.keterangan;

        document.getElementById('judulFormAbsen').textContent =
            'Edit Data Absen';

        modalAbsen.show();
    }

    function hapusAbsen(index) {
        if (confirm('Yakin ingin menghapus data absen ini?')) {
            daftarAbsen.splice(index, 1);
            tampilkanAbsen();
        }
    }
</script>
@endpush
