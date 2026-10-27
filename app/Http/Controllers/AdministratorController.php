<?php

namespace App\Http\Controllers;

class AdministratorController extends Controller
{
    public function approval()
    {
        return view('administrator.approval');
    }

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
}
