<?php

namespace App\Providers;

use Illuminate\Contracts\Validation\Factory as Validator;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;

class ValidationRulesServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $validator = $this->app->make(Validator::class);
        $validator->extend('composite_unique', '\App\Rules\CompositeUnique@validate');
        $validator->replacer('composite_unique', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':parameter', $parameters[1], str_replace(':attribute', $attribute, $message));
        });
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