<?php

namespace Vinelab\NeoEloquent;

use Closure;
use Exception;
use Throwable;
use Vinelab\NeoEloquent\Exceptions\QueryException;
use Vinelab\NeoEloquent\ConnectionInterface;

use Illuminate\Contracts\Events\Dispatcher as IlluminateDispatcher;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Connection as BaseConnection;
use Illuminate\Database\Schema\Grammars\Grammar as SchemaGrammar;
use Illuminate\Database\QueryException as IlluminateQueryException;
use Illuminate\Events\Dispatcher;

class ConnectionAdapter extends BaseConnection implements ConnectionInterface
{
    public function __construct(array $config = [])
    {
        $this->neoeloquent = app('neoeloquent.connection');
    }


	/**
	 * Set the query grammar to the default implementation.
	 *
	 * @return void
	 */
	public function useDefaultQueryGrammar()
	{
		$this->neoeloquent->useDefaultQueryGrammar();
	}

	/**
	 * Get the default query grammar instance.
	 *
	 * @return \Illuminate\Database\Query\Grammars\Grammar
	 */
	protected function getDefaultQueryGrammar()
	{
		return $this->neoeloquent->getDefaultQueryGrammar();
	}

	/**
	 * Set the schema grammar to the default implementation.
	 *
	 * @return void
	 */
	public function useDefaultSchemaGrammar()
	{
		$this->neoeloquent->useDefaultSchemaGrammar();
	}

	/**
	 * Get the default schema grammar instance.
	 *
	 * @return \Illuminate\Database\Schema\Grammars\Grammar
	 */
	protected function getDefaultSchemaGrammar() {}

	/**
	 * Set the query post processor to the default implementation.
	 *
	 * @return void
	 */
	public function useDefaultPostProcessor()
	{
		$this->neoeloquent->useDefaultQueryGrammar();
	}

	/**
	 * Get the default post processor instance.
	 *
	 * @return \Illuminate\Database\Query\Processors\Processor
	 */
	protected function getDefaultPostProcessor()
	{
        return $this->neoeloquent->getDefaultPostProcessor();
	}

	/**
	 * Get a schema builder instance for the connection.
	 *
	 * @return \Illuminate\Database\Schema\Builder
	 */
	public function getSchemaBuilder()
	{
        return $this->neoeloquent->getSchemaBuilder();
	}

	/**
	 * Get a new raw query expression.
	 *
	 * @param  mixed  $value
	 * @return \Illuminate\Database\Query\Expression
	 */
	public function raw($value)
	{
        return $this->neoeloquent->raw($value);
	}

	/**
	 * Run a select statement and return a single result.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return mixed
	 */
	public function selectOne($query, $bindings = array(), $useReadPdo = true)
	{
        return $this->neoeloquent->selectOne($query, $bindings);
	}

	/**
	 * Run a select statement against the database.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return array
	 */
	public function selectFromWriteConnection($query, $bindings = array())
	{
		return $this->neoeloquent->selectFromWriteConnection($query, $bindings);
	}

	/**
	 * Run a select statement against the database.
	 *
	 * @param  string  $query
	 * @param  array  $bindings
	 * @param  bool  $useReadPdo
	 * @return array
	 */
	public function select($query, $bindings = array(), $useReadPdo = true)
	{
        return $this->neoeloquent->select($query, $bindings);
	}

	/**
	 * Run an insert statement against the database.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return bool
	 */
	public function insert($query, $bindings = array())
	{
		return $this->neoeloquent->insert($query, $bindings);
	}

	/**
	 * Run an update statement against the database.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return int
	 */
	public function update($query, $bindings = array())
	{
		return $this->neoeloquent->update($query, $bindings);
	}

	/**
	 * Run a delete statement against the database.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return int
	 */
	public function delete($query, $bindings = array())
	{
		return $this->neoeloquent->delete($query, $bindings);
	}

	/**
	 * Execute an SQL statement and return the boolean result.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return bool
	 */
	public function statement($query, $bindings = array(), $rawResults = false)
	{
		return $this->neoeloquent->statement($query, $bindings, $rawResults);
	}

	/**
	 * Run an SQL statement and get the number of rows affected.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return int
	 */
	public function affectingStatement($query, $bindings = array())
	{
		return $this->neoeloquent->affectingStatement($query, $bindings);
	}

	/**
	 * Run a raw, unprepared query against the PDO connection.
	 *
	 * @param  string  $query
	 * @return bool
	 */
	public function unprepared($query)
	{
		return $this->neoeloquent->unprepared($query);
	}

