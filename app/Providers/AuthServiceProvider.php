<?php

namespace App\Providers;

use App\Category;
use App\Contact;
use App\Page;
use App\Policies\CategoryPolicy;
use App\Policies\ContactPolicy;
use App\Policies\FilesystemPolicy;
use App\Policies\PagePolicy;
use App\Policies\PostPolicy;
use App\Policies\TagPolicy;
use App\Policies\UserPolicy;
use App\Post;
use App\Tag;
use App\User;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    private $policies = [
        Contact::class    => ContactPolicy::class,
        Post::class       => PostPolicy::class,
        Category::class   => CategoryPolicy::class,
        Tag::class        => TagPolicy::class,
        User::class       => UserPolicy::class,
        Filesystem::class => FilesystemPolicy::class,
        Page::class       => PagePolicy::class
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
            $apiToken = $request->hasHeader('API-Token') ? $request->header('API-Token') : $request->cookie('API-Token');
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
