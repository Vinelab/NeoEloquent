<?php

namespace Nesk\Rialto\Exceptions;

class ReadSocketTimeoutException extends \RuntimeException
{
    /**
     * Constructor.
     */
    public function __construct(float $timeout, \Throwable $previous = null)
    {
        $timeout = number_format($timeout, 3);

        parent::__construct(implode(' ', [
            "The timeout ($timeout seconds) has been exceeded while reading the socket of the process.",
            'Maybe you should increase the "read_timeout" option.',
        ]), 0, $previous);
    }
}
