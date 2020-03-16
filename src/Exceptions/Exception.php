<?php

namespace Vinelab\NeoEloquent\Exceptions;

use RuntimeException;

class Exception extends RuntimeException
{
    /**
     * The query resulting in this error.
     *
     * @var string
     */
    protected $query;

    /**
     * The bindings used leading to this error.
     *
     * @var array
     */
    protected $bindings;

    /**
     * The driver's error.
     *
     * @var Neoxygen\NeoClient\Exception\*
     */
    protected $exception;

    /**
     * create an instance of this class.
     *
     * @param array $messages
     */
    public function __construct($query, $bindings, $exception)
    {
        $this->query = $query;
        $this->bindings = $bindings;
        $this->exception = $exception;
    }

    /**
     * return the query.
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * return the bindings.
     *
     * @return string
     */
    public function getBindings()
    {
        return $this->bindings;
    }

    /**
     * return the driver's exception.
     *
     * @return string
     */
    public function getDriverException()
    {
        return $this->exception;
    }
}
