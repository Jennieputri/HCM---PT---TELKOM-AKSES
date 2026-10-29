<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class HomeModel
{
    private const MAX_KECAMATAN_BARS = 8;

    public static function dashboardSummary(bool $includeAdministration)
    {
        return [
            'employees'      => $includeAdministration ? self::employeeSummary() : null,
            'alat_kerja'     => self::alatKerjaSummary(),
            'sto'            => self::stoSummary(),
            'administration' => $includeAdministration ? self::administrationSummary() : null,
        ];
    }

    private static function employeeSummary()
    {
        $totalsByStatus = DB::table('tb_employee')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'active'   => (int) ($totalsByStatus[AuthModel::STATUS_ACTIVE] ?? 0),
            'pending'  => (int) ($totalsByStatus[AuthModel::STATUS_PENDING] ?? 0),
            'rejected' => (int) ($totalsByStatus[AuthModel::STATUS_REJECTED] ?? 0),
            'inactive' => (int) ($totalsByStatus[AuthModel::STATUS_INACTIVE] ?? 0),
            'total'    => (int) $totalsByStatus->sum(),
        ];
    }

    private static function alatKerjaSummary()
    {
        $summary = DB::table('tb_alat_kerja')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN kondisi = 'Baik' THEN 1 ELSE 0 END) as good_condition")
            ->selectRaw("SUM(CASE WHEN status = 'Sudah Dikembalikan' THEN 1 ELSE 0 END) as returned")
            ->first();

        return [
            'total'          => (int) $summary->total,
            'good_condition' => (int) $summary->good_condition,
            'returned'       => (int) $summary->returned,
            'borrowed'       => (int) $summary->total - (int) $summary->returned,
        ];
    }

    private static function stoSummary()
    {
        $totalsByStatus = DB::table('tb_sto')
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalsByKecamatan = DB::table('tb_sto')
            ->select(DB::raw("COALESCE(NULLIF(kecamatan, ''), 'Tanpa kecamatan') as kecamatan"), DB::raw('COUNT(*) as total'))
            ->groupBy('kecamatan')
            ->orderByDesc('total')
            ->orderBy('kecamatan')
            ->limit(self::MAX_KECAMATAN_BARS)
            ->get();

        return [
            'total'        => (int) $totalsByStatus->sum(),
            'active'       => (int) ($totalsByStatus['Aktif'] ?? 0),
            'kecamatan'    => (int) DB::table('tb_sto')->distinct()->count('kecamatan'),
            'by_kecamatan' => $totalsByKecamatan,
        ];
    }

    private static function administrationSummary()
    {
        return [
            'roles'             => DB::table('tb_role')->count(),
            'positions'         => DB::table('tb_position')->count(),
            'pending_approvals' => DB::table('tb_employee')->where('status', AuthModel::STATUS_PENDING)->count(),
        ];
    }
}
