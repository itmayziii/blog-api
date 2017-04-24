<?php

namespace App\Providers;

use App\Contact;
use App\Policies\ContactPolicy;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    private $policies = [
        Contact::class => ContactPolicy::class
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $apiToken = $request->header('API-Token');
            if ($apiToken) {
                $user = User::where('api_token', $apiToken)->first();
                return $user;
            }
        });
    }

    private function registerPolicies()
    {
        foreach ($this->policies as $class => $policyClass) {
            Gate::policy($class, $policyClass);
        }
    }
}
