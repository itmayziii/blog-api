<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Contact;
use App\Policies\CategoryPolicy;
use App\Policies\ContactPolicy;
use App\Policies\FilesystemPolicy;
use App\Policies\TagPolicy;
use App\Policies\UserPolicy;
use App\Policies\WebPagePolicy;
use App\Models\Tag;
use App\Models\User;
use App\Models\WebPage;
use App\Repositories\UserRepository;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    private $policies = [
        Contact::class    => ContactPolicy::class,
        WebPage::class    => WebPagePolicy::class,
        Category::class   => CategoryPolicy::class,
        Tag::class        => TagPolicy::class,
        User::class       => UserPolicy::class,
        Filesystem::class => FilesystemPolicy::class
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
            if (is_null($apiToken)) {
                return null;
            }

            $userRepository = app()->make(UserRepository::class);
            $user = $userRepository->findByApiToken($apiToken);
            if (is_null($user)) {
                return null;
            }

            if ($user->isApiTokenExpired()) {
                return null;
            }

            return $user;
        });
    }

    private function registerPolicies()
    {
        foreach ($this->policies as $class => $policyClass) {
            Gate::policy($class, $policyClass);
        }
    }
}
