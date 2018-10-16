<?php

namespace App\Providers;

use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(Mailer::class, function () {
            $queue = $this->app->make('queue');
            return $this->app->make('mailer')->setQueue($queue);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [Mailer::class];
    }
}