	/**
	 * Prepare the query bindings for execution.
	 *
	 * @param  array  $bindings
	 * @return array
	 */
	public function prepareBindings(array $bindings)
	{
		return $this->neoeloquent->prepareBindings($bindings);
	}

	/**
	 * Execute a Closure within a transaction.
	 *
	 * @param  \Closure  $callback
	 * @return mixed
	 *
	 * @throws \Exception
	 */
	public function transaction(Closure $callback, $attempts = 1)
	{
        $this->neoeloquent->transaction($callback, $attempts);
	}

	/**
	 * Start a new database transaction.
	 *
	 * @return void
	 */
	public function beginTransaction()
	{
        $this->neoeloquent->beginTransaction();
	}

	/**
	 * Commit the active database transaction.
	 *
	 * @return void
	 */
	public function commit()
	{
        $this->neoeloquent->commit();
	}

	/**
	 * Rollback the active database transaction.
	 *
	 * @return void
	 */
	public function rollBack($toLevel = null)
	{
		$this->neoeloquent->rollBack();
	}

	/**
	 * Get the number of active transactions.
	 *
	 * @return int
	 */
	public function transactionLevel()
	{
		return $this->neoeloquent->transactionLevel();
	}

	/**
	 * Execute the given callback in "dry run" mode.
	 *
	 * @param  \Closure  $callback
	 * @return array
	 */
	public function pretend(Closure $callback)
	{
        return $this->neoeloquent->pretend($callback);
	}

	/**
	 * Run a SQL statement and log its execution context.
	 *
	 * @param  string    $query
	 * @param  array     $bindings
	 * @param  \Closure  $callback
	 * @return mixed
	 *
	 * @throws \Illuminate\Database\QueryException
	 */
	protected function run($query, $bindings, Closure $callback)
	{
		return $this->neoeloquent->run($query, $bindings, $callback);
	}

	/**
	 * Run a SQL statement.
	 *
	 * @param  string    $query
	 * @param  array     $bindings
	 * @param  \Closure  $callback
	 * @return mixed
	 *
	 * @throws \Illuminate\Database\QueryException
	 */
	protected function runQueryCallback($query, $bindings, Closure $callback)
	{
		return $this->neoeloquent->runQueryCallback($query, $bindings, $callback);
	}

	/**
	 * Handle a query exception that occurred during query execution.
	 *
	 * @param  \Illuminate\Database\QueryException  $e
	 * @param  string    $query
	 * @param  array     $bindings
	 * @param  \Closure  $callback
	 * @return mixed
	 *
	 * @throws \Illuminate\Database\QueryException
	 */
	protected function tryAgainIfCausedByLostConnection(IlluminateQueryException $e, $query, $bindings, Closure $callback)
	{
		return $this->neoeloquent->tryAgainIfCausedByLostConnection(new QueryException($e), $query, $bindings, $callback);
	}

	/**
	 * Determine if the given exception was caused by a lost connection.
	 *
	 * @param  \Illuminate\Database\QueryException
	 * @return bool
	 */
	protected function causedByLostConnection(Throwable $e)
	{
        return $this->neoeloquent->causedByLostConnection(new QueryException($e));
	}

	/**
	 * Disconnect from the underlying PDO connection.
	 *
	 * @return void
	 */
	public function disconnect()
	{
        $this->neoeloquent->disconnect();
	}

	/**
	 * Reconnect to the database.
	 *
	 * @return void
	 *
	 * @throws \LogicException
	 */
	public function reconnect()
	{
		$this->neoeloquent->reconnect();
	}

	/**
	 * Reconnect to the database if a PDO connection is missing.
	 *
	 * @return void
	 */
	protected function reconnectIfMissingConnection()
	{
		$this->neoeloquent->reconnectIfMissingConnection();
	}

	/**
	 * Log a query in the connection's query log.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @param  float|null  $time
	 * @return void
	 */
	public function logQuery($query, $bindings, $time = null)
	{
        $this->neoeloquent->logQuery($query, $bindings, $time, null);
	}

	/**
	 * Register a database query listener with the connection.
	 *
	 * @param  \Closure  $callback
	 * @return void
	 */
	public function listen(Closure $callback)
	{
		$this->neoeloquent->listen($callback);
	}

	/**
	 * Fire an event for this connection.
	 *
	 * @param  string  $event
	 * @return void
	 */
	protected function fireConnectionEvent($event)
	{
		$this->neoeloquent->fireConnectionEvent($event);
	}

	/**
	 * Get the elapsed time since a given starting point.
	 *
	 * @param  int    $start
	 * @return float
	 */
	protected function getElapsedTime($start)
	{
		return $this->neoeloquent->getElapsedTime($start);
	}

