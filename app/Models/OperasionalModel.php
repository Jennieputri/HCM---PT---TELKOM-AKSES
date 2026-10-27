<?php

namespace App\Models;

use App\Models\Concerns\BuildsDataTableQuery;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class OperasionalModel
{
    use BuildsDataTableQuery;

    private const STO_DETAIL_COLUMNS = ['id', 'kode_sto', 'nama_sto', 'alamat', 'kecamatan', 'latitude', 'longitude', 'status', 'created_at', 'updated_at'];

    public static function alatKerjaList(array $table, array $filters)
    {
        $query = DB::table('tb_alat_kerja')
            ->leftJoin('tb_employee as holder', 'holder.nik', '=', 'tb_alat_kerja.nik_karyawan')
            ->select(
                'tb_alat_kerja.id',
                DB::raw("CONCAT(COALESCE(holder.name, 'Karyawan tidak ditemukan'), ' (', tb_alat_kerja.nik_karyawan, ')') as karyawan"),
                'tb_alat_kerja.kode_alat',
                'tb_alat_kerja.nama_alat',
                'tb_alat_kerja.tanggal_penyerahan',
                'tb_alat_kerja.kondisi',
                'tb_alat_kerja.status',
            );

        self::addAuditColumns($query, 'tb_alat_kerja');

        $totalRows = DB::table('tb_alat_kerja')->count();

        if ($table['search'] !== '')
        {
            $search = '%'.$table['search'].'%';

            $query->where(function ($searchQuery) use ($search)
            {
                $searchQuery->where('holder.name', 'like', $search)
                    ->orWhere('tb_alat_kerja.nik_karyawan', 'like', $search)
                    ->orWhere('tb_alat_kerja.kode_alat', 'like', $search)
                    ->orWhere('tb_alat_kerja.nama_alat', 'like', $search);
            });
        }

        self::applyTextFilter($query, ['holder.name', 'tb_alat_kerja.nik_karyawan'], $filters['karyawan'] ?? null);
        self::applyTextFilter($query, ['tb_alat_kerja.kode_alat'], $filters['kode_alat'] ?? null);
        self::applyTextFilter($query, ['tb_alat_kerja.nama_alat'], $filters['nama_alat'] ?? null);
        self::applyExactFilter($query, 'tb_alat_kerja.kondisi', $filters['kondisi'] ?? null);
        self::applyExactFilter($query, 'tb_alat_kerja.status', $filters['status'] ?? null);
        self::applyDateRange($query, 'tb_alat_kerja.tanggal_penyerahan', $filters['tanggal_from'] ?? null, $filters['tanggal_to'] ?? null);

        return self::dataTableRows($query, $totalRows, $table);
    }

    public static function saveAlatKerja(array $alatKerja, $actorId)
    {
        $alatKerjaData = [
            'nik_karyawan'       => $alatKerja['nik_karyawan'],
            'kode_alat'          => $alatKerja['kode_alat'],
            'nama_alat'          => $alatKerja['nama_alat'],
            'tanggal_penyerahan' => $alatKerja['tanggal_penyerahan'],
            'kondisi'            => $alatKerja['kondisi'],
            'status'             => $alatKerja['status'],
            'updated_by'         => $actorId,
            'updated_at'         => now(),
        ];

        if (empty($alatKerja['id']))
        {
            $alatKerjaData['created_by'] = $actorId;
            $alatKerjaData['created_at'] = now();

            DB::table('tb_alat_kerja')->insert($alatKerjaData);

            return;
        }

        DB::table('tb_alat_kerja')->where('id', $alatKerja['id'])->update($alatKerjaData);
    }

    public static function deleteAlatKerja($alatKerjaId)
    {
        DB::table('tb_alat_kerja')->where('id', $alatKerjaId)->delete();
    }

    public static function stoList(array $table, array $filters)
    {
        $query = DB::table('tb_sto')->select(self::STO_DETAIL_COLUMNS);

        $totalRows = DB::table('tb_sto')->count();

        if ($table['search'] !== '')
        {
            $search = '%'.$table['search'].'%';

            $query->where(function ($searchQuery) use ($search)
            {
                $searchQuery->where('kode_sto', 'like', $search)
                    ->orWhere('nama_sto', 'like', $search)
                    ->orWhere('alamat', 'like', $search)
                    ->orWhere('kecamatan', 'like', $search);
            });
        }

        self::applyStoFilters($query, $filters);

        return self::dataTableRows($query, $totalRows, $table);
    }

    public static function stoMarkers(array $filters, $maxMarkers)
    {
        $query = DB::table('tb_sto')->select(self::STO_DETAIL_COLUMNS);

        self::applyStoFilters($query, $filters);

        return $query->orderBy('kode_sto')->limit($maxMarkers)->get();
    }

    private static function applyStoFilters(Builder $query, array $filters)
    {
        self::applyTextFilter($query, ['kode_sto'], $filters['kode_sto'] ?? null);
        self::applyTextFilter($query, ['nama_sto'], $filters['nama_sto'] ?? null);
        self::applyTextFilter($query, ['kecamatan'], $filters['kecamatan'] ?? null);
        self::applyTextFilter($query, ['alamat'], $filters['alamat'] ?? null);
        self::applyTextFilter($query, ['latitude', 'longitude'], $filters['koordinat'] ?? null);
        self::applyExactFilter($query, 'status', $filters['status'] ?? null);
    }

    public static function saveSto(array $sto)
    {
        $stoData = [
            'kode_sto'   => $sto['kode_sto'],
            'nama_sto'   => $sto['nama_sto'],
            'alamat'     => $sto['alamat'],
            'kecamatan'  => $sto['kecamatan'] ?? null,
            'latitude'   => $sto['latitude'],
            'longitude'  => $sto['longitude'],
            'status'     => $sto['status'],
            'updated_at' => now(),
        ];

        if (empty($sto['id']))
        {
            $stoData['created_at'] = now();

            DB::table('tb_sto')->insert($stoData);

            return;
        }

        DB::table('tb_sto')->where('id', $sto['id'])->update($stoData);
    }

    public static function deleteSto($stoId)
    {
        DB::table('tb_sto')->where('id', $stoId)->delete();
    }
}
