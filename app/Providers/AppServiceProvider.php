<?php

namespace App\Providers;

use Carbon\Carbon;
use App\Models\Company;
use App\Models\JobRole;
use App\Models\Departemen;
use App\Models\Kompartemen;
use App\Observers\MasterDataObserver;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Company::observe(MasterDataObserver::class);
        Kompartemen::observe(MasterDataObserver::class);
        Departemen::observe(MasterDataObserver::class);
        JobRole::observe(MasterDataObserver::class);
        Carbon::setLocale('id');

        if ($appUrl = config('app.url')) {
            URL::forceRootUrl($appUrl);
        }
    }
}
