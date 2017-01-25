<?php

namespace Vinelab\NeoEloquent\Connectors;

use InvalidArgumentException;
use Vinelab\NeoEloquent\Connection;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;

class ConnectionFactory
{
    /**
     * The driver to use.
     *
     * @var string
     */
    private $driver = 'neo4j';

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected $container;


    /**
     * Create a new connection factory instance.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Establish a PDO connection based on the configuration.
     *
     * @param array  $config
     * @param string $name
     *
     * @return \Illuminate\Database\Connection
     */
    public function make(array $config, $name = null)
    {
        if (isset($config['replication']) && $config['replication'] == true && isset($config['connections'])) {
            // HA / Replication configuration
            $connection = $this->createHAConnection($config);
        } elseif (isset($config['connections']) && count($config['connections']) > 1) {
            // multi-server configuration
            $connection = $this->createMultiServerConnection($config);
        } else {
            // single connection configuration
            $connection = $this->createSingleConnection($config);
        }

        return $connection;
    }

    /**
     * Create a single database connection instance.
     *
     * @param array $config
     *
     * @return \Illuminate\Database\Connection
     */
    protected function createSingleConnection(array $config)
    {
        $connector = $this->createConnector($config);

        return $this->createConnection($this->driver, $connector, Connection::TYPE_SINGLE, $config);
    }

    /**
     * Create a single database connection instance to multiple servers.
     *
     * @param array $config
     *
     * @return \Illuminate\Database\Connection
     */
    protected function createMultiServerConnection(array $config)
    {
        $connector = $this->createConnector($config);

        return $this->createConnection($this->driver, $connector, Connection::TYPE_MULTI, $config);
    }

    protected function createHAConnection(array $config)
    {
        $connector = $this->createConnector($config);

        return $this->createConnection($this->driver, $connector, Connection::TYPE_HA, $config);
    }

    /**
     * Create a single database connection instance.
     *
     * @param array $config
     *
     * @return \Illuminate\Database\Connection
     */
    protected function createReadWriteConnection(array $config)
    {
        $connection = $this->createSingleConnection($this->getWriteConfig($config));

    }

    /**
     * Get the read configuration for a read / write connection.
     *
     * @param array $config
     *
     * @return array
     */
    protected function getReadConfig(array $config)
    {
        $readConfig = $this->getReadWriteConfig($config, 'read');

        if (isset($readConfig['host']) && is_array($readConfig['host'])) {
            $readConfig['host'] = count($readConfig['host']) > 1
                ? $readConfig['host'][array_rand($readConfig['host'])]
                : $readConfig['host'][0];
        }

        return $this->mergeReadWriteConfig($config, $readConfig);
    }

    /**
     * Merge a configuration for a read / write connection.
     *
     * @param array $config
     * @param array $merge
     *
     * @return array
     */
    protected function mergeReadWriteConfig(array $config, array $merge)
    {
        return Arr::except(array_merge($config, $merge), ['read', 'write']);
    }

    /**
     * Parse and prepare the database configuration.
     *
     * @param array  $config
     * @param string $name
     *
     * @return array
     */
    protected function parseConfig(array $config, $name)
    {
        return Arr::add($config, 'name', $name);
    }

    /**
     * Create a connector instance based on the configuration.
     *
     * @param array $config
     *
     * @return \Illuminate\Database\Connectors\ConnectorInterface
     *
     * @throws \InvalidArgumentException
     */
    public function createConnector(array $config)
    {
        if ($this->container->bound($key = "db.connector.{$this->driver}")) {
            return $this->container->make($key);
        }

        switch ($this->driver) {
            case 'neo4j':
                return new Neo4jConnector();
                break;
        }

        throw new InvalidArgumentException("Unsupported driver [{$this->driver}]");
    }

    /**
     * Create a new connection instance.
     *
     * @param string        $driver
     * @param \PDO|\Closure $connection
     * @param string        $database
     * @param string        $prefix
     * @param array         $config
     *
     * @return \Illuminate\Database\Connection
     *
     * @throws \InvalidArgumentException
     */
    protected function createConnection($driver, $connector, $type, array $config = [])
    {
        if ($this->container->bound($key = "db.connection.{$driver}")) {
            return $this->container->make($key, [$connection, $config]);
        }

        switch ($driver) {
            case 'neo4j':
                return $connector->connect($type, $config);
                break;
        }

        throw new InvalidArgumentException("Unsupported driver [$driver]");
    }
}
