<?php

namespace App\Providers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (app()->environment('local', 'testing')) {
            DB::listen(function ($query) {
                Log::info("Query: $query->sql\n" . 'Bindings: ' . print_r($query->bindings, true));
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
