<?php

namespace App\Providers;

use Exception;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\ServiceProvider;
use MongoDB\Client as MongoClient;
use MongoDB\Database as MongoDB;
use Psr\Log\LoggerInterface;

class MongoServiceProvider extends ServiceProvider
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
        $config = $this->app->make(Config::class);
        $logger = $this->app->make(LoggerInterface::class);

        $this->app->singleton(MongoDB::class, function () use ($config, $logger) {
            $username = $config->get('database.connections.mongo.username');
            $password = $config->get('database.connections.mongo.password');
            $host = $config->get('database.connections.mongo.host');
            $database = $config->get('database.connections.mongo.database');

            if (empty($username) || empty($password) || empty($host) || empty($database)) {
                $errorMessage = MongoServiceProvider::class . ' could not register ' . MongoClient::class . ', invalid MongoDB configuration';
                $logger->error($errorMessage);
                throw new Exception($errorMessage);
            }

            $mongoClient = new MongoClient("mongodb://{$host}", [
                'username'   => $username,
                'password'   => $password,
                'authSource' => 'admin'
            ]);

            return $mongoClient->selectDatabase($database);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [MongoDB::class];
    }
}
