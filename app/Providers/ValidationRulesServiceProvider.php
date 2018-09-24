<?php

namespace App\Providers;

use Illuminate\Support\Facades\Validator;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class ValidationRulesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Validator::extend('composite_unique', '\App\Rules\CompositeUnique@validate');
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