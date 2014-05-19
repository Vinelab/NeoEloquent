<?php namespace Vinelab\NeoEloquent;

use Everyman\Neo4j\Exception as Neo4jException;

class QueryException extends Neo4jException {

    public function __construct($query, $bindings, $exception)
    {
        if ($exception instanceof Neo4jException)
        {
            parent::__construct($exception->getMessage(), 0, $exception->getHeaders(), $exception->getData());
        } elseif ($exception instanceof \Exception)
        {
            throw $exception;
        } else
        {
            parent::__construct($query);
        }
    }
}
