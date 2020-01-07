<?php

namespace Nesk\Rialto\Exceptions;

use Symfony\Component\Process\Process;

class ProcessUnexpectedlyTerminatedException extends \RuntimeException
{
    use IdentifiesProcess;

    /**
     * Constructor.
     */
    public function __construct(Process $process)
    {
        parent::__construct('The process has been unexpectedly terminated.');

        $this->process = $process;
    }
}
