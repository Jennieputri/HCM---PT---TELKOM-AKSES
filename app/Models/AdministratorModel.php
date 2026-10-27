<?php

namespace App\Models;

use App\Models\Concerns\BuildsDataTableQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdministratorModel
{
    use BuildsDataTableQuery;

    public static function employeeList(array $table, array $filters)
    {
        $query = DB::table('tb_employee')
            ->leftJoin('tb_role', 'tb_role.id', '=', 'tb_employee.role_id')
            ->select(
                'tb_employee.id',
                'tb_employee.nik',
                'tb_employee.name',
                self::personLabel('tb_employee', 'employee_label'),
                'tb_employee.number_phone',
                'tb_employee.email',
                'tb_employee.address',
                'tb_employee.role_id',
                'tb_role.name as role_name',
                'tb_employee.status',
            );

        self::addAuditColumns($query, 'tb_employee');

        $totalRows = DB::table('tb_employee')->count();

        if ($table['search'] !== '')
        {
            $search = '%'.$table['search'].'%';

            $query->where(function ($searchQuery) use ($search)
            {
                $searchQuery->where('tb_employee.nik', 'like', $search)
                    ->orWhere('tb_employee.name', 'like', $search)
                    ->orWhere('tb_employee.email', 'like', $search)
                    ->orWhere('tb_role.name', 'like', $search);
            });
        }

        self::applyTextFilter($query, ['tb_employee.name', 'tb_employee.nik'], $filters['karyawan'] ?? null);
        self::applyTextFilter($query, ['tb_employee.email'], $filters['email'] ?? null);
        self::applyTextFilter($query, ['tb_employee.number_phone'], $filters['number_phone'] ?? null);
        self::applyExactFilter($query, 'tb_employee.role_id', $filters['role_id'] ?? null);
        self::applyExactFilter($query, 'tb_employee.status', $filters['status'] ?? null);

        return self::dataTableRows($query, $totalRows, $table);
    }

    public static function approvalList(array $table, array $filters)
    {
        $registrationStatuses = [AuthModel::STATUS_PENDING, AuthModel::STATUS_REJECTED];

        $query = DB::table('tb_employee')
            ->leftJoin('tb_role', 'tb_role.id', '=', 'tb_employee.role_id')
            ->select(
                'tb_employee.id',
                'tb_employee.nik',
                'tb_employee.name',
                self::personLabel('tb_employee', 'employee_label'),
                'tb_role.name as role_name',
                'tb_employee.status',
            )
            ->whereIn('tb_employee.status', $registrationStatuses);

        self::addAuditColumns($query, 'tb_employee');

        $totalRows = DB::table('tb_employee')->whereIn('status', $registrationStatuses)->count();

        if ($table['search'] !== '')
        {
            self::applyTextFilter($query, ['tb_employee.name', 'tb_employee.nik'], $table['search']);
        }

        self::applyTextFilter($query, ['tb_employee.name', 'tb_employee.nik'], $filters['pendaftar'] ?? null);
        self::applyDateRange($query, 'tb_employee.created_at', $filters['registered_from'] ?? null, $filters['registered_to'] ?? null);
        self::applyExactFilter($query, 'tb_employee.status', $filters['status'] ?? null);

        return self::dataTableRows($query, $totalRows, $table);
    }

    public static function pendingApprovalCount()
    {
        return DB::table('tb_employee')->where('status', AuthModel::STATUS_PENDING)->count();
    }

    public static function changeRegistrationStatus($employeeId, array $allowedCurrentStatuses, $newStatus, $actorId)
    {
        return DB::table('tb_employee')
            ->where('id', $employeeId)
            ->whereIn('status', $allowedCurrentStatuses)
            ->update([
                'status'     => $newStatus,
                'updated_by' => $actorId,
                'updated_at' => now(),
            ]);
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

    public static function roleList(array $table, array $filters)
    {
        $query = DB::table('tb_role')->select('tb_role.id', 'tb_role.name');

        self::addAuditColumns($query, 'tb_role');

        $totalRows = DB::table('tb_role')->count();

        if ($table['search'] !== '')
        {
            $query->where('tb_role.name', 'like', '%'.$table['search'].'%');
        }

        self::applyTextFilter($query, ['tb_role.name'], $filters['name'] ?? null);
        self::applyTextFilter($query, ['creator.name', 'creator.nik'], $filters['created_by'] ?? null);
        self::applyDateRange($query, 'tb_role.updated_at', $filters['updated_from'] ?? null, $filters['updated_to'] ?? null);

        return self::dataTableRows($query, $totalRows, $table);
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

    public static function positionList(array $table, array $filters)
    {
        $query = DB::table('tb_position')->select('tb_position.id', 'tb_position.name');

        self::addAuditColumns($query, 'tb_position');

        $totalRows = DB::table('tb_position')->count();

        if ($table['search'] !== '')
        {
            $query->where('tb_position.name', 'like', '%'.$table['search'].'%');
        }

        self::applyTextFilter($query, ['tb_position.name'], $filters['name'] ?? null);
        self::applyTextFilter($query, ['creator.name', 'creator.nik'], $filters['created_by'] ?? null);
        self::applyDateRange($query, 'tb_position.updated_at', $filters['updated_from'] ?? null, $filters['updated_to'] ?? null);

        return self::dataTableRows($query, $totalRows, $table);
    }

    public static function savePosition(array $position, $actorId)
    {
        $positionData = [
            'name'       => $position['name'],
            'updated_by' => $actorId,
            'updated_at' => now(),
        ];

        if (empty($position['id']))
        {
            $positionData['created_by'] = $actorId;
            $positionData['created_at'] = now();

            DB::table('tb_position')->insert($positionData);

            return;
        }

        DB::table('tb_position')->where('id', $position['id'])->update($positionData);
    }

    public static function deletePosition($positionId)
    {
        DB::table('tb_position')->where('id', $positionId)->delete();
    }
}
