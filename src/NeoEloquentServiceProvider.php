<?php

namespace Vinelab\NeoEloquent;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use Laudis\Neo4j\Basic\Driver;
use Laudis\Neo4j\Databags\SessionConfiguration;
use Laudis\Neo4j\Enum\AccessMode;
use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use Vinelab\NeoEloquent\Connectors\ConnectionFactory;

class NeoEloquentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('db.connector.neo4j', ConnectionFactory::class);

        Connection::resolverFor('neo4j', $this->neo4jResolver(...));

        $this->registerPercentile('percentileDisc');
        $this->registerPercentile('percentileCont');
        $this->registerAggregate('stdev');
        $this->registerAggregate('stdevp');
        $this->registerCollect();
    }

    private function registerPercentile(string $function): void
    {
        $macro = function (string $logins, float|int $percentile = null) use ($function): float {
            /** @var \Vinelab\NeoEloquent\Query\Builder $x */
            $x = $this;

            return $x->aggregate($function, [$logins, new RawExpression((string) ($percentile ?? 0.0))]);
        };

        Builder::macro($function, $macro);
        \Illuminate\Database\Eloquent\Builder::macro($function, $macro);
    }

    private function registerAggregate(string $functionName): void
    {
        $macro = function (string $logins) use ($functionName): mixed {
            /** @var \Vinelab\NeoEloquent\Query\Builder $x */
            $x = $this;

            return $x->aggregate($functionName, [$logins]);
        };

        Builder::macro($functionName, $macro);
        \Illuminate\Database\Eloquent\Builder::macro($functionName, $macro);
    }

    private function registerCollect(): void
    {
        $macro = function (string $logins): Collection {
            /** @var \Vinelab\NeoEloquent\Query\Builder $x */
            $x = $this;

            return new Collection($x->aggregate('collect', [$logins])->toArray());
        };

        Builder::macro('collect', $macro);
        \Illuminate\Database\Eloquent\Builder::macro('collect', $macro);
    }

    /**
     * @param  callable():Driver  $driver
     */
    private function neo4jResolver(callable $driver, string $database, string $prefix, array $config): Connection
    {
        $sessionConfig = SessionConfiguration::default()
            ->withDatabase($config['database'] ?? null);

        $driver = $driver();

        return new \Vinelab\NeoEloquent\Connection(
            $driver->createSession($sessionConfig->withAccessMode(AccessMode::READ())),
            $driver->createSession($sessionConfig),
            $database,
            $prefix,
            $config
        );
    }
}
