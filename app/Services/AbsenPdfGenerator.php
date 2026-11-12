<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\Settings;
use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;
use Symfony\Component\Process\Process;

class AbsenPdfGenerator
{
    private const LIBREOFFICE =
        'C:\\Program Files\\LibreOffice\\program\\soffice.com';

    public static function cuti(
        object $absen,
        object $karyawan,
        object $cuti
    ): string {
        $processor = self::processor(
            self::template('template_pdf_cuti.docx')
        );

        $processor->setValue('nama', self::text($karyawan->name ?? null));
        $processor->setValue('jabatan', self::text(
            $cuti->jabatan ?? $karyawan->jabatan ?? $karyawan->position ?? null
        ));
        $processor->setValue('unit_kerja', self::text(
            $cuti->unit_kerja ?? $karyawan->unit_kerja ?? $karyawan->unit ?? null
        ));
        $processor->setValue('band', self::text(
            $cuti->band ?? $karyawan->band ?? null
        ));
        $processor->setValue('nik', self::text(
            $karyawan->nik ?? $absen->nik_karyawan ?? null
        ));

        $processor->setValue('lama_cuti', self::text(
            $cuti->lama_cuti ?? null
        ));

        $processor->setValue('jenis_cuti', self::text(
            $cuti->jenis_cuti ?? null
        ));

        $processor->setValue('cuti_mulai', self::date(
            $cuti->tanggal_mulai ?? null
        ));

        $processor->setValue('cuti_selesai', self::date(
            $cuti->tanggal_selesai ?? null
        ));

        $processor->setValue('alamat_cuti', self::text(
            $cuti->alamat_selama_cuti ?? null
        ));

        $processor->setValue('no_hp_cuti', self::text(
            $cuti->telepon_cuti ?? null
        ));

        $processor->setValue(
            'perjalanan_cuti_brngkt',
            self::dateRange(
                $cuti->perjalanan_berangkat_mulai ?? null,
                $cuti->perjalanan_berangkat_selesai ?? null
            )
        );

        $processor->setValue(
            'perjalanan_cuti_pulang',
            self::dateRange(
                $cuti->perjalanan_pulang_mulai ?? null,
                $cuti->perjalanan_pulang_selesai ?? null
            )
        );

        $processor->setValue('alasan_cuti', self::text(
            $cuti->alasan_cuti ?? null
        ));

        $processor->setValue('nik_mengajukan', self::text(
            $karyawan->nik ?? $absen->nik_karyawan ?? null
        ));

        // Placeholder ini belum memiliki sumber data pada form/pengajuan saat ini.
        // Isi dengan tanda kosong yang konsisten agar tidak ada token {..} tersisa.
        $processor->setValue('nik_manager', '-');
        $processor->setValue('nik_atasan', '-');
        $processor->setValue('saldo_cuti', '-');
        $processor->setValue('sisa_cuti', '-');

        return self::toPdf($processor);
    }

    public static function sppd(
        object $sppd,
        Collection $sppdKaryawan
    ): string {
        $processor = self::processor(
            self::template('template_pdf_sppd.docx')
        );

        $processor->setValue(
            'tgl_penulisan_sppd',
            self::date($sppd->tanggal_surat ?? null)
        );

        $processor->setValue(
            'perihal_pekerjaan',
            self::text($sppd->perihal_pekerjaan ?? null)
        );

        $processor->setValue(
            'lokasi_sppd',
            self::text($sppd->lokasi_pekerjaan ?? null)
        );

        $processor->setValue(
            'tgl_mulai_sppd',
            self::date($sppd->tanggal_mulai_penugasan ?? null)
        );

        $processor->setValue(
            'tgl_selesai_sppd',
            self::date($sppd->tanggal_selesai_penugasan ?? null)
        );

        $rows = $sppdKaryawan->map(
            fn (object $pegawai) => [
                'nik_karyawan' => self::text($pegawai->nik ?? null),
                'nama_karyawan' => self::text($pegawai->nama ?? null),
                'position_sppd' => self::text($pegawai->position ?? null),
                'Kota_asal' => self::text($pegawai->kota_asal ?? null),
                'Kota_tujuan' => self::text($pegawai->kota_tujuan ?? null),
            ]
        )->values()->all();

        if ($rows === []) {
            $rows[] = [
                'nik_karyawan' => '-',
                'nama_karyawan' => '-',
                'position_sppd' => '-',
                'Kota_asal' => '-',
                'Kota_tujuan' => '-',
            ];
        }

        $processor->cloneRowAndSetValues(
            'nik_karyawan',
            $rows
        );

        return self::toPdf($processor);
    }

