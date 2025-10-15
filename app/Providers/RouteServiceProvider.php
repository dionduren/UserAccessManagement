<?php

namespace App\Providers;

use App\Models\JobRole;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Bind {job_role} by job_role_id (string) OR id (numeric) safely
        Route::bind('job_role', function ($value) {
            // Do NOT include trashed; return 404 for soft-deleted records
            if (is_numeric($value)) {
                return \App\Models\JobRole::where('id', (int)$value)->firstOrFail();
            }
            return \App\Models\JobRole::where('job_role_id', $value)->firstOrFail();
        });

        $this->routes(function () {
            Route::middleware('api')->prefix('api')->group(base_path('routes/api.php'));
            Route::middleware('web')->group(base_path('routes/web.php'));
        });
    }
}
