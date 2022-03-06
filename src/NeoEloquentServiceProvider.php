<?php

namespace Vinelab\NeoEloquent;


use Closure;
use Throwable;
use Vinelab\NeoEloquent\Eloquent\NeoEloquentFactory;
use Illuminate\Support\ServiceProvider;
use Faker\Generator as FakerGenerator;
use function array_key_exists;

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
        $this->app->singleton(NeoEloquentFactory::class, function ($app) {
            return NeoEloquentFactory::construct(
                $app->make(FakerGenerator::class), $this->app->databasePath('factories')
            );
        });

        if ($this->app->runningInConsole()) {
            $this->app->register(MigrationServiceProvider::class);
        }
    }
}
