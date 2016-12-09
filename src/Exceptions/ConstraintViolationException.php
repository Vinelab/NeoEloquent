<?php

namespace Vinelab\NeoEloquent\Exceptions;

class ConstraintViolationException extends Exception
{
    public function __construct($e)
    {
        $this->code = $e->getCode();
        $this->line = $e->getLine();
        $this->file = $e->getFile();

        // Exception message sample: Neo4j Exception with code "Neo.ClientError.Schema.ConstraintValidationFailed" and message "Node 534 already exists with label Talent and property "name"=[السيدة ميسا عابدين]"

        $curatedMessage = substr($e->getMessage(), strpos($e->getMessage(), 'message') + 8);

        // Curated message sample: "Node 534 already exists with label Talent and property "name"=[السيدة ميسا عابدين]"
        $this->message $curatedMessage;
    }
}
