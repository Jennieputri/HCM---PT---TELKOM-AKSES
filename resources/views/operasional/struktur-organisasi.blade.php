struktur organisasi blade php 

@extends('layouts')

@section('title', 'Struktur Organisasi')

@section('content')

@php
    // Menghilangkan jabatan yang diawali kata Teknisi.
    // Sekaligus menyembunyikan jabatan dengan level TEKNISI.
    $dataTampil = $dataStruktur->filter(function ($item) {
        $jabatan = trim($item->nama_jabatan ?? '');
        $level = trim($item->level_jabatan ?? '');

        return stripos($jabatan, 'Teknisi') !== 0
            && strcasecmp($level, 'TEKNISI') !== 0;
    })->values();

    // Pencarian berdasarkan nama jabatan.
    $cariJabatan = function ($kataKunci) use ($dataTampil) {
        return $dataTampil->filter(function ($item) use ($kataKunci) {
            return stripos($item->nama_jabatan ?? '', $kataKunci) !== false;
        })->values();
    };

    // Jabatan utama.
    $pm = $dataTampil->filter(function ($item) {
        return preg_match(
            '/\bPM\b|Project Manager/i',
            $item->nama_jabatan ?? ''
        );
    })->values();

    $mgr = $dataTampil->filter(function ($item) {
        return preg_match(
            '/Mgr Branch Palangkaraya|Manager Branch Palangkaraya|Branch Manager Palangkaraya/i',
            $item->nama_jabatan ?? ''
        );
    })->values();

    // Tim di bawah PM.
    $drafter = $cariJabatan('Drafter');
    $surveyor = $cariJabatan('Surveyor');

    // Tim di bawah Mgr Branch.
    $officer1 = $cariJabatan('Officer 1');
    $officer3 = $cariJabatan('Officer 3');
    $korlap = $cariJabatan('Korlap');
    $staff = $cariJabatan('Staff');

    $timPM = [
        ['nama' => 'Drafter', 'anggota' => $drafter],
        ['nama' => 'Surveyor', 'anggota' => $surveyor],
    ];

    $timBranch = [
        ['nama' => 'Officer 1', 'anggota' => $officer1],
        ['nama' => 'Officer 3', 'anggota' => $officer3],
        ['nama' => 'Korlap', 'anggota' => $korlap],
        ['nama' => 'Staff', 'anggota' => $staff],
    ];
@endphp

