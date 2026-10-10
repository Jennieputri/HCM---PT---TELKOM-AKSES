<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeModel extends Model
{
    protected $table = 'tb_employee';

    public static function totalActiveEmployees()
    {
        return self::where('status', AuthModel::STATUS_ACTIVE)->count();
    }
}
