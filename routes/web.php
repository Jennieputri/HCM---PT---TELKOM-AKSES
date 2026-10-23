<?php

use App\Http\Controllers\AdministratorController;
use App\Http\Controllers\AjaxController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
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

    Route::get('administrator/employee', [AdministratorController::class, 'employee'])->name('administrator.employee');
    Route::get('administrator/role', [AdministratorController::class, 'role'])->name('administrator.role');

    Route::get('administrator/position', [AdministratorController::class, 'position'])
    ->name('administrator.position');
   
    Route::get('administrator/absen', [AdministratorController::class, 'absen'])
    ->name('administrator.absen');

    Route::get('administrator/alat-kerja', [AdministratorController::class, 'alatKerja'])
    ->name('administrator.alat-kerja');

    Route::get('administrator/sto', [AdministratorController::class, 'sto'])
    ->name('administrator.sto');
});

Route::prefix('ajax')->name('ajax.')->middleware(['auth', 'data.guard'])->group(function ()
{
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

    Route::post('alat-kerja/list', [AjaxController::class, 'alatKerja_list'])->name('alat-kerja.list');
    Route::post('alat-kerja/save', [AjaxController::class, 'alatKerja_save'])->name('alat-kerja.save');
    Route::post('alat-kerja/delete', [AjaxController::class, 'alatKerja_delete'])->name('alat-kerja.delete');

    Route::post('sto/save', [AjaxController::class, 'sto_save'])->name('sto.save');
    Route::post('sto/delete', [AjaxController::class, 'sto_delete'])->name('sto.delete');
    Route::post('sto/list', [AjaxController::class, 'sto_list'])->name('sto.list');

});
