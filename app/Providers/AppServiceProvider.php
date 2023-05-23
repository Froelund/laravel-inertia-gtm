<?php

namespace App\Providers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Session;
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
        Store::macro('addDataLayer', function ($data) {
            $this->push('data_layer', $data);

            return $this;
        });

        RedirectResponse::macro('addDataLayer', function ($data) {
            Session::addDataLayer($data);

            return $this;
        });
    }
}
