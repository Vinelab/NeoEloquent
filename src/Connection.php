<?php

namespace Vinelab\NeoEloquent;

use Closure;
use DateTime;
use Exception;
use Throwable;

use Vinelab\NeoEloquent\Exceptions\ConstraintViolationException;
use Vinelab\NeoEloquent\Exceptions\Exception as QueryException;
use Vinelab\NeoEloquent\Query\Builder as QueryBuilder;
use Vinelab\NeoEloquent\Query\Grammars\CypherGrammar;

use Illuminate\Database\Connection as IlluminateCollection;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Support\Arr;

use Neoxygen\NeoClient\Client;
use Neoxygen\NeoClient\ClientBuilder;

class Connection extends IlluminateCollection
{
    const TYPE_HA = 'ha';
    const TYPE_MULTI = 'multi';
    const TYPE_SINGLE = 'single';

    /**
     * The Neo4j active client connection.
     *
     * @var Neoxygen\NeoClient\Client
     */
    protected $neo;

    /**
     * The Neo4j database transaction.
     *
     * @var Neoxygen\NeoClient\Transaction\Transaction
     */
    protected $transaction;

    /**
     * Default connection configuration parameters.
     *
     * @var array
     */
    protected $defaults = array(
        'scheme' => 'http',
        'host' => 'localhost',
        'port' => 7474,
        'username' => null,
        'password' => null,
    );

    /**
     * The neo4j driver name.
     *
     * @var string
     */
    protected $driverName = 'neo4j';

    /**
     * Create a new database connection instance.
     *
     * @param array $config The database connection configuration
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();
    }

    /**
     * Get the default post processor instance.
     *
     * @return \Illuminate\Database\Query\Processors\Processor
     */
    protected function getDefaultPostProcessor()
    {
        return new Processor();
    }

    public function createConnection()
    {
        return $this->getClient();
    }

    /**
     * Create a new Neo4j client.
     *
     * @return Neoxygen\NeoClient\Client
     */
    public function createSingleConnectionClient()
    {
        $config = $this->config;

        return ClientBuilder::create()
            ->addConnection(
                'default',
                $this->getScheme($config),
                $this->getHost($config),
                $this->getPort($config),
                $this->isSecured($config),
                $this->getUsername($config),
                $this->getPassword($config)
            )
            ->setAutoFormatResponse(true)
            ->build();
    }

    public function createMultipleConnectionsClient()
    {
        $clientBuilder = ClientBuilder::create();

        $default = $this->getConfigOption('default');

        foreach ($this->getConfigOption('connections') as $connection => $config) {
            if ($default === $connection) {
                $connection = 'default';
            }

            $clientBuilder->addConnection(
                $connection,
                $this->getScheme($config),
                $this->getHost($config),
                $this->getPort($config),
                $this->isSecured($config),
                $this->getUsername($config),
                $this->getPassword($config)
            );
        }

        return $clientBuilder->setAutoFormatResponse(true)->build();
    }

    public function createHAClient()
    {
        $connections = $this->getConfigOption('connections');

        $clientBuilder = ClientBuilder::create();

        $master = $connections['master'];
        $clientBuilder->addConnection(
            'master',
            $this->getScheme($master),
            $this->getHost($master),
            $this->getPort($master),
            $this->isSecured($master),
            $this->getUsername($master),
            $this->getPassword($master)
        )->setMasterConnection('master');

        if (isset($connections['slaves'])) {
            foreach ($connections['slaves'] as $connection => $config) {
                $clientBuilder->addConnection(
                    $connection,
                    $this->getScheme($config),
                    $this->getHost($config),
                    $this->getPort($config),
                    $this->isSecured($config),
                    $this->getUsername($config),
                    $this->getPassword($config)
                )->setSlaveConnection($connection);
            }
        }

        return $clientBuilder->enableHAMode()->setAutoFormatResponse(true)->build();
    }

    /**
     * Get the currenty active database client.
     *
     * @return \Neoxygen\NeoClient\Client
     */
    public function getClient()
    {
        if (!$this->neo) {
            $this->setClient($this->createSingleConnectionClient());
        }

        return $this->neo;
    }

    /**
     * Set the client responsible for the
     * database communication.
     *
     * @param \Neoxygen\NeoClient\Client $client
     */
    public function setClient(Client $client)
    {
        $this->neo = $client;
    }

    public function getScheme(array $config)
    {
        return Arr::get($config, 'scheme', $this->defaults['scheme']);
    }

    /**
     * Get the connection host.
     *
     * @return string
     */
    public function getHost(array $config)
    {
        return Arr::get($config, 'host', $this->defaults['host']);
    }

    /**
     * Get the connection port.
     *
     * @return int|string
     */
    public function getPort(array $config)
    {
        return Arr::get($config, 'port', $this->defaults['port']);
    }

