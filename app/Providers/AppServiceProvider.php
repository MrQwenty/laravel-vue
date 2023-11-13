<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Cambio path pubblico per servire l'applicazione
        $this->app->bind('path.public', function () {
            return base_path(env('APP_PUBLIC_PATH', 'public'));
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Inertia::share([
            'errors' => function () {
                return session()->get('errors')
                    ? session()->get('errors')->getBag('default')->getMessages()
                    : (object)[];
            },
        ]);

        Inertia::share('flash', function () {
            return [
                'message' => session()->get('message'),
                'error' => session()->get('error'),
            ];
        });

        $this->app->singleton('langs',function () {
            return array(
                ['value' => 'it','icon' => 'fi fi-it fis'],
                ['value' => 'en','icon' => 'fi fi-gb fis'],
            );
        });
/*
        RateLimiter::for('emails', function ($job) {
            return Limit::perMinute(3);
        });*/
    }
}
