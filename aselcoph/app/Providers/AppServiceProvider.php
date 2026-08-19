<?php

namespace App\Providers;

use App\Models\AccountLink;
use App\Models\BillingUpload;
use App\Observers\AccountLinkObserver;
use App\Observers\BillingUploadObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        BillingUpload::observe(BillingUploadObserver::class);
        AccountLink::observe(AccountLinkObserver::class);

        RateLimiter::for('api-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
        });
        RateLimiter::for('api-register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });
        RateLimiter::for('api-membership', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('api-ledger', function (Request $request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });
    }
}
