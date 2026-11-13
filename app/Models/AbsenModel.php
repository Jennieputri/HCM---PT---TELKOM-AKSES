<?php

namespace App\Models;

use App\Models\Concerns\BuildsDataTableQuery;
use App\Services\AbsenPdfGenerator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AbsenModel
{
    use BuildsDataTableQuery;

    public const STATUS_PENDING = 'Menunggu';

    public const STATUS_APPROVED = 'Disetujui';

    public const STATUS_REJECTED = 'Ditolak';

    private const JENIS_SPECIFIC_COLUMNS = [
        'jenis_cuti',
        'lama_cuti',
        'band',
        'jabatan',
        'unit_kerja',
        'alamat_selama_cuti',
        'telepon_cuti',
        'perjalanan_berangkat_mulai',
        'perjalanan_berangkat_selesai',
        'perjalanan_pulang_mulai',
        'perjalanan_pulang_selesai',
        'tanggal_surat',
        'lokasi_pekerjaan',
        'sppd_karyawan',
        'surat_dokter_path',
    ];

    private const PDF_COLUMNS = [
        'id',
        'nik_karyawan',
        'jenis',
        'status',
        'disetujui_oleh',
        'tanggal_mulai',
        'tanggal_selesai',
        'nomor_dokumen',
        'keterangan',
        'surat_dokter_path',
        'updated_at',
        'jenis_cuti',
        'lama_cuti',
        'band',
        'jabatan',
        'unit_kerja',
        'alamat_selama_cuti',
        'telepon_cuti',
        'perjalanan_berangkat_mulai',
        'perjalanan_berangkat_selesai',
        'perjalanan_pulang_mulai',
        'perjalanan_pulang_selesai',
        'tanggal_surat',
        'lokasi_pekerjaan',
        'sppd_karyawan',
    ];

    public static function list(array $table, array $filters, ?int $ownerNik)
    {
        $query = DB::table('tb_absen')
            ->join('tb_employee', 'tb_employee.nik', '=', 'tb_absen.nik_karyawan')
            ->select(
                'tb_absen.id',
                'tb_absen.nik_karyawan',
                'tb_employee.name as nama_karyawan',
                'tb_absen.jenis',
                'tb_absen.tanggal_mulai',
                'tb_absen.tanggal_selesai',
                'tb_absen.nomor_dokumen',
                'tb_absen.status',
                'tb_absen.keterangan',
                'tb_absen.keterangan_admin',
                'tb_absen.created_at',
                'tb_absen.jenis_cuti',
                'tb_absen.lama_cuti',
                'tb_absen.band',
                'tb_absen.jabatan',
                'tb_absen.unit_kerja',
                'tb_absen.alamat_selama_cuti',
                'tb_absen.telepon_cuti',
                'tb_absen.perjalanan_berangkat_mulai',
                'tb_absen.perjalanan_berangkat_selesai',
                'tb_absen.perjalanan_pulang_mulai',
                'tb_absen.perjalanan_pulang_selesai',
                'tb_absen.tanggal_surat',
                'tb_absen.lokasi_pekerjaan',
                'tb_absen.sppd_karyawan',
            );

        if ($ownerNik !== null)
        {
            $query->where('tb_absen.nik_karyawan', $ownerNik);
        }

        $totalRows = (clone $query)->count();

        if ($table['search'] !== '')
        {
            $search = '%'.$table['search'].'%';

            $query->where(function ($searchQuery) use ($search)
            {
                $searchQuery->where('tb_employee.name', 'like', $search)
                    ->orWhere('tb_absen.nik_karyawan', 'like', $search)
                    ->orWhere('tb_absen.nomor_dokumen', 'like', $search)
                    ->orWhere('tb_absen.jenis', 'like', $search)
                    ->orWhere('tb_absen.status', 'like', $search);
            });
        }

        self::applyTextFilter($query, ['tb_employee.name', 'tb_absen.nik_karyawan'], $filters['karyawan'] ?? null);
        self::applyTextFilter($query, ['tb_absen.nomor_dokumen'], $filters['nomor_dokumen'] ?? null);
        self::applyExactFilter($query, 'tb_absen.jenis', $filters['jenis'] ?? null);
        self::applyExactFilter($query, 'tb_absen.status', $filters['status'] ?? null);
        self::applyDateRange($query, 'tb_absen.tanggal_mulai', $filters['tanggal_mulai'] ?? null, null);
        self::applyDateRange($query, 'tb_absen.tanggal_selesai', null, $filters['tanggal_selesai'] ?? null);

        return self::dataTableRows($query, $totalRows, $table);
    }

    public static function nextNomorDokumen()
    {
        $prefix = 'ABS-'.now()->format('Ymd').'-';

        $sequence = DB::table('tb_absen')
            ->where('nomor_dokumen', 'like', $prefix.'%')
            ->count() + 1;

        do
        {
            $nomorDokumen = $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
            $sequence++;
        }
        while (DB::table('tb_absen')->where('nomor_dokumen', $nomorDokumen)->exists());

        return $nomorDokumen;
    }

    public static function findOwned(int $id, int $nik)
    {
        return DB::table('tb_absen')
            ->select('id', 'jenis', 'status', 'nomor_dokumen', 'surat_dokter_path')
            ->where('id', $id)
            ->where('nik_karyawan', $nik)
            ->first();
    }

    public static function save(array $pengajuan, int $nik, ?object $existing, array $sppdKaryawan, ?string $suratDokterPath)
    {
        $nomorDokumen = $existing ? $existing->nomor_dokumen : self::nextNomorDokumen();

        $absenData = array_merge(self::columnsForJenis($pengajuan, $sppdKaryawan, $suratDokterPath), [
            'nik_karyawan'     => $nik,
            'jenis'            => $pengajuan['jenis'],
            'nomor_dokumen'    => $nomorDokumen,
            'status'           => self::STATUS_PENDING,
            'keterangan_admin' => null,
            'disetujui_oleh'   => null,
            'updated_at'       => now(),
        ]);

        if ($existing)
        {
            DB::table('tb_absen')
                ->where('id', $existing->id)
                ->where('status', self::STATUS_PENDING)
                ->update($absenData);
        }
        else
        {
            DB::table('tb_absen')->insert($absenData + ['created_at' => now()]);
        }

        return $nomorDokumen;
    }

    public static function delete(int $id, int $nik)
    {
        DB::table('tb_absen')
            ->where('id', $id)
            ->where('nik_karyawan', $nik)
            ->where('status', self::STATUS_PENDING)
            ->delete();
    }

    public static function approve(int $id, string $keteranganAdmin, int $approverNik)
    {
        return DB::table('tb_absen')
            ->where('id', $id)
            ->where('status', self::STATUS_PENDING)
            ->update([
                'status'           => self::STATUS_APPROVED,
                'keterangan_admin' => $keteranganAdmin,
                'disetujui_oleh'   => $approverNik,
                'updated_at'       => now(),
            ]) > 0;
    }

    public static function reject(int $id, string $keteranganAdmin)
    {
        return DB::table('tb_absen')
            ->where('id', $id)
            ->where('status', self::STATUS_PENDING)
            ->update([
                'status'           => self::STATUS_REJECTED,
                'keterangan_admin' => $keteranganAdmin,
                'updated_at'       => now(),
            ]) > 0;
    }

    public static function findForDownload(int $id)
    {
        return DB::table('tb_absen')
            ->select(self::PDF_COLUMNS)
            ->where('id', $id)
            ->first();
    }

    public static function buildPdf(object $absen)
    {
        $karyawan = self::employee($absen->nik_karyawan);

        if (! $karyawan)
        {
            throw new RuntimeException('Data karyawan tidak ditemukan.');
        }

        return match ($absen->jenis)
        {
            'Cuti'  => AbsenPdfGenerator::cuti($absen, $karyawan, self::branchManager(), self::approver($absen), self::leaveBalance($absen)),
            'SPPD'  => AbsenPdfGenerator::sppd($absen, json_decode((string) $absen->sppd_karyawan) ?: [], self::branchManager()),
            'Sakit' => AbsenPdfGenerator::sakit($absen, $karyawan, self::approver($absen)),
            default => throw new RuntimeException('Jenis pengajuan tidak dikenali.'),
        };
    }

    private static function columnsForJenis(array $pengajuan, array $sppdKaryawan, ?string $suratDokterPath)
    {
        $jenisColumns = match ($pengajuan['jenis'])
        {
            'Cuti' => [
                'tanggal_mulai'                => $pengajuan['cuti_tanggal_mulai'],
                'tanggal_selesai'              => $pengajuan['cuti_tanggal_selesai'],
                'keterangan'                   => $pengajuan['alasan_cuti'],
                'jenis_cuti'                   => $pengajuan['jenis_cuti'],
                'lama_cuti'                    => $pengajuan['lama_cuti'],
                'band'                         => $pengajuan['band']                         ?? null,
                'jabatan'                      => $pengajuan['jabatan']                      ?? null,
                'unit_kerja'                   => $pengajuan['unit_kerja']                   ?? null,
                'alamat_selama_cuti'           => $pengajuan['alamat_selama_cuti'],
                'telepon_cuti'                 => $pengajuan['telepon_cuti'],
                'perjalanan_berangkat_mulai'   => $pengajuan['perjalanan_berangkat_mulai']   ?? null,
                'perjalanan_berangkat_selesai' => $pengajuan['perjalanan_berangkat_selesai'] ?? null,
                'perjalanan_pulang_mulai'      => $pengajuan['perjalanan_pulang_mulai']      ?? null,
                'perjalanan_pulang_selesai'    => $pengajuan['perjalanan_pulang_selesai']    ?? null,
            ],
            'SPPD' => [
                'tanggal_mulai'    => $pengajuan['sppd_tanggal_mulai'],
                'tanggal_selesai'  => $pengajuan['sppd_tanggal_selesai'],
                'keterangan'       => $pengajuan['perihal_pekerjaan'],
                'tanggal_surat'    => $pengajuan['tanggal_surat'],
                'lokasi_pekerjaan' => $pengajuan['lokasi_pekerjaan'],
                'sppd_karyawan'    => json_encode($sppdKaryawan),
            ],
            default => [
                'tanggal_mulai'     => $pengajuan['tanggal_mulai'],
                'tanggal_selesai'   => $pengajuan['tanggal_selesai'],
                'keterangan'        => $pengajuan['keterangan'],
                'surat_dokter_path' => $suratDokterPath,
            ],
        };

        return array_merge(array_fill_keys(self::JENIS_SPECIFIC_COLUMNS, null), $jenisColumns);
    }

    private static function employee($nik)
    {
        return DB::table('tb_employee')
            ->select('nik', 'name')
            ->where('nik', $nik)
            ->first();
    }

    private static function approver(object $absen)
    {
        return self::employee($absen->disetujui_oleh) ?? (object) ['nik' => null, 'name' => null];
    }

    private static function branchManager()
    {
        $manager = DB::table('tb_struktur_organisasi')
            ->select('nama', 'nama_jabatan')
            ->where('level_jabatan', 'MGR BRANCH')
            ->orderBy('id')
            ->first();

        return (object) [
            'nama'    => $manager->nama         ?? null,
            'jabatan' => $manager->nama_jabatan ?? null,
            'nik'     => config('absen.nik_manager'),
        ];
    }

    private static function leaveBalance(object $absen)
    {
        $usedAnnualLeave = (int) DB::table('tb_absen')
            ->where('nik_karyawan', $absen->nik_karyawan)
            ->where('jenis', 'Cuti')
            ->where('jenis_cuti', 'Tahunan')
            ->where('status', self::STATUS_APPROVED)
            ->where('id', '!=', $absen->id)
            ->whereYear('tanggal_mulai', date('Y', strtotime($absen->tanggal_mulai)))
            ->where('tanggal_mulai', '<', $absen->tanggal_mulai)
            ->sum('lama_cuti');

        $balanceBefore = max((int) config('absen.jatah_cuti_tahunan') - $usedAnnualLeave, 0);
        $usedThisTime  = $absen->jenis_cuti === 'Tahunan' ? (int) $absen->lama_cuti : 0;

        return [
            'saldo' => $balanceBefore,
            'sisa'  => max($balanceBefore - $usedThisTime, 0),
        ];
    }
}
