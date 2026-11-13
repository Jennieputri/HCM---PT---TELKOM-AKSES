<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;
use Symfony\Component\Process\Process;

class AbsenPdfGenerator
{
    private const CONVERSION_TIMEOUT_SECONDS = 120;

    private const EMPTY_VALUE = '-';

    private const SPPD_ROW_FIELDS = ['nomor_urut', 'nik_karyawan', 'nama_karyawan', 'position', 'kota_asal', 'kota_tujuan'];

    public static function cuti(object $absen, object $karyawan, object $manager, object $atasan, array $leaveBalance)
    {
        $processor = self::processor('template_cuti.docx');

        $processor->setValues([
            'nama'              => self::upperText($karyawan->name),
            'nik'               => self::text($karyawan->nik),
            'band'              => self::text($absen->band),
            'jabatan'           => self::text($absen->jabatan),
            'unit_kerja'        => self::text($absen->unit_kerja),
            'jenis_cuti'        => self::text($absen->jenis_cuti),
            'lama_cuti'         => self::text($absen->lama_cuti),
            'cuti_mulai'        => self::upperDate($absen->tanggal_mulai),
            'cuti_selesai'      => self::upperDate($absen->tanggal_selesai),
            'alamat_cuti'       => self::text($absen->alamat_selama_cuti),
            'telepon_cuti'      => self::text($absen->telepon_cuti),
            'berangkat_mulai'   => self::upperDate($absen->perjalanan_berangkat_mulai),
            'berangkat_selesai' => self::upperDate($absen->perjalanan_berangkat_selesai),
            'pulang_mulai'      => self::upperDate($absen->perjalanan_pulang_mulai),
            'pulang_selesai'    => self::upperDate($absen->perjalanan_pulang_selesai),
            'alasan_cuti'       => self::text($absen->keterangan),
            'nama_manager'      => self::upperText($manager->nama),
            'nik_manager'       => self::text($manager->nik),
            'nama_atasan'       => self::upperText($atasan->name),
            'nik_atasan'        => self::text($atasan->nik),
            'saldo_cuti'        => self::text($leaveBalance['saldo']),
            'sisa_cuti'         => self::text($leaveBalance['sisa']),
        ]);

        return self::convertToPdf($processor);
    }

    public static function sppd(object $absen, array $assignedEmployees, object $manager)
    {
        $processor = self::processor('template_sppd.docx');

        $processor->setValues([
            'tanggal_surat'         => self::longDate($absen->tanggal_surat),
            'perihal_pekerjaan'     => self::text($absen->keterangan),
            'lokasi_pekerjaan'      => self::text($absen->lokasi_pekerjaan),
            'tanggal_mulai'         => self::longDate($absen->tanggal_mulai),
            'tanggal_selesai'       => self::longDate($absen->tanggal_selesai),
            'kota_surat'            => self::text(config('absen.kota_surat')),
            'nama_pemberi_tugas'    => self::text($manager->nama),
            'jabatan_pemberi_tugas' => self::text($manager->jabatan),
        ]);

        $employeeRows = [];

        foreach (array_values($assignedEmployees) as $rowIndex => $employee)
        {
            $employeeRows[] = [
                'nomor_urut'    => (string) ($rowIndex + 1),
                'nik_karyawan'  => self::text($employee->nik ?? null),
                'nama_karyawan' => self::text($employee->nama ?? null),
                'position'      => self::text($employee->position ?? null),
                'kota_asal'     => self::text($employee->kota_asal ?? null),
                'kota_tujuan'   => self::text($employee->kota_tujuan ?? null),
            ];
        }

        if ($employeeRows === [])
        {
            $employeeRows[] = array_fill_keys(self::SPPD_ROW_FIELDS, self::EMPTY_VALUE);
        }

        $processor->cloneRowAndSetValues('nik_karyawan', $employeeRows);

        return self::convertToPdf($processor);
    }

