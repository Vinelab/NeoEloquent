<?php namespace Vinelab\NeoEloquent;

use Everyman\Neo4j\Client as NeoClient;
use Everyman\Neo4j\Cypher\Query as CypherQuery;
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

    /**
	 * Run a select statement against the database.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return array
	 */
    public function select($query, $bindings = array())
    {
        return $this->run($query, $bindings, function($me, $query, $bindings)
		{
			if ($me->pretending()) return array();

			// For select statements, we'll simply execute the query and return an array
			// of the database result set. Each element in the array will be a single
			// node from the database, and will either be an array or objects.
            $statement = $me->getCypherQuery($query, $bindings);

            return $statement->getResultSet();
		});
    }

    /**
     * Make a query out of a Cypher statement
     * and the bindings values
     *
     * @param  string  $query
     * @param  array  $bindings
     */
    public function getCypherQuery($query, array $bindings)
    {
        return new CypherQuery($this->getClient(), $query, $this->transformBindings($query, $bindings));
    }

    /**
	 * Prepare the query bindings for execution.
	 *
     * @param  string $query
	 * @param  array  $bindings
	 * @return array
	 */
	public function transformBindings($query, array $bindings)
    {
        // first we call our mother to prepare the bindings for us
        $prepared = parent::prepareBindings($bindings);

        // Now that our bindings are ready to be injected into
        // the query, according to the Neo4j client bindings
        // can be matched by key, i.e. {name} will match
        // the value in an associative arra like ['name' => $name]
        // therefore we need to match the "columns" to their values
        preg_match_all('/{(.*?)}/', $query, $matches);

        $transformed = array();

        if ((isset($matches[1]) and ! empty($matches[1])))
        {
            foreach ($matches[1] as $index=>$property)
            {
                $transformed[$property] = $bindings[$index];
            }
        }

        return $transformed;
    }

    /**
     * Get the query grammar used by the connection.
     *
     * @return \Vinelab\NeoEloquent\Query\Grammars\CypherGrammar
     */
    public function getQueryGrammar()
    {
        if ( ! $this->queryGrammar)
        {
            $this->useDefaultQueryGrammar();
        }

        return $this->queryGrammar;
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \Vinelab\NeoEloquent\Query\Grammars\CypherGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        return new Query\Grammars\CypherGrammar;
    }

}
