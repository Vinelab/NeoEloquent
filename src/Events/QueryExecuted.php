<?php

namespace Vinelab\NeoEloquent\Events;

class QueryExecuted
{
    /**
     * The Cypher query that was executed.
     *
     * @var string
     */
    public $cypher;

    /**
     * The array of query bindings.
     *
     * @var array
     */
    public $bindings;

    /**
     * The number of milliseconds it took to execute the query.
     *
     * @var float
     */
    public $time;

    /**
     * The databse connection instance.
     *
     * @var \Illuminate\Database\Connection
     */
    public $connection;

    /**
     * The database connection name.
     *
     * @var \Illuminate\Database\Connection
     */
    public $connectionName;

    /**
     * Create a new event instance.
     *
     * @param string $cypher
     * @param array  $bindings
     * @param float  $time
     * @param
     */
    public function __construct($cypher, $bindings, $time, $connection)
    {
        $this->cypher = $cypher;
        $this->time = $time;
        $this->bindings = $bindings;
        $this->connection = $connection;
        $this->connectionName = $connection->getName();
    }
}
