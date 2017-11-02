<?php

namespace App\Providers;

use App\Blog;
use App\Category;
use App\Contact;
use App\Http\Controllers\FileController;
use App\Policies\BlogPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\ContactPolicy;
use App\Policies\FilePolicy;
use App\Policies\TagPolicy;
use App\Policies\UserPolicy;
use App\Tag;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    private $policies = [
        Contact::class  => ContactPolicy::class,
        Blog::class     => BlogPolicy::class,
        Category::class => CategoryPolicy::class,
        Tag::class      => TagPolicy::class,
        User::class     => UserPolicy::class
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

            return null;
        });
    }

    private function registerPolicies()
    {
        foreach ($this->policies as $class => $policyClass) {
            Gate::policy($class, $policyClass);
        }
    }
}
