<?php

namespace Vinelab\NeoEloquent;


use Closure;
use Illuminate\Database\Query\Builder;
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

        Builder::macro('whereRelationship', function (string $relationship, string $other): Builder {
            $this->wheres[] = [
                'type' => 'Relationship',
                'relationship' => $relationship,
                'target' => $other
            ];

            return $this;
        });
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
