<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Tugas SPPD</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 25px 35px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #222;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header {
            border-bottom: 2px solid #b40020;
            padding-bottom: 10px;
            margin-bottom: 18px;
        }

        .logo {
            width: 125px;
            height: auto;
        }

        .company {
            text-align: right;
            color: #b40020;
            font-weight: bold;
            font-size: 10px;
        }

        h2 {
            text-align: center;
            font-size: 16px;
            margin: 15px 0 22px;
            letter-spacing: 1px;
        }

        .identity td {
            padding: 6px 4px;
            vertical-align: top;
        }

        .label {
            width: 25%;
            font-weight: bold;
        }

        .employee {
            margin-top: 15px;
        }

        .employee th,
        .employee td {
            border: 1px solid #333;
            padding: 7px 5px;
            text-align: center;
        }

        .employee th {
            background: #eeeeee;
            font-size: 9px;
        }

        .period {
            margin-top: 18px;
            line-height: 1.8;
        }

        .signature {
            margin-top: 35px;
        }

        .signature td {
            text-align: center;
            vertical-align: top;
        }

        .sign-space {
            height: 75px;
        }
    </style>
</head>

<body>

    <table class="header">
        <tr>
            <td style="width: 40%;">
                <img
                    class="logo"
                    src="{{ public_path('assets/images/logo-telkomakses-sppd.jpg') }}"
                    style="width: 150px; height: auto;"
                >
            </td>

            <td class="company">
                PT TELKOM AKSES<br>
                BRANCH PALANGKARAYA
            </td>
        </tr>
    </table>

    <h2>SURAT TUGAS</h2>

    <table class="identity">
        <tr>
            <td class="label">Nomor Dokumen</td>
            <td>: {{ $absen->nomor_dokumen ?? '-' }}</td>
        </tr>

        <tr>
            <td class="label">Tanggal</td>
            <td>
                :
                {{ !empty($sppd->tanggal_surat) ? \Carbon\Carbon::parse($sppd->tanggal_surat)->format('d-m-Y') : '-' }}
            </td>
        </tr>

        <tr>
            <td class="label">Perihal Pekerjaan</td>
            <td>: {{ $sppd->perihal_pekerjaan ?? '-' }}</td>
        </tr>

        <tr>
            <td class="label">Dilokasi</td>
            <td>: {{ $sppd->lokasi_pekerjaan ?? '-' }}</td>
        </tr>
    </table>

    <p style="margin-top: 20px;">
        Dengan ini kami tugaskan kepada:
    </p>

    <table class="employee">
        <thead>
            <tr>
                <th style="width: 5%;">NO</th>
                <th style="width: 15%;">NIK</th>
                <th style="width: 23%;">NAMA</th>
                <th style="width: 19%;">POSITION</th>
                <th style="width: 19%;">KOTA ASAL</th>
                <th style="width: 19%;">KOTA TUJUAN</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($sppdKaryawan as $index => $pegawai)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $pegawai->nik ?? '-' }}</td>
                    <td>{{ $pegawai->nama ?? '-' }}</td>
                    <td>{{ $pegawai->position ?? '-' }}</td>
                    <td>{{ $pegawai->kota_asal ?? '-' }}</td>
                    <td>{{ $pegawai->kota_tujuan ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Data karyawan tidak tersedia</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="period">
        Penugasan ini berlaku mulai tanggal
        <strong>
            {{ !empty($sppd->tanggal_mulai_penugasan) ? \Carbon\Carbon::parse($sppd->tanggal_mulai_penugasan)->format('d-m-Y') : '-' }}
        </strong>
        s.d.
        <strong>
            {{ !empty($sppd->tanggal_selesai_penugasan) ? \Carbon\Carbon::parse($sppd->tanggal_selesai_penugasan)->format('d-m-Y') : '-' }}
        </strong>.
    </div>

    <table class="signature">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;">
                Yang Memberi Tugas,
                <div class="sign-space"></div>
                <strong>PGS Mgr Branch</strong>
            </td>
        </tr>
    </table>

</body>
</html>
