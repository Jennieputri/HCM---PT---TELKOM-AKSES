<?php

namespace App\Providers;

use App\Models\AdministratorModel;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        RateLimiter::for('document-download', function (Request $request)
        {
            return Limit::perMinute(config('security.document_downloads_per_minute'))->by('document-download:'.$request->user()->nik);
        });

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
