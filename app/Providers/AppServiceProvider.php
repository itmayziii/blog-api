<?php

namespace App\Providers;

use Illuminate\Database\DatabaseManager;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $logger = $this->app->make(LoggerInterface::class);
        $db = $this->app->make(DatabaseManager::class);

        if (app()->environment('local', 'testing')) {
            $db->listen(function ($query) use ($logger) {
                $logger->info("Query: $query->sql\n" . 'Bindings: ' . print_r($query->bindings, true));
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
