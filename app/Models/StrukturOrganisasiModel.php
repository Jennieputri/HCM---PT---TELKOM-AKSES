<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class StrukturOrganisasiModel
{
    public static function getAll()
    {
        return DB::table('tb_struktur_organisasi')
            ->orderBy('id')
            ->get();
    }

    public static function findById($id)
    {
        return DB::table('tb_struktur_organisasi')
            ->where('id', $id)
            ->first();
    }
}
