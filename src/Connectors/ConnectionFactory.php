<?php

namespace Vinelab\NeoEloquent\Connectors;

use Illuminate\Contracts\Container\Container;
use InvalidArgumentException;
use Vinelab\NeoEloquent\Connection;

class ConnectionFactory
{
    /**
     * The driver to use.
     */
    private string $driver = 'neo4j';

    /**
     * The IoC container instance.
     */
    protected Container $container;


    /**
     * Create a new connection factory instance.
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Establish a Neo4j connection based on the configuration.
     */
    public function make(array $config): Connection
    {
        if (count($config['connections'] ?? []) > 1) {
            return $this->createMultiServerConnection($config);
        }

        return $this->createSingleConnection($config);
    }

    /**
     * Create a single database connection instance.
     */
    protected function createSingleConnection(array $config): Connection
    {
        $connector = $this->createConnector();

        return $this->createConnection($this->driver, $connector, Connection::TYPE_SINGLE, $config);
    }

    /**
     * Create a single database connection instance to multiple servers.
     */
    protected function createMultiServerConnection(array $config): Connection
    {
        $connector = $this->createConnector();

        return $this->createConnection($this->driver, $connector, Connection::TYPE_MULTI, $config);
    }

    /**
     * Create a connector instance based on the configuration.
     */
    public function createConnector(): Neo4jConnector
    {
        if ($this->container->bound($key = "db.connector.$this->driver")) {
            return $this->container->make($key);
        }

        if ($this->driver === 'neo4j') {
            return new Neo4jConnector();
        }

        throw new InvalidArgumentException("Unsupported driver [$this->driver]");
    }

    /**
     * Create a new connection instance.
     */
    protected function createConnection(
        string         $driver,
        Neo4jConnector $connection,
        string         $type,
        array          $config = []
    )
    {
        if ($this->container->bound($key = "db.connection.$driver")) {
            return $this->container->make($key, [$connection, $config]);
        }

        if ($driver === 'neo4j') {
            return $connection->connect($type, $config);
        }

        throw new InvalidArgumentException("Unsupported driver [$driver]");
    }
}
