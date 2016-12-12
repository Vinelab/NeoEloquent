<?php

namespace Vinelab\NeoEloquent\Exceptions;

use RuntimeException;

class ConstraintViolationException extends RuntimeException
{
    protected $query;
    protected $bindings;

    public function __construct($query, $bindings, $e)
    {
        $this->code = $e->getCode();
        $this->line = $e->getLine();
        $this->file = $e->getFile();

        // Exception message sample: Neo4j Exception with code "Neo.ClientError.Schema.ConstraintValidationFailed" and message "Node 534 already exists with label Talent and property "name"=[السيدة ميسا عابدين]"

        $curatedMessage = substr($e->getMessage(), strpos($e->getMessage(), 'message') + 8);

        // Curated message sample: "Node 534 already exists with label Talent and property "name"=[السيدة ميسا عابدين]"
        $this->message = $curatedMessage.'.';
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getBindings()
    {
        return $this->bindings;
    }
}
