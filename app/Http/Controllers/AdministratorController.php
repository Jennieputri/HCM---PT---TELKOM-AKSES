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
}
