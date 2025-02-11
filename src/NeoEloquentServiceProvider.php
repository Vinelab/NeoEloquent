<?php

namespace Vinelab\NeoEloquent;

use Illuminate\Container\Container;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use Laudis\Neo4j\Basic\Client;
use Laudis\Neo4j\Basic\Driver;
use Laudis\Neo4j\Basic\Session;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Contracts\DriverInterface;
use Laudis\Neo4j\Contracts\SessionInterface;
use Laudis\Neo4j\Databags\SessionConfiguration;
use Laudis\Neo4j\Enum\AccessMode;
use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use Vinelab\NeoEloquent\Connectors\ConnectionFactory;

class NeoEloquentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ConnectionFactory::class, static function (Container $container) {
            return new ConnectionFactory(client: $container->get(Client::class));
        });

        $this->app->alias(ConnectionFactory::class, 'db.connector.neo4j');

        Connection::resolverFor('neo4j', $this->neo4jResolver(...));

        $this->registerPercentile('percentileDisc');
        $this->registerPercentile('percentileCont');
        $this->registerAggregate('stdev');
        $this->registerAggregate('stdevp');
        $this->registerCollect();

        $this->app->singleton(Client::class, static function (Container $container): Client {
            $connections = $container->get('config')->get('database.connections');
            $builder = ClientBuilder::create();
            $factory = new ConnectionFactory();
            $default = $container->get('config')->get('database.connections.default');

            foreach ($connections as $name => $connection) {
                if ($connection['driver'] === 'neo4j') {
                    [ 0 => $uri, 2 => $auth] = $factory->toBaseConnectionParts($connection);

                    $builder = $builder->withDriver($name, $uri->__toString(), $auth);
                }

                if ($name === $default) {
                    $builder = $builder->withDefaultDriver($default);
                }
            }

            return new Client($builder->build());
        });

        $this->app->alias(Client::class, \Laudis\Neo4j\Client::class);
        $this->app->alias(Client::class, ClientInterface::class);

        $this->app->singleton(Driver::class, static function (Container $container): Driver {
            return $container->get(Client::class)->getDriver(null);
        });
        $this->app->alias(Driver::class, DriverInterface::class);

        $this->app->bind(Session::class, static function (Container $container): Session {
            return $container->get(Driver::class)->createSession();
        });
        $this->app->alias(Session::class, SessionInterface::class);
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