<style>
    .struktur-wrapper {
        background: #fff;
        padding: 24px;
        border-radius: 16px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .07);
        overflow-x: auto;
    }

    .struktur-header {
        background: #b5121b;
        color: #fff;
        padding: 22px 24px;
        border-radius: 12px;
        margin-bottom: 25px;
    }

    .struktur-header p {
        color: #ffe5e5;
    }

    .struktur-search {
        max-width: 450px;
        margin: 0 0 35px;
    }

    .bagan-organisasi {
        min-width: 850px;
        padding: 24px 18px 35px;
        border-radius: 18px;

        background-color: #fcebed;
        background-image:
            radial-gradient(
                circle at 0% 0%,
                rgba(181, 18, 27, 0.16),
                transparent 55%
            ),
            radial-gradient(
                circle at 100% 100%,
                rgba(181, 18, 27, 0.14),
                transparent 55%
            ),
            linear-gradient(
                135deg,
                #fff1f2 0%,
                #fbdfe2 35%,
                #f8e3e5 65%,
                #fff0f1 100%
            ),
            repeating-linear-gradient(
                45deg,
                transparent 0,
                transparent 18px,
                rgba(181, 18, 27, 0.035) 18px,
                rgba(181, 18, 27, 0.035) 19px
            );

        border: 1px solid #edc4c8;
        box-shadow: inset 0 0 30px rgba(181, 18, 27, 0.04);
    }
    
        .bagan-organisasi::before {
        content: "";
        position: absolute;
        top: 0;
        left: 24px;
        right: 24px;
        height: 3px;
        border-radius: 0 0 5px 5px;

        background: linear-gradient(
            90deg,
            #b5121b,
            rgba(181, 18, 27, 0.15),
            transparent
        );

        pointer-events: none;
    }

    .bagan-utama {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 35px;
        align-items: start;
    }

    .bagan-kolom {
        min-width: 0;
        text-align: center;
    }

    .kotak-jabatan-utama {
        background: #b5121b;
        color: #fff;
        padding: 14px;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 700;
        min-height: 55px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .garis-turun {
        height: 25px;
        width: 2px;
        margin: 0 auto;
        background: #b5121b;
    }
    
    .daftar-anggota {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        align-items: stretch;
        gap: 12px;
        padding: 16px 0;
        position: relative;
        border-top: none;
    }

    .daftar-anggota::before {
        display: none;
    }

    .daftar-anggota::before {
        content: "";
        position: absolute;
        width: 2px;
        height: 16px;
        background: #b5121b;
        top: -17px;
        left: 50%;
        transform: translateX(-50%);
    }

    .kartu-karyawan {
        width: 150px;
        flex: 0 1 150px;
        border: 1px solid #e2b4b7;
        border-radius: 10px;
        background: #fff;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0, 0, 0, .06);
    }

    .kartu-aksen {
        height: 6px;
        background: #b5121b;
    }

    .kartu-isi {
        padding: 13px 9px;
        text-align: center;
    }

    .foto-upload {
        position: relative;
        display: block;
        width: 70px;
        height: 70px;
        margin: 0 auto 10px;
        border: 2px solid #b5121b;
        border-radius: 50%;
        overflow: hidden;
        background: #eee;
        cursor: pointer;
    }

    .foto-upload img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
    }

    .foto-upload:hover::after {
        content: "Ganti foto";
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 10px;
        background: rgba(140, 0, 0, .78);
    }

    .foto-input {
        display: none;
    }

    .karyawan-nama {
        color: #292929;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 6px;
        overflow-wrap: anywhere;
    }

    .karyawan-jabatan {
        color: #b5121b;
        font-size: 11px;
        font-weight: 600;
        margin-bottom: 5px;
        overflow-wrap: anywhere;
    }

    .karyawan-unit {
        color: #777;
        font-size: 10px;
        margin: 0;
        overflow-wrap: anywhere;
    }

    .pesan-kosong {
        color: #777;
        font-size: 12px;
        padding: 12px;
        text-align: center;
        width: 100%;
    }

    .cabang-pm {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
        gap: 16px;
        padding-top: 24px;
        position: relative;
    }

    .cabang-pm::before {
        content: "";
        position: absolute;
        top: 0;
        left: 25%;
        right: 25%;
        height: 2px;
        background: #b5121b;
    }

    .cabang-tim {
        min-width: 0;
        position: relative;
    }

    .cabang-tim::before {
        content: "";
        position: absolute;
        top: -24px;
        left: 50%;
        width: 2px;
        height: 24px;
        background: #b5121b;
        transform: translateX(-50%);
    }

    .kotak-subjabatan {
        background: #b5121b;
        color: #fff;
        border-radius: 8px;
        padding: 12px 8px;
        min-height: 48px;
        display: flex;
        justify-content: center;
        align-items: center;
        font-weight: 700;
        font-size: 13px;
        overflow-wrap: anywhere;
    }

    .rantai-branch {
        position: relative;
        padding-top: 24px;
    }

    .rantai-branch::before {
        content: "";
        position: absolute;
        top: 0;
        left: 50%;
        height: 24px;
        width: 2px;
        background: #b5121b;
        transform: translateX(-50%);
    }

    .blok-jabatan {
        position: relative;
        margin-bottom: 0;
    }

    .blok-jabatan + .blok-jabatan {
        margin-top: 0;
    }

    .blok-jabatan + .blok-jabatan::before {
        content: "";
        display: block;
        height: 25px;
        width: 2px;
        background: #b5121b;
        margin: 0 auto;
    }

    .blok-jabatan .daftar-anggota {
        margin-bottom: 0;
    }

    .struktur-info {
        color: #777;
        font-size: 12px;
        margin-top: 20px;
    }

    @media (max-width: 700px) {
        .struktur-wrapper {
            padding: 14px;
        }

        .bagan-organisasi {
            min-width: 850px;
        }
    }