    private static function processor(string $template): TemplateProcessor
    {
        $temporaryDirectory = storage_path(
            'framework/cache/phpword'
        );

        File::ensureDirectoryExists($temporaryDirectory);

        Settings::setTempDir($temporaryDirectory);

        // PhpWord memerlukan format ${nama} untuk setValue dan cloneRow.
        // Normalisasi salinan sementara agar file template asli tetap utuh.
        $normalizedTemplate = self::normalizeTemplateMacros(
            $template,
            $temporaryDirectory
        );

        try {
            return new TemplateProcessor($normalizedTemplate);
        } finally {
            if (is_file($normalizedTemplate)) {
                unlink($normalizedTemplate);
            }
        }
    }

    /**
     * Convert {placeholder} macros (including macros split between Word runs)
     * to PhpWord's ${placeholder} format without changing the source template.
     */
    private static function normalizeTemplateMacros(
        string $template,
        string $temporaryDirectory
    ): string {
        $normalizedTemplate = $temporaryDirectory
            . DIRECTORY_SEPARATOR
            . uniqid('template-', true)
            . '.docx';

        if (! copy($template, $normalizedTemplate)) {
            throw new RuntimeException(
                'Template Word tidak dapat disiapkan untuk diproses.'
            );
        }

        $archive = new \ZipArchive();
        if ($archive->open($normalizedTemplate) !== true) {
            unlink($normalizedTemplate);
            throw new RuntimeException(
                'File template Word tidak dapat dibuka.'
            );
        }

        try {
            for ($index = 0; $index < $archive->numFiles; $index++) {
                $partName = $archive->getNameIndex($index);

                if (
                    ! is_string($partName)
                    || ! str_starts_with($partName, 'word/')
                    || ! str_ends_with($partName, '.xml')
                ) {
                    continue;
                }

                $xml = $archive->getFromName($partName);
                if (! is_string($xml) || ! str_contains($xml, '{')) {
                    continue;
                }

                $document = new \DOMDocument();
                $document->preserveWhiteSpace = true;
                if (! $document->loadXML($xml, LIBXML_NONET)) {
                    throw new RuntimeException(
                        'Bagian template Word tidak dapat dibaca: ' . $partName
                    );
                }

                $xpath = new \DOMXPath($document);
                $xpath->registerNamespace(
                    'w',
                    'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
                );

                foreach ($xpath->query('//w:p') as $paragraph) {
                    $textNodes = [];
                    foreach ($xpath->query('.//w:t', $paragraph) as $textNode) {
                        $ancestor = $textNode->parentNode;
                        $belongsToParagraph = true;
                        while ($ancestor && $ancestor !== $paragraph) {
                            if (
                                $ancestor instanceof \DOMElement
                                && $ancestor->namespaceURI === 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'
                                && $ancestor->localName === 'p'
                            ) {
                                $belongsToParagraph = false;
                                break;
                            }
                            $ancestor = $ancestor->parentNode;
                        }

                        if ($belongsToParagraph) {
                            $textNodes[] = $textNode;
                        }
                    }

                    $starts = [];
                    $paragraphText = '';
                    foreach ($textNodes as $textNode) {
                        $starts[] = strlen($paragraphText);
                        $paragraphText .= $textNode->nodeValue ?? '';
                    }

                    if (! preg_match_all(
                        '/\$?\{([A-Za-z][A-Za-z0-9_]*)\}/',
                        $paragraphText,
                        $macros,
                        PREG_OFFSET_CAPTURE
                    )) {
                        continue;
                    }

                    for ($matchIndex = count($macros[0]) - 1; $matchIndex >= 0; $matchIndex--) {
                        $macro = $macros[0][$matchIndex][0];
                        $macroStart = $macros[0][$matchIndex][1];
                        $macroEnd = $macroStart + strlen($macro);
                        $name = $macros[1][$matchIndex][0];
                        $firstNode = null;
                        $lastNode = null;

                        foreach ($textNodes as $nodeIndex => $textNode) {
                            $nodeStart = $starts[$nodeIndex];
                            $nodeEnd = $nodeStart + strlen($textNode->nodeValue ?? '');
                            if ($firstNode === null && $macroStart >= $nodeStart && $macroStart < $nodeEnd) {
                                $firstNode = $nodeIndex;
                            }
                            if ($macroEnd > $nodeStart && $macroEnd <= $nodeEnd) {
                                $lastNode = $nodeIndex;
                                break;
                            }
                        }

                        if ($firstNode === null || $lastNode === null) {
                            continue;
                        }

                        $replacement = '${' . $name . '}';
                        $firstValue = $textNodes[$firstNode]->nodeValue ?? '';
                        $firstOffset = $macroStart - $starts[$firstNode];
                        $lastValue = $textNodes[$lastNode]->nodeValue ?? '';
                        $lastOffset = $macroEnd - $starts[$lastNode];

                        if ($firstNode === $lastNode) {
                            $textNodes[$firstNode]->nodeValue = substr($firstValue, 0, $firstOffset)
                                . $replacement
                                . substr($firstValue, $lastOffset);
                            continue;
                        }

                        $textNodes[$firstNode]->nodeValue = substr($firstValue, 0, $firstOffset)
                            . $replacement;
                        for ($nodeIndex = $firstNode + 1; $nodeIndex < $lastNode; $nodeIndex++) {
                            $textNodes[$nodeIndex]->nodeValue = '';
                        }
                        $textNodes[$lastNode]->nodeValue = substr($lastValue, $lastOffset);
                    }
                }

                $archive->addFromString($partName, $document->saveXML());
            }
        } catch (\Throwable $exception) {
            $archive->close();
            unlink($normalizedTemplate);
            throw $exception;
        }

        $archive->close();

        return $normalizedTemplate;
    }

