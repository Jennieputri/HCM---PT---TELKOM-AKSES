<?php

namespace App\Models;

use App\Models\Concerns\BuildsDataTableQuery;
use Illuminate\Support\Facades\DB;

class AbsenModel
{
    use BuildsDataTableQuery;

    public static function absenList(array $table, array $filters)
    {
        $query = DB::table('tb_absen')
            ->leftJoin('tb_employee', 'tb_employee.nik', '=', 'tb_absen.nik_karyawan')
            ->select(
                'tb_absen.id',
                'tb_absen.nik_karyawan',
                self::personLabel('tb_employee', 'karyawan_label'),
                'tb_absen.jenis',
                'tb_absen.tanggal_mulai',
                'tb_absen.tanggal_selesai',
                'tb_absen.nomor_dokumen',
                'tb_absen.status',
                'tb_absen.keterangan',
                'tb_absen.keterangan_admin',
                'tb_absen.pdf_path',
                'tb_absen.surat_dokter_path',
                'tb_absen.created_at',
                'tb_absen.updated_at'
            );

        $totalRows = DB::table('tb_absen')->count();

        $keyword = trim((string) $table['search']);

        if ($keyword !== '')
        {
            $escapedKeyword = str_replace(['%', '_'], ['\%', '\_'], $keyword);

            $query->where(function ($subQuery) use ($escapedKeyword)
            {
                $subQuery->where('tb_employee.name', 'like', "%{$escapedKeyword}%")
                    ->orWhere('tb_absen.nik_karyawan', 'like', "%{$escapedKeyword}%")
                    ->orWhere('tb_absen.nomor_dokumen', 'like', "%{$escapedKeyword}%")
                    ->orWhere('tb_absen.jenis', 'like', "%{$escapedKeyword}%")
                    ->orWhere('tb_absen.status', 'like', "%{$escapedKeyword}%");
            });
        }

        self::applyTextFilter($query, ['tb_employee.name', 'tb_absen.nik_karyawan'], $filters['karyawan'] ?? null);
        self::applyExactFilter($query, 'tb_absen.jenis', $filters['jenis'] ?? null);
        self::applyDateRange($query, 'tb_absen.tanggal_mulai', $filters['tanggal_mulai'] ?? null, null);
        self::applyDateRange($query, 'tb_absen.tanggal_selesai', null, $filters['tanggal_selesai'] ?? null);
        self::applyExactFilter($query, 'tb_absen.status', $filters['status'] ?? null);

        return self::dataTableRows($query, $totalRows, $table);
    }

    public static function findExistingAbsen(int $nikKaryawan, string $jenis, string $tanggalMulai, string $tanggalSelesai, ?int $excludeId = null)
    {
        $query = DB::table('tb_absen')
            ->where('nik_karyawan', $nikKaryawan)
            ->where('jenis', $jenis)
            ->where(function ($dateQuery) use ($tanggalMulai, $tanggalSelesai)
            {
                $dateQuery->whereBetween('tanggal_mulai', [$tanggalMulai, $tanggalSelesai])
                    ->orWhereBetween('tanggal_selesai', [$tanggalMulai, $tanggalSelesai])
                    ->orWhere(function ($enclosedQuery) use ($tanggalMulai, $tanggalSelesai)
                    {
                        $enclosedQuery->where('tanggal_mulai', '<=', $tanggalMulai)
                            ->where('tanggal_selesai', '>=', $tanggalSelesai);
                    });
            });

        if ($excludeId !== null)
        {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }

    public static function generateNomorDokumen(string $jenis)
    {
        $sequenceNumber = DB::table('tb_absen')
            ->where('jenis', $jenis)
            ->count() + 1;

        $prefixMap = [
            'SPPD'  => 'SPPD',
            'Sakit' => 'SKT',
            'Cuti'  => 'CTI',
        ];

        $prefix    = $prefixMap[$jenis] ?? 'DOC';
        $timestamp = now()->format('YmdHis');

        return "{$prefix}-{$timestamp}-{$sequenceNumber}";
    }

    public static function documentNumberExists(string $nomorDokumen, ?int $excludeId = null)
    {
        $query = DB::table('tb_absen')->where('nomor_dokumen', $nomorDokumen);

        if ($excludeId !== null)
        {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public static function createAbsen(array $absenData)
    {
        DB::table('tb_absen')->insert($absenData);
    }

    public static function findAbsenById(int $id)
    {
        return DB::table('tb_absen')
            ->where('id', $id)
            ->where('status', 'Menunggu')
            ->first();
    }

    public static function approveAbsen(int $id, string $keteranganAdmin)
    {
        return DB::table('tb_absen')
            ->where('id', $id)
            ->where('status', 'Menunggu')
            ->update([
                'status'           => 'Disetujui',
                'keterangan_admin' => $keteranganAdmin,
                'updated_at'       => now(),
            ]);
    }

    public static function rejectAbsen(int $id, string $keteranganAdmin)
    {
        return DB::table('tb_absen')
            ->where('id', $id)
            ->where('status', 'Menunggu')
            ->update([
                'status'           => 'Ditolak',
                'keterangan_admin' => $keteranganAdmin,
                'updated_at'       => now(),
            ]);
    }

    public static function deleteAbsen(int $id)
    {
        return DB::table('tb_absen')
            ->where('id', $id)
            ->where('status', 'Menunggu')
            ->delete();
    }

    public static function updateAbsen(int $id, int $nikKaryawan, array $values)
    {
        return DB::table('tb_absen')
            ->where('id', $id)
            ->where('nik_karyawan', $nikKaryawan)
            ->where('status', 'Menunggu')
            ->update($values);
    }
}
