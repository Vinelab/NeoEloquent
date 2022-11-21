<?php

namespace Vinelab\NeoEloquent;


use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;

class NeoEloquentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $resolver = function ($connection, string $database, string $prefix, array $config) {
            return $this->app->get(ConnectionFactory::class)->make($database, $prefix, $config);
        };

        \Illuminate\Database\Connection::resolverFor('neo4j', Closure::fromCallable($resolver));

        Builder::macro('percentileDisc', function (string $logins, $percentile) {
            return $this->aggregate('percentileDisc', $logins, $percentile ?? 1.0);
        });
    }
}
