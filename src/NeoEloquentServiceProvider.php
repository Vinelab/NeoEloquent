<?php

namespace Vinelab\NeoEloquent;


use Closure;
use Throwable;
use Illuminate\Support\ServiceProvider;

class NeoEloquentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot(): void
    {
        $resolver = function ($connection, string $database, string $prefix, array $config) {
            return $this->app->get(ConnectionFactory::class)->make($database, $prefix, $config);
        };

        \Illuminate\Database\Connection::resolverFor('neo4j', Closure::fromCallable($resolver));
    }

    /**
     * Register the service provider.
     *
     * @throws Throwable
     */
    public function register(): void
    {
        if ($this->app->runningInConsole()) {
            $this->app->register(MigrationServiceProvider::class);
        }
    }
}
