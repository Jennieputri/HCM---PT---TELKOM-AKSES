<?php

namespace App\Http\Controllers;

use App\Models\HomeModel;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        $employee             = Auth::user();
        $totalActiveEmployees = HomeModel::totalActiveEmployees();

        return view('home', compact('employee', 'totalActiveEmployees'));
    }
}
