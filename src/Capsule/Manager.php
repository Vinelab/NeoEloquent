<?php

namespace Vinelab\NeoEloquent\Capsule;

use Illuminate\Contracts\Container\Container;
use UnexpectedValueException;
use Vinelab\NeoEloquent\Connection;
use Vinelab\NeoEloquent\Eloquent\Model as Eloquent;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Support\Traits\CapsuleManagerTrait;
use Vinelab\NeoEloquent\Schema\Builder as SchemaBuilder;

class Manager
{
    use CapsuleManagerTrait;

    /**
     * The database manager instance.
     */
    protected DatabaseManager $manager;

    /**
     * Create a new database capsule manager.
     */
    public function __construct(?Container $container = null)
    {
        $this->setupContainer($container ?? new \Illuminate\Container\Container());

        // Once we have the container setup, we will setup the default configuration
        // options in the container "config" binding. This will make the database
        // manager behave correctly since all the correct binding are in place.
        $this->setupDefaultConfiguration();

        $this->setupManager();
    }

    /**
     * Setup the default database configuration options.
     */
    protected function setupDefaultConfiguration(): void
    {
        $this->container['config']['database.default'] = 'neo4j';
    }

    /**
     * Build the database manager instance.
     */
    protected function setupManager(): void
    {
        $factory = new ConnectionFactory($this->container);

        /** @noinspection PhpParamsInspection */
        $this->manager = new DatabaseManager($this->container, $factory);
    }

    /**
     * Get a connection instance from the global manager.
     */
    public static function connection(?string $connection = null): Connection
    {
        return static::$instance->getConnection($connection);
    }

    /**
     * Get a fluent query builder instance.
     */
    public static function table(string $table, ?string $connection = null)
    {
        return static::$instance->connection($connection)->table($table);
    }

    /**
     * Get a schema builder instance.
     */
    public static function schema(string $connection = null): SchemaBuilder
    {
        return static::$instance->connection($connection)->getSchemaBuilder();
    }

    /**
     * Get a registered connection instance.
     */
    public function getConnection(?string $name = null): Connection
    {
        $connection = $this->manager->connection($name);
        if (!$connection instanceof Connection) {
            throw new UnexpectedValueException('Expected connection to be instance of ' . Connection::class);
        }

        return $connection;
    }

    /**
     * Register a connection with the manager.
     */
    public function addConnection(array $config, ?string $name = null): void
    {
        $name ??= 'default';

        $connections = $this->container['config']['database.connections'];

        $connections[$name] = $config;

        $this->container['config']['database.connections'] = $connections;
    }

    /**
     * Bootstrap Eloquent so it is ready for usage.
     */
    public function bootEloquent(): void
    {
        Eloquent::setConnectionResolver($this->manager);

        // If we have an event dispatcher instance, we will go ahead and register it
        // with the Eloquent ORM, allowing for model callbacks while creating and
        // updating "model" instances; however, if it not necessary to operate.
        if ($dispatcher = $this->getEventDispatcher()) {
            Eloquent::setEventDispatcher($dispatcher);
        }
    }

    /**
     * Set the fetch mode for the database connections.
     *
     * @param int $fetchMode
     *
     * @return static
     */
    public function setFetchMode(int $fetchMode): self
    {
        $this->container['config']['database.fetch'] = $fetchMode;

        return $this;
    }

    /**
     * Get the database manager instance.
     */
    public function getDatabaseManager(): DatabaseManager
    {
        return $this->manager;
    }

    /**
     * Get the current event dispatcher instance.
     */
    public function getEventDispatcher(): ?Dispatcher
    {
        if ($this->container->bound('events')) {
            return $this->container['events'];
        }

        return null;
    }

    /**
     * Set the event dispatcher instance to be used by connections.
     */
    public function setEventDispatcher(Dispatcher $dispatcher): void
    {
        $this->container->instance('events', $dispatcher);
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $parameters)
    {
        return call_user_func_array([static::connection(), $method], $parameters);
    }
}
