<?php

namespace Vinelab\NeoEloquent;


use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use WikibaseSolutions\CypherDSL\Query;

class NeoEloquentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $resolver = function ($connection, string $database, string $prefix, array $config) {
            return $this->app->get(ConnectionFactory::class)->make($database, $prefix, $config);
        };

        \Illuminate\Database\Connection::resolverFor('neo4j', Closure::fromCallable($resolver));

        $this->registerPercentile('percentileDisc');
        $this->registerPercentile('percentileCont');
        $this->registerAggregate('stdev');
        $this->registerAggregate('stdevp');
        $this->registerCollect();
    }

    /**
     * @return void
     */
    private function registerPercentile(string $function): void
    {
        $macro = function (string $logins, $percentile = null) use ($function) {
            /** @var Builder $x */
            $x = $this;

            return $x->aggregate($function, [$logins, Query::literal($percentile ?? 0.0)]);
        };
        Builder::macro($function, $macro);
        \Illuminate\Database\Eloquent\Builder::macro($function, $macro);
    }

    /**
     * @return void
     */
    private function registerAggregate(string $function): void
    {
        $macro = function (string $logins) use ($function) {
            /** @var Builder $x */
            $x = $this;

            return $x->aggregate($function, $logins);
        };

        Builder::macro($function, $macro);
        \Illuminate\Database\Eloquent\Builder::macro($function, $macro);
    }

    private function registerCollect()
    {
        $macro = function (string $logins) {
            /** @var Builder $x */
            $x = $this;

            return collect($x->aggregate('collect', $logins)->toArray());
        };

        Builder::macro('collect', $macro);
        \Illuminate\Database\Eloquent\Builder::macro('collect', $macro);
    }
}
