<?php

namespace App\Models\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait BuildsDataTableQuery
{
    private static function personLabel(string $employeeAlias, string $labelAlias)
    {
        return DB::raw("CONCAT({$employeeAlias}.name, ' (', {$employeeAlias}.nik, ')') as {$labelAlias}");
    }

    private static function addAuditColumns(Builder $query, string $table)
    {
        $query->leftJoin('tb_employee as creator', 'creator.id', '=', "{$table}.created_by")
            ->leftJoin('tb_employee as updater', 'updater.id', '=', "{$table}.updated_by")
            ->addSelect(
                self::personLabel('creator', 'created_by_label'),
                "{$table}.created_at",
                self::personLabel('updater', 'updated_by_label'),
                "{$table}.updated_at",
            );
    }

    private static function applyTextFilter(Builder $query, array $columns, ?string $keyword)
    {
        if ($keyword === null)
        {
            return;
        }

        $query->where(function ($filterQuery) use ($columns, $keyword)
        {
            foreach ($columns as $column)
            {
                $filterQuery->orWhere($column, 'like', '%'.$keyword.'%');
            }
        });
    }

    private static function applyExactFilter(Builder $query, string $column, $value)
    {
        if ($value !== null)
        {
            $query->where($column, $value);
        }
    }

    private static function applyDateRange(Builder $query, string $column, ?string $fromDate, ?string $toDate)
    {
        if ($fromDate !== null)
        {
            $query->whereDate($column, '>=', $fromDate);
        }

        if ($toDate !== null)
        {
            $query->whereDate($column, '<=', $toDate);
        }
    }

    private static function dataTableRows(Builder $query, int $totalRows, array $table)
    {
        $filteredRows = (clone $query)->count();

        $rows = $query->orderBy($table['orderColumn'], $table['orderDirection'])
            ->offset($table['start'])
            ->limit($table['length'])
            ->get();

        return compact('totalRows', 'filteredRows', 'rows');
    }
}
