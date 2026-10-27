<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthModel extends Authenticatable
{
    public const STATUS_INACTIVE = 0;

    public const STATUS_ACTIVE = 1;

    public const STATUS_PENDING = 2;

    public const STATUS_REJECTED = 3;

    public const ROLE_USER = 'User';

    public const ROLE_HCM = 'HCM';

    protected $table = 'tb_employee';

    protected $fillable = [
        'role_id',
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
        return self::select('id', 'role_id', 'nik', 'name', 'password', 'status')
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
            'role_id'  => DB::table('tb_role')->where('name', self::ROLE_USER)->value('id'),
            'nik'      => $nik,
            'name'     => $name,
            'password' => Hash::make($password),
            'status'   => self::STATUS_PENDING,
        ]);
    }

    public function isHcm()
    {
        return once(fn () => DB::table('tb_role')->where('id', $this->role_id)->value('name') === self::ROLE_HCM);
    }
}
