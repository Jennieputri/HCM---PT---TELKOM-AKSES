<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sto extends Model
{
    protected $table = 'tb_sto';

    protected $fillable = [
        'kode_sto',
        'nama_sto',
        'alamat',
        'kecamatan',
        'latitude',
        'longitude',
        'status',
    ];
}
