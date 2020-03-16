<?php

namespace Vinelab\NeoEloquent\Exceptions;

use Exception;
use Neoxygen\NeoClient\Exception\Neo4jException;

class QueryException extends Exception
{
    public function __construct($query, $bindings = array(), $exception = null)
    {
        // Let's handle Neo4j exceptions into the QueryException so that we extract
        // relevant info from it and send a helpful decent exception.
        if ($exception instanceof Neo4jException) {
            $message = $this->formatMessage($exception);

            parent::__construct($message);
        }
        // In case this exception is an instance of any other exception that we should not be handling
        // then we throw it as is.
        elseif ($exception instanceof \Exception) {
            throw $exception;
        }
        // We'll just add the query that was run.
        else {
            parent::__construct($query);
        }
    }

    /**
     * Format the message that should be printed out for devs.
     *
     * @param \Neoxygen\NeoClient\Exception\Neo4jException $exception
     *
     * @return string
     */
    protected function formatMessage(Neo4jException $exception)
    {
        $e = substr($exception->getMessage(), strpos($exception->getMessage(), 'Neo4j Exception with code ') + 26, strpos($exception->getMessage(), ' and message') - 26);

        $message = substr($exception->getMessage(), strpos($exception->getMessage(), 'message ') + 8);

        $exceptionName = $e ? $e.': ' : Neo4jException::class;
        $message = $message ? $message : $exception->getMessage();

        return $exceptionName.$message;
    }
}
