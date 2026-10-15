<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdministratorModel
{
    public static function employeeList($search, $orderColumn, $orderDirection, $start, $length)
    {
        $query = DB::table('tb_employee')
            ->leftJoin('tb_role', 'tb_role.id', '=', 'tb_employee.role_id')
            ->select(
                'tb_employee.id',
                'tb_employee.nik',
                'tb_employee.name',
                'tb_employee.number_phone',
                'tb_employee.email',
                'tb_employee.address',
                'tb_employee.role_id',
                'tb_role.name as role_name',
                'tb_employee.status',
            );

        $totalRows = DB::table('tb_employee')->count();

        if ($search !== '')
        {
            $query->where(function ($searchQuery) use ($search)
            {
                $searchQuery->where('tb_employee.nik', 'like', '%'.$search.'%')
                    ->orWhere('tb_employee.name', 'like', '%'.$search.'%')
                    ->orWhere('tb_employee.email', 'like', '%'.$search.'%')
                    ->orWhere('tb_role.name', 'like', '%'.$search.'%');
            });
        }

        $filteredRows = (clone $query)->count();

        $rows = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        return compact('totalRows', 'filteredRows', 'rows');
    }

    public static function saveEmployee(array $employee, $actorId)
    {
        $employeeData = [
            'nik'          => $employee['nik'],
            'name'         => $employee['name'],
            'number_phone' => $employee['number_phone'] ?? null,
            'email'        => $employee['email']        ?? null,
            'address'      => $employee['address']      ?? null,
            'role_id'      => $employee['role_id'],
            'status'       => $employee['status'],
            'updated_by'   => $actorId,
            'updated_at'   => now(),
        ];

        if (! empty($employee['password']))
        {
            $employeeData['password'] = Hash::make($employee['password']);
        }

        if (empty($employee['id']))
        {
            $employeeData['created_by'] = $actorId;
            $employeeData['created_at'] = now();

            DB::table('tb_employee')->insert($employeeData);

            return;
        }

        DB::table('tb_employee')->where('id', $employee['id'])->update($employeeData);
    }

    public static function deleteEmployee($employeeId)
    {
        DB::table('tb_employee')->where('id', $employeeId)->delete();
    }

    public static function roleList($search, $orderColumn, $orderDirection, $start, $length)
    {
        $query = DB::table('tb_role')->select('id', 'name');

        $totalRows = DB::table('tb_role')->count();

        if ($search !== '')
        {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $filteredRows = (clone $query)->count();

        $rows = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        return compact('totalRows', 'filteredRows', 'rows');
    }

    public static function roleOptions($search, $page, $perPage)
    {
        $query = DB::table('tb_role')->select('id', 'name as text')->orderBy('name');

        if ($search !== '')
        {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $roles = $query->simplePaginate($perPage, ['*'], 'page', $page);

        return [
            'rows'    => $roles->items(),
            'hasMore' => $roles->hasMorePages(),
        ];
    }

    public static function saveRole(array $role, $actorId)
    {
        $roleData = [
            'name'       => $role['name'],
            'updated_by' => $actorId,
            'updated_at' => now(),
        ];

        if (empty($role['id']))
        {
            $roleData['created_by'] = $actorId;
            $roleData['created_at'] = now();

            DB::table('tb_role')->insert($roleData);

            return;
        }

        DB::table('tb_role')->where('id', $role['id'])->update($roleData);
    }

    public static function isRoleUsed($roleId)
    {
        return DB::table('tb_employee')->where('role_id', $roleId)->exists();
    }

    public static function deleteRole($roleId)
    {
        DB::table('tb_role')->where('id', $roleId)->delete();
    }

    public static function positionList($search, $orderColumn, $orderDirection, $start, $length)
    {
        $query = DB::table('tb_position')->select('id', 'name');

        $totalRows = DB::table('tb_position')->count();

        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $filteredRows = (clone $query)->count();

        $rows = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        return compact('totalRows', 'filteredRows', 'rows');
    }

    public static function savePosition(array $position, $actorId)
    {
        $positionData = [
            'name'       => $position['name'],
            'updated_by' => $actorId,
            'updated_at' => now(),
        ];

        if (empty($position['id'])) {
            $positionData['created_by'] = $actorId;
            $positionData['created_at'] = now();

            DB::table('tb_position')->insert($positionData);

            return;
        }

        DB::table('tb_position')
            ->where('id', $position['id'])
            ->update($positionData);
    }

    public static function deletePosition($positionId)
    {
        DB::table('tb_position')
            ->where('id', $positionId)
            ->delete();
    }

    
    public static function alatKerjaList($search, $orderColumn, $orderDirection, $start, $length)
    {
        $query = DB::table('tb_alat_kerja')->select(
            'id',
            'nik_karyawan',
            'kode_alat',
            'nama_alat',
            'tanggal_penyerahan',
            'kondisi',
            'status'
        );

        $totalRows = DB::table('tb_alat_kerja')->count();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('nik_karyawan', 'like', '%'.$search.'%')
                    ->orWhere('kode_alat', 'like', '%'.$search.'%')
                    ->orWhere('nama_alat', 'like', '%'.$search.'%')
                    ->orWhere('kondisi', 'like', '%'.$search.'%')
                    ->orWhere('status', 'like', '%'.$search.'%');
            });
        }

        $filteredRows = (clone $query)->count();

        $rows = $query->orderBy($orderColumn, $orderDirection)
            ->offset($start)
            ->limit($length)
            ->get();

        return compact('totalRows', 'filteredRows', 'rows');
    }

    public static function saveAlatKerja(array $alat, $actorId)
    {
        $alatData = [
            'nik_karyawan'      => $alat['nik_karyawan'],
            'kode_alat'         => $alat['kode_alat'],
            'nama_alat'         => $alat['nama_alat'],
            'tanggal_penyerahan' => $alat['tanggal_penyerahan'],
            'kondisi'            => $alat['kondisi'],
            'status'             => $alat['status'],
            'updated_by'         => $actorId,
            'updated_at'         => now(),
        ];

        if (empty($alat['id'])) {
            $alatData['created_by'] = $actorId;
            $alatData['created_at'] = now();

            DB::table('tb_alat_kerja')->insert($alatData);

            return;
        }

        DB::table('tb_alat_kerja')
            ->where('id', $alat['id'])
            ->update($alatData);
    }

    public static function deleteAlatKerja($alatId)
    {
        DB::table('tb_alat_kerja')
            ->where('id', $alatId)
            ->delete();
    }
}
