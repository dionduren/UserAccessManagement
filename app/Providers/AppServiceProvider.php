<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Company;
use App\Models\Kompartemen;
use App\Models\Departemen;
use App\Models\JobRole;
use App\Observers\MasterDataObserver;

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
    }
}
