<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Login
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

/*
|--------------------------------------------------------------------------
| Register
|--------------------------------------------------------------------------
*/

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', function (Request $request) {

    $request->validate([
        'nik' => ['required', 'regex:/^[0-9]+$/'],
        'name' => ['required', 'string'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ], [
        'nik.regex' => 'NIK harus berupa angka!',
        'password.min' => 'Password minimal 8 karakter!',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
    ]);

    return redirect()->route('login');

})->name('register.submit');
