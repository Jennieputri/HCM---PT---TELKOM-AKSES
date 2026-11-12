<?php

namespace App\Http\Controllers;

use App\Models\AuthModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    private const MAX_LOGIN_ATTEMPTS = 5;

    private const LOGIN_LOCK_SECONDS = 300;

    public function login()
    {
        return view('auth.login');
    }

    public function login_post(Request $request)
    {
        $request->validate([
            'nik'      => 'required|integer|min:1|max:2147483647',
            'password' => 'required|string|max:255',
        ], [
            'nik.required'      => 'NIK wajib diisi!',
            'nik.integer'       => 'NIK harus berupa angka!',
            'password.required' => 'Password wajib diisi!',
        ]);

        $loginLockKey = 'login:'.$request->nik;

        if (RateLimiter::tooManyAttempts($loginLockKey, self::MAX_LOGIN_ATTEMPTS))
        {
            $minutesLeft = ceil(RateLimiter::availableIn($loginLockKey) / 60);

            return back()
                ->withErrors(['login' => 'Terlalu banyak percobaan gagal. Coba lagi dalam '.$minutesLeft.' menit.'])
                ->onlyInput('nik');
        }

        $employee = AuthModel::findByNik($request->nik);

        if (! $employee || ! Hash::check($request->password, $employee->password))
        {
            RateLimiter::hit($loginLockKey, self::LOGIN_LOCK_SECONDS);

            return back()->withErrors(['login' => 'NIK atau Password salah!'])->onlyInput('nik');
        }

        if ((int) $employee->status !== AuthModel::STATUS_ACTIVE)
        {
            return back()->withErrors(['login' => $this->inactiveAccountMessage((int) $employee->status)])->onlyInput('nik');
        }

        RateLimiter::clear($loginLockKey);

        Auth::login($employee);
        $request->session()->regenerate();

        return redirect()->route('home');
    }

    public function register()
    {
        return view('auth.register');
    }

    public function register_post(Request $request)
    {
        $request->validate([
            'nik'      => 'required|integer|min:1|max:2147483647',
            'no_ktp'   => ['required', 'regex:/^[0-9]{16}$/'],
            'name'     => 'required|string|max:100',
            'password' => 'required|string|min:8|max:255|confirmed',
        ], [
            'nik.required'       => 'NIK wajib diisi!',
            'nik.integer'        => 'NIK harus berupa angka!',
            'no_ktp.required'    => 'Nomor KTP wajib diisi!',
            'no_ktp.regex'       => 'Nomor KTP harus terdiri dari 16 digit angka!',
            'name.required'      => 'Nama lengkap wajib diisi!',
            'password.required'  => 'Password wajib diisi!',
            'password.min'       => 'Password minimal 8 karakter!',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        if (AuthModel::isNikRegistered($request->nik))
        {
            return back()->withErrors(['nik' => 'NIK sudah terdaftar!'])->onlyInput('nik', 'name');
        }

        AuthModel::register($request->nik, $request->no_ktp, $request->name, $request->password);

        return redirect()->route('login')->with('success', 'Pendaftaran berhasil. Akun Anda menunggu persetujuan HCM sebelum bisa login.');
    }

    private function inactiveAccountMessage(int $status)
    {
        return match ($status)
        {
            AuthModel::STATUS_PENDING  => 'Akun Anda masih menunggu persetujuan HCM.',
            AuthModel::STATUS_REJECTED => 'Pendaftaran akun Anda ditolak. Silakan hubungi HCM.',
            default                    => 'Akun Anda tidak aktif!',
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
