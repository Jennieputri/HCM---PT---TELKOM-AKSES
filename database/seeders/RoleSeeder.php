<?php

namespace Database\Seeders;

use App\Models\AuthModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([AuthModel::ROLE_USER, AuthModel::ROLE_HCM] as $roleName)
        {
            if (DB::table('tb_role')->where('name', $roleName)->doesntExist())
            {
                DB::table('tb_role')->insert([
                    'name'       => $roleName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
