<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $employee = Auth::user();
        $isHcm    = $employee->isHcm();

        return view('home', compact('employee', 'isHcm'));
    }
}
