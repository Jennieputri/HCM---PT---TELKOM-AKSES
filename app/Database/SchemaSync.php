<?php

namespace App\Database;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SchemaSync
{
    public static function table(string $tableName, array $columnDefinitions)
    {
        if (! Schema::hasTable($tableName))
        {
            self::createTable($tableName, $columnDefinitions);

            return;
        }

        self::addMissingColumns($tableName, $columnDefinitions);
    }

    private static function createTable(string $tableName, array $columnDefinitions)
    {
        Schema::create($tableName, function (Blueprint $table) use ($columnDefinitions)
        {
            foreach ($columnDefinitions as $defineColumn)
            {
                $defineColumn($table);
            }
        });
    }

    private static function addMissingColumns(string $tableName, array $columnDefinitions)
    {
        $existingColumns = Schema::getColumnListing($tableName);
        $missingColumns  = array_diff_key($columnDefinitions, array_flip($existingColumns));

        if ($missingColumns === [])
        {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($missingColumns)
        {
            foreach ($missingColumns as $defineColumn)
            {
                $defineColumn($table);
            }
        });
    }
}