    /**
     * Get the connection username.
     *
     * @return int|string
     */
    public function getUsername(array $config)
    {
        return Arr::get($config, 'username', $this->defaults['username']);
    }

    /**
     * Returns whether or not the connection should be secured.
     *
     * @return bool
     */
    public function isSecured(array $config)
    {
        return Arr::get($config, 'username') !== null && Arr::get($config, 'password') !== null;
    }

    /**
     * Get the connection password.
     *
     * @return int|strings
     */
    public function getPassword(array $config)
    {
        return Arr::get($config, 'password', $this->defaults['password']);
    }

    /**
     * Get an option from the configuration options.
     *
     * @param string $option
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getConfigOption($option, $default = null)
    {
        return Arr::get($this->config, $option, $default);
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
     * @param string $query
     * @param array  $bindings
     * @param  bool  $useReadPdo
     *
     * @return array
     */
    public function select($query, $bindings = array(), $useReadPdo = true)
    {
        return $this->run($query, $bindings, function (self $me, $query, array $bindings) {
            if ($me->pretending()) {
                return array();
            }

            // For select statements, we'll simply execute the query and return an array
            // of the database result set. Each element in the array will be a single
            // node from the database, and will either be an array or objects.
            $query = $me->getCypherQuery($query, $bindings);

            return $this->getClient()
                ->sendCypherQuery($query['statement'], $query['parameters'])
                ->getResult();
        });
    }

    /**
     * Run an insert statement against the database.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return mixed
     */
    public function insert($query, $bindings = array())
    {
        return $this->statement($query, $bindings, true);
    }

    /**
     * Run a Cypher statement and get the number of nodes affected.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return int
     */
    public function affectingStatement($query, $bindings = array())
    {
        return $this->run($query, $bindings, function (self $me, $query, array $bindings) {
            if ($me->pretending()) {
                return 0;
            }

            // For update or delete statements, we want to get the number of rows affected
            // by the statement and return that back to the developer. We'll first need
            // to execute the statement and then we'll use CypherQuery to fetch the affected.
            $query = $me->getCypherQuery($query, $bindings);

            return $this->getClient()
                ->sendCypherQuery($query['statement'], $query['parameters'])
                ->getResult();
        });
    }

    /**
     * Execute a Cypher statement and return the boolean result.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return bool|\Everyman\Neo4j\Query\ResultSet When $result is set to true.
     */
    public function statement($query, $bindings = array(), $rawResults = false)
    {
        return $this->run($query, $bindings, function (self $me, $query, array $bindings) use ($rawResults) {
            if ($me->pretending()) {
                return true;
            }

            $query = $me->getCypherQuery($query, $bindings);

            $results = $this->getClient()
                ->sendCypherQuery($query['statement'], $query['parameters'])
                ->getResult();

            return ($rawResults === true) ? $results : !!$results;
        });
    }

