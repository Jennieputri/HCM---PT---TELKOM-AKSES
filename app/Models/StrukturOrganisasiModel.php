<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StrukturOrganisasiModel
{
    private const PM_PATTERN = '/\bPM\b|Project Manager/i';

    private const BRANCH_MANAGER_PATTERN = '/Mgr Branch Palangkaraya|Manager Branch Palangkaraya|Branch Manager Palangkaraya/i';

    private const PM_TEAMS = ['Drafter', 'Surveyor'];

    private const BRANCH_TEAMS = ['Officer 1', 'Officer 3', 'Korlap', 'Staff'];

    public static function chart()
    {
        $visibleMembers = DB::table('tb_struktur_organisasi')
            ->select('id', 'nama', 'nama_jabatan', 'level_jabatan', 'sub_unit')
            ->orderBy('id')
            ->get()
            ->reject(fn ($member) => self::isTechnician($member))
            ->values();

        return [
            'totalMembers'   => $visibleMembers->count(),
            'projectManager' => self::matchingPosition($visibleMembers, self::PM_PATTERN),
            'branchManager'  => self::matchingPosition($visibleMembers, self::BRANCH_MANAGER_PATTERN),
            'pmTeams'        => self::teams($visibleMembers, self::PM_TEAMS),
            'branchTeams'    => self::teams($visibleMembers, self::BRANCH_TEAMS),
        ];
    }

    private static function isTechnician(object $member)
    {
        return stripos(trim((string) $member->nama_jabatan), 'Teknisi')     === 0
            || strcasecmp(trim((string) $member->level_jabatan), 'TEKNISI') === 0;
    }

    private static function matchingPosition(Collection $members, string $positionPattern)
    {
        return $members->filter(fn ($member) => preg_match($positionPattern, (string) $member->nama_jabatan))->values();
    }

    private static function teams(Collection $members, array $teamNames)
    {
        return array_map(fn ($teamName) => [
            'nama'    => $teamName,
            'anggota' => $members->filter(fn ($member) => stripos((string) $member->nama_jabatan, $teamName) !== false)->values(),
        ], $teamNames);
    }
}
