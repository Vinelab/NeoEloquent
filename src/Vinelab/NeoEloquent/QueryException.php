<?php namespace Vinelab\NeoEloquent;

use Everyman\Neo4j\Exception as Neo4jException;

class QueryException extends Neo4jException {

    public function __construct($query, $bindings, $exception)
    {
        // Let's handle Neo4j exceptions into the QueryException so that we extract
        // relevant info from it and send a helpful decent exception.
        if ($exception instanceof Neo4jException)
        {
            parent::__construct($exception->getMessage(), 0, $exception->getHeaders(), $exception->getData());
        }
        // In case this exception is an instance of any other exception that we should not be handling
        // then we throw it as is.
        elseif ($exception instanceof \Exception)
        {
            throw $exception;
        }
        // We'll just add the query that was run.
        else
        {
            parent::__construct($query);
        }
    }
}
