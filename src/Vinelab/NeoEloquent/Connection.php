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
	 * Run a Cypher statement and get the number of nodes affected.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return int
	 */
	public function affectingStatement($query, $bindings = array())
	{
		return $this->run($query, $bindings, function($me, $query, $bindings)
		{
			if ($me->pretending()) return 0;

			// For update or delete statements, we want to get the number of rows affected
			// by the statement and return that back to the developer. We'll first need
			// to execute the statement and then we'll use CypherQuery to fetch the affected.
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
        return new CypherQuery($this->getClient(), $query, $this->prepareBindings($bindings));
    }

    /**
	 * Prepare the query bindings for execution.
	 *
	 * @param  array  $bindings
	 * @return array
	 */
	public function prepareBindings(array $bindings)
	{

		$grammar = $this->getQueryGrammar();

        $prepared = array();

		foreach ($bindings as $key => $value)
		{
            // The bindings are a collected in a little bit different way than
            // Eloquent, we will need the key name in order to know where to replace
            // the value using the Neo4j client.
            $binding = array($key => $value);

            // We need to get the array value of the binding
            // if it were mapped
            if (is_array($value))
            {
                $value = reset($value);
            }

			// We need to transform all instances of the DateTime class into an actual
			// date string. Each query grammar maintains its own date string format
			// so we'll just ask the grammar for the format to get from the date.

			if ($value instanceof DateTime)
			{
				$bindings[$key] = $value->format($grammar->getDateFormat());
			}
			elseif ($value === false)
			{
				$bindings[$key] = 0;
			}

            if ( ! $this->isBinding($binding))
            {
                $binding = reset($binding);
            }

            $property = key($binding);

            // We do this because the binding replacement
            // will not accept replacing "id(n)" with a value
            // which have been previously processed by the grammar
            // to be _nodeId instead.

            if ($property == 'id') $property = '_nodeId';

            // Set the binding key name and value
            $prepared[$property] = $value;
		}

		return $prepared;
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

    /**
     * A binding should always be in an associative
     * form of a key=>value, otherwise we will not be able to
     * consider it a valid binding and replace its values in the query.
     * This function validates whether the binding is valid to be used.
     *
     * @param  array $binding
     * @return boolean
     */
    public function isBinding(array $binding)
    {
        // A binding is valid only when the key is not a number
        $keys = array_keys($binding);

        return ! is_numeric(reset($keys));
    }

}