    private static function toPdf(
        TemplateProcessor $processor
    ): string {
        $temporaryDirectory = storage_path(
            'framework/cache/phpword'
        );

        File::ensureDirectoryExists($temporaryDirectory);

        if (! is_file(self::LIBREOFFICE)) {
            throw new RuntimeException(
                'LibreOffice tidak ditemukan.'
            );
        }

        $filename = uniqid('absen-', true);

        $docxPath = $temporaryDirectory
            . DIRECTORY_SEPARATOR
            . $filename
            . '.docx';

        $pdfPath = $temporaryDirectory
            . DIRECTORY_SEPARATOR
            . $filename
            . '.pdf';
        $profileDirectory = $temporaryDirectory
            . DIRECTORY_SEPARATOR
            . 'lo-profile-' . $filename;

        try {
            $processor->saveAs($docxPath);
            File::ensureDirectoryExists($profileDirectory);

            // Satu profil sementara per konversi mencegah bentrok dengan
            // sesi LibreOffice lain yang memakai profil default.
            $profileUri = 'file:///' . str_replace(
                ['\\', ' '],
                ['/', '%20'],
                $profileDirectory
            );

            $process = new Process([
                self::LIBREOFFICE,
                '-env:UserInstallation=' . $profileUri,
                '--headless',
                '--nologo',
                '--convert-to',
                'pdf',
                '--outdir',
                $temporaryDirectory,
                $docxPath,
            ], $temporaryDirectory, [
                'TEMP' => $temporaryDirectory,
                'TMP' => $temporaryDirectory,
            ]);

            $process->setTimeout(120);
            $process->run();

            if (! $process->isSuccessful() || ! is_file($pdfPath)) {
                throw new RuntimeException(
                    'Konversi PDF gagal. '
                    . 'Exit Code: ' . var_export($process->getExitCode(), true)
                    . ' | Error: ' . $process->getErrorOutput()
                    . ' | Output: ' . $process->getOutput()
                );
            }

            $contents = file_get_contents($pdfPath);

            if (
                $contents === false
                || $contents === ''
                || substr($contents, 0, 5) !== '%PDF-'
            ) {
                throw new RuntimeException(
                    'File PDF tidak valid.'
                );
            }
            return $contents;

        } finally {
            File::deleteDirectory($profileDirectory);

            foreach ([$docxPath, $pdfPath] as $temporaryFile) {
                if (is_file($temporaryFile)) {
                    unlink($temporaryFile);
                }
            }
        }
    }

    private static function template(string $filename): string
    {
        $path = resource_path('templates/' . $filename);

        if (! is_file($path)) {
            throw new RuntimeException(
                'Template Word tidak ditemukan: ' . $filename
            );
        }

        return $path;
    }

    private static function date(mixed $value): string
    {
        if (! $value) {
            return '-';
        }

        $timestamp = strtotime((string) $value);

        return $timestamp === false ? '-' : date('d-m-Y', $timestamp);
    }

    private static function dateRange(
        mixed $start,
        mixed $end
    ): string {
        if (! $start && ! $end) {
            return '-';
        }

        return self::date($start)
            . ' s.d. '
            . self::date($end);
    }

    private static function text(mixed $value): string
    {
        $text = trim((string) ($value ?? ''));

        return $text === '' ? '-' : $text;
    }
}