</style>

<div class="struktur-wrapper">

    <div class="struktur-search">
        <input
            type="text"
            id="cari-struktur"
            class="form-control"
            placeholder="Cari nama, jabatan, atau sub unit..."
        >
    </div>

    @if ($dataTampil->count() > 0)

        <div class="bagan-organisasi">
            <div class="bagan-utama">

                {{-- SISI KIRI: PM --}}
                <section class="bagan-kolom">

                    <div class="daftar-anggota">
                        @forelse ($pm as $item)
                            <div
                                class="kartu-karyawan"
                                data-search="{{ strtolower(($item->nama ?? '') . ' ' . ($item->nama_jabatan ?? '') . ' ' . ($item->sub_unit ?? '')) }}"
                            >
                                <div class="kartu-aksen"></div>

                                <div class="kartu-isi">
                                    <label class="foto-upload">
                                        <img
                                            src="{{ !empty($item->foto) ? asset('storage/' . $item->foto) : asset('assets/images/defaultprofile.png') }}"
                                            alt="Foto profil"
                                            class="foto-preview"
                                            onerror="this.onerror=null; this.src='{{ asset('assets/images/defaultprofile.png') }}';"
                                        >
                                        <input
                                            type="file"
                                            class="foto-input"
                                            accept="image/*"
                                        >
                                    </label>

                                    <div class="karyawan-nama">
                                        {{ $item->nama }}
                                    </div>

                                    <div class="karyawan-jabatan">
                                        {{ $item->nama_jabatan }}
                                    </div>

                                    <p class="karyawan-unit">
                                        {{ $item->sub_unit ?: 'Sub unit belum diisi' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="pesan-kosong">
                                Data PM belum ditemukan.
                            </div>
                        @endforelse
                    </div>

                    <div class="garis-turun"></div>

                    <div class="cabang-pm">

                        @foreach ($timPM as $tim)
                            <div class="cabang-tim">

                                <div class="kotak-subjabatan">
                                    {{ $tim['nama'] }}
                                </div>

                                <div class="garis-turun"></div>

                                <div class="daftar-anggota">

                                    @forelse ($tim['anggota'] as $item)
                                        <div
                                            class="kartu-karyawan"
                                            data-search="{{ strtolower(($item->nama ?? '') . ' ' . ($item->nama_jabatan ?? '') . ' ' . ($item->sub_unit ?? '')) }}"
                                        >
                                            <div class="kartu-aksen"></div>

                                            <div class="kartu-isi">
                                                <label class="foto-upload">
                                                    <img
                                                        src="{{ !empty($item->foto) ? asset('storage/' . $item->foto) : asset('assets/images/defaultprofile.png') }}"
                                                        alt="Foto profil"
                                                        class="foto-preview"
                                                        onerror="this.onerror=null; this.src='{{ asset('assets/images/defaultprofile.png') }}';"
                                                    >
                                                    <input
                                                        type="file"
                                                        class="foto-input"
                                                        accept="image/*"
                                                    >
                                                </label>

                                                <div class="karyawan-nama">
                                                    {{ $item->nama }}
                                                </div>

                                                <div class="karyawan-jabatan">
                                                    {{ $item->nama_jabatan }}
                                                </div>

                                                <p class="karyawan-unit">
                                                    {{ $item->sub_unit ?: 'Sub unit belum diisi' }}
                                                </p>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="pesan-kosong">
                                            Belum ada anggota {{ $tim['nama'] }}.
                                        </div>
                                    @endforelse

                                </div>
                            </div>
                        @endforeach

                    </div>
                </section>

                {{-- SISI KANAN: MGR BRANCH --}}
                <section class="bagan-kolom">

                    <div class="daftar-anggota">
                        @forelse ($mgr as $item)
                            <div
                                class="kartu-karyawan"
                                data-search="{{ strtolower(($item->nama ?? '') . ' ' . ($item->nama_jabatan ?? '') . ' ' . ($item->sub_unit ?? '')) }}"
                            >
                                <div class="kartu-aksen"></div>

                                <div class="kartu-isi">
                                    <label class="foto-upload">
                                        <img
                                            src="{{ !empty($item->foto) ? asset('storage/' . $item->foto) : asset('assets/images/defaultprofile.png') }}"
                                            alt="Foto profil"
                                            class="foto-preview"
                                            onerror="this.onerror=null; this.src='{{ asset('assets/images/defaultprofile.png') }}';"
                                        >
                                        <input
                                            type="file"
                                            class="foto-input"
                                            accept="image/*"
                                        >
                                    </label>

                                    <div class="karyawan-nama">
                                        {{ $item->nama }}
                                    </div>

                                    <div class="karyawan-jabatan">
                                        {{ $item->nama_jabatan }}
                                    </div>

                                    <p class="karyawan-unit">
                                        {{ $item->sub_unit ?: 'Sub unit belum diisi' }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <div class="pesan-kosong">
                                Data Mgr Branch belum ditemukan.
                            </div>
                        @endforelse
                    </div>

                    <div class="garis-turun"></div>

                    <div class="rantai-branch">

                        @foreach ($timBranch as $tim)
                            <div class="blok-jabatan">

                                <div class="kotak-subjabatan">
                                    {{ $tim['nama'] }}
                                </div>

                                <div class="garis-turun"></div>

                                <div class="daftar-anggota">

                                    @forelse ($tim['anggota'] as $item)
                                        <div
                                            class="kartu-karyawan"
                                            data-search="{{ strtolower(($item->nama ?? '') . ' ' . ($item->nama_jabatan ?? '') . ' ' . ($item->sub_unit ?? '')) }}"
                                        >
                                            <div class="kartu-aksen"></div>

                                            <div class="kartu-isi">
                                                <label class="foto-upload">
                                                    <img
                                                        src="{{ !empty($item->foto) ? asset('storage/' . $item->foto) : asset('assets/images/defaultprofile.png') }}"
                                                        alt="Foto profil"
                                                        class="foto-preview"
                                                        onerror="this.onerror=null; this.src='{{ asset('assets/images/defaultprofile.png') }}';"
                                                    >
                                                    <input
                                                        type="file"
                                                        class="foto-input"
                                                        accept="image/*"
                                                    >
                                                </label>

                                                <div class="karyawan-nama">
                                                    {{ $item->nama }}
                                                </div>

                                                <div class="karyawan-jabatan">
                                                    {{ $item->nama_jabatan }}
                                                </div>

                                                <p class="karyawan-unit">
                                                    {{ $item->sub_unit ?: 'Sub unit belum diisi' }}
                                                </p>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="pesan-kosong">
                                            Belum ada anggota {{ $tim['nama'] }}.
                                        </div>
                                    @endforelse

                                </div>
                            </div>
                        @endforeach

                    </div>
                </section>

            </div>
        </div>

    @else

        <div class="alert alert-warning">
            Belum ada data struktur organisasi yang dapat ditampilkan.
        </div>

    @endif

    <div class="struktur-info">
        Total data yang ditampilkan: {{ $dataTampil->count() }} karyawan
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const inputCari = document.getElementById('cari-struktur');

    if (inputCari) {
        inputCari.addEventListener('input', function () {
            const kataKunci = this.value.toLowerCase().trim();

            document.querySelectorAll('.kartu-karyawan').forEach(function (kartu) {
                const teks = (kartu.dataset.search || '').toLowerCase();
                kartu.style.display = teks.includes(kataKunci) ? '' : 'none';
            });
        });
    }

    document.querySelectorAll('.foto-input').forEach(function (input) {
        input.addEventListener('change', function () {
            const file = this.files[0];

            if (!file) return;

            if (!file.type.startsWith('image/')) {
                alert('Silakan pilih file gambar.');
                this.value = '';
                return;
            }

            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran foto maksimal 5 MB.');
                this.value = '';
                return;
            }

            const foto = this.closest('.kartu-karyawan')
                .querySelector('.foto-preview');

            const reader = new FileReader();

            reader.onload = function (event) {
                foto.src = event.target.result;
            };

            reader.readAsDataURL(file);
        });
    });

});
</script>

@endsection