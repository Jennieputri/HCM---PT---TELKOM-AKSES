<?php

namespace App\Http\Controllers;

class AdministratorController extends Controller
{
    public function employee()
    {
        return view('administrator.employee');
    }

    public function role()
    {
        return view('administrator.role');
    }

    public function position()
    {
        return view('administrator.position');
    }

    public function absen()
    {
        return view('administrator.absen');
    }

    public function alatKerja()
    {
        return view('administrator.alat-kerja');
    }

    public function sto()
    {
        $data = \App\Models\Sto::orderBy('kode_sto')->get();

        return view('administrator.sto', compact('data'));
    }

}
