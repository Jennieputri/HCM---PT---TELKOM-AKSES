<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class AuthModel extends Authenticatable
{
    public const STATUS_ACTIVE = 1;

    protected $table = 'tb_employee';

    protected $fillable = [
        'nik',
        'name',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public static function findByNik($nik)
    {
        return self::select('id', 'nik', 'name', 'password', 'status')
            ->where('nik', $nik)
            ->first();
    }

    public static function isNikRegistered($nik)
    {
        return self::where('nik', $nik)->exists();
    }

    public static function register($nik, $name, $password)
    {
        return self::create([
            'nik'      => $nik,
            'name'     => $name,
            'password' => Hash::make($password),
            'status'   => self::STATUS_ACTIVE,
        ]);
    }
}
