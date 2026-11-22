<?php

namespace App\Providers;

use App\Models\Destination;
use App\Models\Service;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Public forms: generous for real people, tight enough to stop floods.
        RateLimiter::for('forms', fn (Request $request) => [
            Limit::perMinute(5)->by($request->ip()),
            Limit::perDay(40)->by($request->ip()),
        ]);

        RateLimiter::for('admin-login', fn (Request $request) => Limit::perMinute(5)->by(
            strtolower((string) $request->input('email')).'|'.$request->ip()
        ));

        // Navigation data shared by the header, footer and enquiry modal.
        View::composer(['layouts.app', 'partials.*', 'pages.*', 'errors.404'], function ($view) {
            static $nav = null;

            if ($nav === null) {
                $nav = [
                    'navDestinations' => Destination::active()->get(),
                    'navServices' => Service::orderBy('sort_order')->get()->groupBy('group'),
                ];
            }

            $view->with($nav);
        });
    }
}
