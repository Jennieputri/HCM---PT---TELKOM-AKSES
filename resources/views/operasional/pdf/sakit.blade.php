<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Keterangan Sakit</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 25px 35px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #222;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header {
            border-bottom: 2px solid #b40020;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }

        .header td {
            vertical-align: middle;
        }

        .logo {
            width: 155px;
            height: auto;
        }

        .company-name {
            color: #b40020;
            font-weight: bold;
        }

        .company {
            color: #b40020;
            font-weight: bold;
            font-size: 11px;
            text-align: right;
        }

        h2 {
            text-align: center;
            font-size: 16px;
            margin: 20px 0 30px;
            letter-spacing: 1px;
        }

        .content td {
            padding: 10px 5px;
            vertical-align: top;
        }

        .label {
            width: 28%;
            font-weight: bold;
        }

        .description {
            line-height: 1.8;
            text-align: justify;
            margin-top: 25px;
        }

        .signature {
            margin-top: 70px;
        }

        .signature td {
            text-align: center;
            vertical-align: top;
        }

        .sign-space {
            height: 75px;
        }

        .attachment {
            margin-top: 30px;
            padding: 10px;
            border: 1px solid #777;
        }
    </style>
</head>

<body>

    <table class="header">
        <tr>
            <td style="width: 65%;">
                <span class="company-name">PT TELKOM AKSES</span><br>
                HUMAN CAPITAL MANAGEMENT<br>
                BRANCH PALANGKARAYA
            </td>

            <td style="width: 35%; text-align: right;">
                <img
                    class="logo"
                    src="{{ public_path('assets/images/logo-telkomakses.png') }}"
                    alt="Logo Telkom Akses"
                >
            </td>
        </tr>
    </table>

    <h2>FORM PENGAJUAN IZIN SAKIT</h2>

    <table class="content">
        <tr>
            <td class="label">Nomor Dokumen</td>
            <td>: {{ $absen->nomor_dokumen ?? '-' }}</td>
        </tr>

        <tr>
            <td class="label">NIK</td>
            <td>: {{ $karyawan->nik ?? '-' }}</td>
        </tr>

        <tr>
            <td class="label">Nama Karyawan</td>
            <td>: {{ $karyawan->name ?? '-' }}</td>
        </tr>

        <tr>
            <td class="label">Tanggal Mulai</td>
            <td>
                :
                {{ !empty($absen->tanggal_mulai) ? \Carbon\Carbon::parse($absen->tanggal_mulai)->format('d-m-Y') : '-' }}
            </td>
        </tr>

        <tr>
            <td class="label">Tanggal Selesai</td>
            <td>
                :
                {{ !empty($absen->tanggal_selesai) ? \Carbon\Carbon::parse($absen->tanggal_selesai)->format('d-m-Y') : '-' }}
            </td>
        </tr>
    </table>

    <div class="description">
        <strong>Keterangan:</strong><br>
        {{ $absen->keterangan ?? '-' }}
    </div>

    <div class="attachment">
        <strong>Lampiran Surat Dokter:</strong><br><br>

        @if (!empty($absen->surat_dokter_path))
            Surat dokter telah dilampirkan pada pengajuan ini.
        @else
            Tidak ada lampiran surat dokter.
        @endif
    </div>

    <table class="signature">
        <tr>
            <td style="width: 50%;">
                Karyawan yang Mengajukan,
                <div class="sign-space"></div>
                <strong>{{ $karyawan->name ?? '-' }}</strong><br>
                NIK: {{ $karyawan->nik ?? '-' }}
            </td>

            <td style="width: 50%;">
                Mengetahui,
                <div class="sign-space"></div>
                <strong>Human Capital Management</strong>
            </td>
        </tr>
    </table>

</body>
</html>
