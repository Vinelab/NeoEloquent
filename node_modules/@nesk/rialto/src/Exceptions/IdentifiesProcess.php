<?php

namespace Nesk\Rialto\Exceptions;

trait IdentifiesProcess
{
    /**
     * The associated process.
     *
     * @var \Symfony\Component\Process\Process
     */
    private $process;

    /**
     * Return the associated process.
     */
    public function getProcess(): Process
    {
        return $this->process;
    }
}