    /**
     * Make a query out of a Cypher statement
     * and the bindings values.
     *
     * @param string $query
     * @param array  $bindings
     */
    public function getCypherQuery($query, array $bindings)
    {
        return ['statement' => $query, 'parameters' => $this->prepareBindings($bindings)];
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param array $bindings
     *
     * @return array
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        $prepared = array();

        foreach ($bindings as $key => $binding) {
            // The bindings are collected in a little bit different way than
            // Eloquent, we will need the key name in order to know where to replace
            // the value using the Neo4j client.
            $value = $binding;

            // We need to get the array value of the binding
            // if it were mapped
            if (is_array($value)) {
                // There are different ways to handle multiple
                // bindings vs. single bindings as values.
                $value = array_values($value);
            }

            // We need to transform all instances of the DateTime class into an actual
            // date string. Each query grammar maintains its own date string format
            // so we'll just ask the grammar for the format to get from the date.

            if ($value instanceof DateTime) {
                $binding = $value->format($grammar->getDateFormat());
            }

            // We will set the binding key and value, then
            // we replace the binding property of the id (if found)
            // with a _nodeId instead since the client
            // will not accept replacing "id(n)" with a value
            // which have been previously processed by the grammar
            // to be _nodeId instead.
            if (!is_array($binding)) {
                $binding = [$binding];
            }

            foreach ($binding as $property => $real) {
                // We should not pass any numeric key-value items since the Neo4j client expects
                // a JSON dictionary.
                if (is_numeric($property)) {
                    $property = (!is_numeric($key)) ? $key : 'id';
                }

                if ($property == 'id') {
                    $property = $grammar->getIdReplacement($property);
                }

                // when the value is an array means we have
                // a property as an array so we'll
                // keep adding to it.
                if (is_array($value)) {
                    $prepared[$property][] = $real;
                } else {
                    $prepared[$property] = $real;
                }
            }
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
        if (!$this->queryGrammar) {
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
        return new CypherGrammar();
    }

    /**
     * A binding should always be in an associative
     * form of a key=>value, otherwise we will not be able to
     * consider it a valid binding and replace its values in the query.
     * This function validates whether the binding is valid to be used.
     *
     * @param array $binding
     *
     * @return bool
     */
    public function isBinding(array $binding)
    {
        if (!empty($binding)) {
            // A binding is valid only when the key is not a number
            $keys = array_keys($binding);

            return !is_numeric(reset($keys));
        }

        return false;
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param \Closure $callback
     *
     * @return mixed
     *
     * @throws \Throwable
     */
    public function transaction(Closure $callback, $attempts = 1)
    {
        $this->beginTransaction();

        // We'll simply execute the given callback within a try / catch block
        // and if we catch any exception we can rollback the transaction
        // so that none of the changes are persisted to the database.
        try {
            $result = $callback($this);

            $this->commit();
        }

        // If we catch an exception, we will roll back so nothing gets messed
        // up in the database. Then we'll re-throw the exception so it can
        // be handled how the developer sees fit for their applications.
        catch (Exception $e) {
            $this->rollBack();

            throw $e;
        } catch (Throwable $e) {
            $this->rollBack();

            throw $e;
        }

        return $result;
    }

    /**
     * Start a new database transaction.
     */
    public function beginTransaction()
    {
        ++$this->transactions;

        if ($this->transactions == 1) {
            $client = $this->getClient();
            $this->transaction = $client->createTransaction();
        }

        $this->fireConnectionEvent('beganTransaction');
    }

    /**
     * Commit the active database transaction.
     */
    public function commit()
    {
        if ($this->transactions == 1) {
            $this->transaction->commit();
        }

        --$this->transactions;

        $this->fireConnectionEvent('committed');
    }

    /**
     * Rollback the active database transaction.
     */
    public function rollBack()
    {
        if ($this->transactions == 1) {
            $this->transactions = 0;

            $this->transaction->rollback();
        } else {
            --$this->transactions;
        }

        $this->fireConnectionEvent('rollingBack');
    }

    /**
     * Begin a fluent query against a node.
     *
     * @param string $label
     *
     * @return \Vinelab\NeoEloquent\Query\Builder
     */
    public function node($label)
    {
        $query = new QueryBuilder($this, $this->getQueryGrammar());

        return $query->from($label);
    }

    /**
     * Get a new query builder instance.
     *
     * @return \Vinelab\NeoEloquent\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    /**
     * Run a Cypher statement.
     *
     * @param string   $query
     * @param array    $bindings
     * @param \Closure $callback
     *
     * @return mixed
     *
     * @throws \Vinelab\NeoEloquent\Exceptions\InvalidCypherException
     */
    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        // To execute the statement, we'll simply call the callback, which will actually
        // run the SQL against the PDO connection. Then we can calculate the time it
        // took to execute and log the query SQL, bindings and time in our memory.
        try {
            $result = $callback($this, $query, $bindings);
        }

        // If an exception occurs when attempting to run a query, we'll format the error
        // message to include the bindings with SQL, which will make this exception a
        // lot more helpful to the developer instead of just the database's errors.
        catch (Exception $e) {
            throw new QueryException(
                $query, $this->prepareBindings($bindings), $e
            );
        }

        return $result;
    }

    /**
     * Disconnect from the underlying PDO connection.
     */
    public function disconnect()
    {
        $this->neo = null;
    }

    /**
     * Reconnect to the database if a PDO connection is missing.
     */
    protected function reconnectIfMissingConnection()
    {
        if (is_null($this->getClient())) {
            $this->reconnect();
        }
    }

    /**
     * Log a query in the connection's query log.
     *
     * @param string     $query
     * @param array      $bindings
     * @param float|null $time
     */
    public function logQuery($query, $bindings, $time = null)
    {
        if (isset($this->events)) {
            $this->events->fire('illuminate.query', [$query, $bindings, $time, $this->getName()]);
        }

        if ($this->loggingQueries) {
            $this->queryLog[] = compact('query', 'bindings', 'time');
        }
    }

    /**
     * Register a database query listener with the connection.
     *
     * @param \Closure $callback
     */
    public function listen(Closure $callback)
    {
        if (isset($this->events)) {
            $this->events->listen(Events\QueryExecuted::class, $callback);
        }
    }

    /**
     * Fire an event for this connection.
     *
     * @param string $event
     */
    protected function fireConnectionEvent($event)
    {
        if (isset($this->events)) {
            $this->events->fire('connection.'.$this->getName().'.'.$event, $this);
        }
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \Vinelab\NeoEloquent\Schema\Builder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new Schema\Builder($this);
    }

    /**
     * Handle exceptions thrown in $this::run()
     *
     * @throws mixed
     */
    protected function handleExceptions($query, $bindings, $e)
    {
        if(strpos($e->getMessage(), '"Neo.ClientError.Schema.ConstraintValidationFailed"') !== false) {
                throw new ConstraintViolationException($query, $bindings, $e);
            }

        throw new QueryException($query, $bindings, $e);
    }
}
