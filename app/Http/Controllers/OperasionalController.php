<?php

namespace App\Http\Controllers;

use App\Models\StrukturOrganisasiModel;

class OperasionalController extends Controller
{
    public function strukturOrganisasi()
    {
        $dataStruktur = StrukturOrganisasiModel::getAll();

        return view('operasional.struktur-organisasi', [
            'dataStruktur' => $dataStruktur,
        ]);
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
