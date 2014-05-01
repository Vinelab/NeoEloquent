<?php namespace Vinelab\NeoEloquent;

use Everyman\Neo4j\Client as NeoClient;
use Vinelab\NeoEloquent\Query\Builder;
use Illuminate\Database\Connection as IlluminateConnection;

class Connection extends IlluminateConnection {

    /**
     * The Neo4j active client connection
     *
     * @var Everyman\Neo4j\Client
     */
    protected $connection;

    /**
     * Default connection configuration parameters
     *
     * @var array
     */
    protected $defaults = array(
        'host' => 'localhost',
        'port' => 7474
    );

    /**
     * The neo4j driver name
     *
     * @var string
     */
    protected $driverName = 'neo4j';

    /**
     * Create a new database connection instance
     *
     * @param array $config The database connection configuration
     */
    public function __construct(array $config)
    {
        $this->config = $config;

        // activate and set the database client connection
        $this->connection = $this->createConnection();
    }

    /**
     * Begin a fluent query against a node
     *
     * @param string $label The node lable
     * @return QueryBuilder
     */
    public function node($labels)
    {
        $query = new Builder($this);

        return $query->from($labels);
    }

    /**
     * Begin a fluent query against a database collection.
     *
     * @param  string  $table
     * @return Builder
     */
    public function table($table)
    {
        return $this->node($table);
    }

    /**
     * Create a new Neo4j client
     *
     * @return Everyman\Neo4j\Client
     */
    public function createConnection()
    {
        return new NeoClient($this->getHost(), $this->getPort());
    }

    /**
     * Get the currenty active client
     *
     * @return Everyman\Neo4j\Client
     */
    public function getClient()
    {
        return $this->connection;
    }

    /**
     * Get the connection host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->getConfig('host', $this->defaults['host']);
    }

    /**
     * Get the connection port
     *
     * @return int|string
     */
    public function getPort()
    {
        return $this->getConfig('port', $this->defaults['port']);
    }

    /**
	 * Get an option from the configuration options.
	 *
	 * @param  string   $option
     * @param  mixed    $default
	 * @return mixed
	 */
    public function getConfig($option, $default = null)
    {
        return array_get($this->config, $option, $default);
    }

    /**
     * Get the Neo4j driver name.
     *
     * @return string
     */
    public function getDriverName()
    {
        return $this->driverName;
    }

}