	/**
	 * Set the reconnect instance on the connection.
	 *
	 * @param  callable  $reconnector
	 * @return $this
	 */
	public function setReconnector(callable $reconnector)
	{
		return $this->neoeloquent->setReconnector($reconnector);
	}

	/**
	 * Get the database connection name.
	 *
	 * @return string|null
	 */
	public function getName()
	{
		return $this->neoeloquent->getConfigOption('name');
	}

	/**
	 * Get an option from the configuration options.
	 *
	 * @param  string  $option
	 * @return mixed
	 */
	public function getConfig($option = null)
	{
		return $this->neoeloquent->getConfigOption($option);
	}

	/**
	 * Get the PDO driver name.
	 *
	 * @return string
	 */
	public function getDriverName()
	{
		return $this->neoeloquent->getDriverName();
	}

	/**
	 * Get the query grammar used by the connection.
	 *
	 * @return \Illuminate\Database\Query\Grammars\Grammar
	 */
	public function getQueryGrammar()
	{
		return $this->neoeloquent->getQueryGrammar();
	}

	/**
	 * Set the query grammar used by the connection.
	 *
	 * @param  \Illuminate\Database\Query\Grammars\Grammar
	 * @return void
	 */
	public function setQueryGrammar(Grammar $grammar)
	{
		$this->neoeloquent->setQueryGrammar($grammar);
	}

	/**
	 * Get the schema grammar used by the connection.
	 *
	 * @return \Illuminate\Database\Query\Grammars\Grammar
	 */
	public function getSchemaGrammar()
	{
		return $this->neoeloquent->getSchemaGrammar();
	}

	/**
	 * Set the schema grammar used by the connection.
	 *
	 * @param  \Illuminate\Database\Schema\Grammars\Grammar
	 * @return void
	 */
	public function setSchemaGrammar(SchemaGrammar $grammar)
	{
		$this->neoeloquent->setSchemaGrammar($grammar);
	}

	/**
	 * Get the query post processor used by the connection.
	 *
	 * @return \Illuminate\Database\Query\Processors\Processor
	 */
	public function getPostProcessor()
	{
		return $this->neoeloquent->getPostProcessor();
	}

	/**
	 * Set the query post processor used by the connection.
	 *
	 * @param  \Illuminate\Database\Query\Processors\Processor
	 * @return void
	 */
	public function setPostProcessor(Processor $processor)
	{
        $this->neoeloquent->setPostProcessor($processor);
	}

	/**
	 * Get the event dispatcher used by the connection.
	 *
	 * @return \Illuminate\Contracts\Events\Dispatcher
	 */
	public function getEventDispatcher()
	{
		return $this->neoeloquent->getEventDispatcher();
	}

	/**
	 * Set the event dispatcher instance on the connection.
	 *
	 * @param  \Illuminate\Contracts\Events\Dispatcher
	 * @return void
	 */
	public function setEventDispatcher(IlluminateDispatcher $events)
	{
		$this->neoeloquent->setEventDispatcher(\App::make(Dispatcher::class));
	}

	/**
	 * Determine if the connection in a "dry run".
	 *
	 * @return bool
	 */
	public function pretending()
	{
		return $this->neoeloquent->pretending();
	}

	/**
	 * Get the default fetch mode for the connection.
	 *
	 * @return int
	 */
	public function getFetchMode()
	{
		return $this->fetchMode;
	}

	/**
	 * Set the default fetch mode for the connection.
	 *
	 * @param  int  $fetchMode
	 * @return int
	 */
	public function setFetchMode($fetchMode, $fetchArgument = null, array $fetchConstructorArgument = [])
	{
        $this->neoeloquent->setFetchMode($fetchMode);
	}

	/**
	 * Get the connection query log.
	 *
	 * @return array
	 */
	public function getQueryLog()
	{
		return $this->neoeloquent->getQueryLog();
	}

	/**
	 * Clear the query log.
	 *
	 * @return void
	 */
	public function flushQueryLog()
	{
		$this->neoeloquent->flushQueryLog();
	}

	/**
	 * Enable the query log on the connection.
	 *
	 * @return void
	 */
	public function enableQueryLog()
	{
		$this->neoeloquent->enableQueryLog();
	}

	/**
	 * Disable the query log on the connection.
	 *
	 * @return void
	 */
	public function disableQueryLog()
	{
		$this->neoeloquent->disableQueryLog();
	}

	/**
	 * Determine whether we're logging queries.
	 *
	 * @return bool
	 */
	public function logging()
	{
		return $this->neoeloquent->logging();
	}

    public function __call($method, $parameters)
    {
        call_user_func_array([$this->neoeloquent, $method], $parameters);
    }
}
