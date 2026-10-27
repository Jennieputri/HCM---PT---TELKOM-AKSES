<?php

use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OperasionalController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function ()
{
    Route::get('login', [AuthController::class, 'login'])->name('login');
    Route::post('login', [AuthController::class, 'login_post'])->middleware('throttle:10,1')->name('login.submit');

    Route::get('register', [AuthController::class, 'register'])->name('register');
    Route::post('register', [AuthController::class, 'register_post'])->middleware('throttle:5,1')->name('register.submit');
});

Route::middleware('auth')->group(function ()
{
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('administrator')->name('administrator.')->middleware('role.hcm')->group(function ()
    {
        Route::get('approval', [AdministratorController::class, 'approval'])->name('approval');
        Route::get('employee', [AdministratorController::class, 'employee'])->name('employee');
        Route::get('role', [AdministratorController::class, 'role'])->name('role');
        Route::get('position', [AdministratorController::class, 'position'])->name('position');
    });

    Route::prefix('operasional')->name('operasional.')->group(function ()
    {
        Route::get('absen', [OperasionalController::class, 'absen'])->name('absen');
        Route::get('alat-kerja', [OperasionalController::class, 'alatKerja'])->name('alat-kerja');
        Route::get('sto', [OperasionalController::class, 'sto'])->name('sto');
    });
});

Route::prefix('ajax')->name('ajax.')->middleware(['auth', 'data.guard'])->group(function ()
{
    Route::post('dashboard/summary', [AjaxController::class, 'dashboard_summary'])->name('dashboard.summary');

    Route::middleware('role.hcm')->group(function ()
    {
        Route::post('approval/list', [AjaxController::class, 'approval_list'])->name('approval.list');
        Route::post('approval/approve', [AjaxController::class, 'approval_approve'])->name('approval.approve');
        Route::post('approval/reject', [AjaxController::class, 'approval_reject'])->name('approval.reject');

        Route::post('employee/list', [AjaxController::class, 'employee_list'])->name('employee.list');
        Route::post('employee/save', [AjaxController::class, 'employee_save'])->name('employee.save');
        Route::post('employee/delete', [AjaxController::class, 'employee_delete'])->name('employee.delete');

        Route::post('role/list', [AjaxController::class, 'role_list'])->name('role.list');
        Route::post('role/options', [AjaxController::class, 'role_options'])->name('role.options');
        Route::post('role/save', [AjaxController::class, 'role_save'])->name('role.save');
        Route::post('role/delete', [AjaxController::class, 'role_delete'])->name('role.delete');

        Route::post('position/list', [AjaxController::class, 'position_list'])->name('position.list');
        Route::post('position/save', [AjaxController::class, 'position_save'])->name('position.save');
        Route::post('position/delete', [AjaxController::class, 'position_delete'])->name('position.delete');
    });

    Route::prefix('operasional')->name('operasional.')->group(function ()
    {
        Route::post('absen/list', [AjaxController::class, 'absen_list'])->name('absen.list');
        Route::post('absen/save', [AjaxController::class, 'absen_save'])->name('absen.save');
        Route::post('absen/delete', [AjaxController::class, 'absen_delete'])->name('absen.delete');

        Route::post('alat-kerja/list', [AjaxController::class, 'alat_kerja_list'])->name('alat-kerja.list');
        Route::post('alat-kerja/save', [AjaxController::class, 'alat_kerja_save'])->name('alat-kerja.save');
        Route::post('alat-kerja/delete', [AjaxController::class, 'alat_kerja_delete'])->name('alat-kerja.delete');

        Route::post('sto/list', [AjaxController::class, 'sto_list'])->name('sto.list');
        Route::post('sto/markers', [AjaxController::class, 'sto_markers'])->name('sto.markers');
        Route::post('sto/save', [AjaxController::class, 'sto_save'])->name('sto.save');
        Route::post('sto/delete', [AjaxController::class, 'sto_delete'])->name('sto.delete');
    });
});
