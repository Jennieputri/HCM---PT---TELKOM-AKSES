<?php

namespace App\Http\Controllers;

class OperasionalController extends Controller
{
    public function absen()
    {
        return view('operasional.absen');
    }

    public function alatKerja()
    {
        return view('operasional.alat-kerja');
    }

    public function sto()
    {
        return view('operasional.sto');
    }
}
