<?php

namespace Vinelab\NeoEloquent\Exceptions;

class ConstraintViolationException extends Exception
{
    protected $code;
    protected $line;
    protected $file;
    protected $message;

    public function __construct($e)
    {
        $this->code = $e->getCode();
        $this->line = $e->getLine();
        $this->file = $e->getFile();
        $this->message = $e->getMessage();
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getLine()
    {
        return $this->line;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function getMessage()
    {
        // Exception message sample: Neo4j Exception with code "Neo.ClientError.Schema.ConstraintValidationFailed" and message "Node 534 already exists with label Talent and property "name"=[السيدة ميسا عابدين]"

        $curatedMessage = substr($this->message(), strpos($this->message(), 'message') + 8);

        // Curated message sample: "Node 534 already exists with label Talent and property "name"=[السيدة ميسا عابدين]"
        return $curatedMessage;

    }
}
