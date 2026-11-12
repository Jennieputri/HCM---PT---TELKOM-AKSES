<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Cuti</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 22px 30px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #222;
            margin: 0;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #b40020;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .header td {
            vertical-align: middle;
        }

        .logo {
            width: 115px;
            height: auto;
        }

        .company {
            text-align: right;
            font-size: 10px;
            font-weight: bold;
            color: #b40020;
        }

        h2 {
            text-align: center;
            font-size: 15px;
            margin: 12px 0 14px;
            letter-spacing: 1px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .form td,
        .form th {
            border: 1px solid #555;
            padding: 6px;
            vertical-align: top;
        }

        .section {
            background: #eeeeee;
            font-weight: bold;
            padding: 7px !important;
        }

        .label {
            width: 28%;
            font-weight: bold;
        }

        .value {
            min-height: 16px;
        }

        .signature {
            margin-top: 22px;
        }

        .signature td {
            text-align: center;
            width: 33.33%;
            vertical-align: top;
            padding: 5px;
        }

        .sign-space {
            height: 65px;
        }

        .footer {
            margin-top: 10px;
            font-size: 9px;
        }
    </style>
</head>

<body>

    <table class="header">
        <tr>
            <td style="width: 35%;">
                <img
                    class="logo"
                    src="{{ public_path('assets/images/logo-telkomakses-cuti.jpg') }}"
                    style="width: 150px; height: auto;"
                >
            </td>
            <td class="company">
                PT TELKOM AKSES<br>
                HUMAN CAPITAL MANAGEMENT
            </td>
        </tr>
    </table>

    <h2>FORM CUTI</h2>

    <table class="form">
        <tr>
            <td class="section" colspan="2">
                JENIS CUTI
            </td>
        </tr>

        <tr>
            <td colspan="2">
                {{ $cuti->jenis_cuti ?? '-' }}
            </td>
        </tr>

        <tr>
            <td class="section" colspan="2">
                DATA KARYAWAN
            </td>
        </tr>

        <tr>
            <td class="label">NIK</td>
            <td class="value">{{ $karyawan->nik ?? '-' }}</td>
        </tr>

        <tr>
            <td class="label">Nama</td>
            <td class="value">{{ $karyawan->name ?? '-' }}</td>
        </tr>

        <tr>
            <td class="section" colspan="2">
                DETAIL CUTI
            </td>
        </tr>

        <tr>
            <td class="label">Lama Cuti</td>
            <td>{{ $cuti->lama_cuti ?? '-' }} hari</td>
        </tr>

        <tr>
            <td class="label">Mulai Tanggal</td>
            <td>
                {{ !empty($cuti->tanggal_mulai) ? \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d-m-Y') : '-' }}
            </td>
        </tr>

        <tr>
            <td class="label">Sampai Dengan</td>
            <td>
                {{ !empty($cuti->tanggal_selesai) ? \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d-m-Y') : '-' }}
            </td>
        </tr>

        <tr>
            <td class="label">Alamat Selama Cuti</td>
            <td>{{ $cuti->alamat_selama_cuti ?? '-' }}</td>
        </tr>

        <tr>
            <td class="label">Telepon</td>
            <td>{{ $cuti->telepon_cuti ?? '-' }}</td>
        </tr>

        <tr>
            <td class="section" colspan="2">
                PERJALANAN CUTI
            </td>
        </tr>

        <tr>
            <td class="label">Berangkat</td>
            <td>
                {{ !empty($cuti->perjalanan_berangkat_mulai) ? \Carbon\Carbon::parse($cuti->perjalanan_berangkat_mulai)->format('d-m-Y') : '-' }}
                sampai
                {{ !empty($cuti->perjalanan_berangkat_selesai) ? \Carbon\Carbon::parse($cuti->perjalanan_berangkat_selesai)->format('d-m-Y') : '-' }}
            </td>
        </tr>

        <tr>
            <td class="label">Pulang</td>
            <td>
                {{ !empty($cuti->perjalanan_pulang_mulai) ? \Carbon\Carbon::parse($cuti->perjalanan_pulang_mulai)->format('d-m-Y') : '-' }}
                sampai
                {{ !empty($cuti->perjalanan_pulang_selesai) ? \Carbon\Carbon::parse($cuti->perjalanan_pulang_selesai)->format('d-m-Y') : '-' }}
            </td>
        </tr>

        <tr>
            <td class="section" colspan="2">
                ALASAN CUTI
            </td>
        </tr>

        <tr>
            <td colspan="2" style="height: 55px;">
                {{ $cuti->alasan_cuti ?? '-' }}
            </td>
        </tr>
    </table>

    <table class="signature">
        <tr>
            <td>
                Pegawai
                <div class="sign-space"></div>
                ({{ $karyawan->name ?? '-' }})
            </td>

            <td>
                Mengetahui,
                <div class="sign-space"></div>
                (____________________)
            </td>

            <td>
                Menyetujui,
                <div class="sign-space"></div>
                (____________________)
            </td>
        </tr>
    </table>

    <table class="form footer">
        <tr>
            <td>
                Saldo Cuti Sebelumnya
            </td>
            <td>
                Sisa Cuti Setelah Pengajuan
            </td>
            <td>
                NIK
            </td>
        </tr>
        <tr>
            <td style="height: 25px;"></td>
            <td></td>
            <td>{{ $karyawan->nik ?? '-' }}</td>
        </tr>
    </table>

</body>
</html>
