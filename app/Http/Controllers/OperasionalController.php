<?php

namespace App\Http\Controllers;

use App\Models\StrukturOrganisasiModel;

class OperasionalController extends Controller
{
    public function strukturOrganisasi()
    {
        return view('operasional.struktur-organisasi', StrukturOrganisasiModel::chart());
    }

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