    public static function sakit(object $absen, object $karyawan, object $atasan)
    {
        $processor = self::processor('template_sakit.docx');

        $processor->setValues([
            'nomor_dokumen'   => self::text($absen->nomor_dokumen),
            'nama'            => self::upperText($karyawan->name),
            'nik'             => self::text($karyawan->nik),
            'tanggal_mulai'   => self::longDate($absen->tanggal_mulai),
            'tanggal_selesai' => self::longDate($absen->tanggal_selesai),
            'lama_izin'       => self::text((int) Carbon::parse($absen->tanggal_mulai)->diffInDays(Carbon::parse($absen->tanggal_selesai)) + 1),
            'keterangan'      => self::text($absen->keterangan),
            'surat_dokter'    => $absen->surat_dokter_path ? 'Terlampir' : 'Tidak ada',
            'kota_surat'      => self::text(config('absen.kota_surat')),
            'tanggal_surat'   => self::longDate($absen->updated_at),
            'nama_atasan'     => self::upperText($atasan->name),
            'nik_atasan'      => self::text($atasan->nik),
        ]);

        return self::convertToPdf($processor);
    }

    private static function processor(string $templateName)
    {
        $templatePath = resource_path('templates/'.$templateName);

        if (! is_file($templatePath))
        {
            throw new RuntimeException('Template Word tidak ditemukan: '.$templateName);
        }

        Settings::setTempDir(self::workingDirectory());
        Settings::setOutputEscapingEnabled(true);

        return new TemplateProcessor($templatePath);
    }

    private static function convertToPdf(TemplateProcessor $processor)
    {
        $libreOfficeBinary = (string) config('services.libreoffice.binary');

        if ($libreOfficeBinary === '' || ! is_file($libreOfficeBinary))
        {
            throw new RuntimeException('LibreOffice tidak ditemukan. Atur LIBREOFFICE_BINARY di .env.');
        }

        $workingDirectory = self::workingDirectory();
        $fileName         = uniqid('absen-', true);
        $docxPath         = $workingDirectory.DIRECTORY_SEPARATOR.$fileName.'.docx';
        $pdfPath          = $workingDirectory.DIRECTORY_SEPARATOR.$fileName.'.pdf';
        $profileDirectory = $workingDirectory.DIRECTORY_SEPARATOR.'lo-profile-'.$fileName;

        try
        {
            $processor->saveAs($docxPath);
            File::ensureDirectoryExists($profileDirectory);

            $process = new Process([
                $libreOfficeBinary,
                '-env:UserInstallation='.self::fileUri($profileDirectory),
                '--headless',
                '--nologo',
                '--convert-to',
                'pdf',
                '--outdir',
                $workingDirectory,
                $docxPath,
            ], $workingDirectory, ['TEMP' => $workingDirectory, 'TMP' => $workingDirectory]);

            $process->setTimeout(self::CONVERSION_TIMEOUT_SECONDS);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($pdfPath))
            {
                throw new RuntimeException('Konversi PDF gagal. Exit code: '.var_export($process->getExitCode(), true).' | '.$process->getErrorOutput());
            }

            $pdfContents = (string) file_get_contents($pdfPath);

            if (! str_starts_with($pdfContents, '%PDF-'))
            {
                throw new RuntimeException('File PDF tidak valid.');
            }

            return $pdfContents;
        }
        finally
        {
            File::deleteDirectory($profileDirectory);
            File::delete([$docxPath, $pdfPath]);
        }
    }

    private static function workingDirectory()
    {
        $workingDirectory = storage_path('framework/cache/phpword');

        File::ensureDirectoryExists($workingDirectory);

        return $workingDirectory;
    }

    private static function fileUri(string $path)
    {
        return 'file:///'.ltrim(str_replace(['\\', ' '], ['/', '%20'], $path), '/');
    }

    private static function longDate($value)
    {
        return $value ? Carbon::parse($value)->locale('id')->translatedFormat('j F Y') : self::EMPTY_VALUE;
    }

    private static function upperDate($value)
    {
        return mb_strtoupper(self::longDate($value));
    }

    private static function text($value)
    {
        $text = trim((string) $value);

        return $text === '' ? self::EMPTY_VALUE : $text;
    }

    private static function upperText($value)
    {
        return mb_strtoupper(self::text($value));
    }
}
