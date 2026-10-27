<?php

namespace App\Providers;

use App\Models\AdministratorModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        View::composer('layouts', function ($view)
        {
            $isHcm = Auth::check() && Auth::user()->isHcm();

            $view->with([
                'isHcm'                => $isHcm,
                'pendingApprovalCount' => $isHcm ? AdministratorModel::pendingApprovalCount() : 0,
            ]);
        });
    }
}
