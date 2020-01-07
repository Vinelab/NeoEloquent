<?php

namespace Nesk\Rialto\Exceptions;

use Symfony\Component\Process\Process;

class IdleTimeoutException extends \RuntimeException
{
    /**
     * Check if the exception can be applied to the process.
     */
    public static function exceptionApplies(Process $process): bool
    {
        if (Node\FatalException::exceptionApplies($process)) {
            $error = json_decode($process->getErrorOutput(), true);

            return $error['message'] === 'The idle timeout has been reached.';
        }

        return false;
    }

    /**
     * Constructor.
     */
    public function __construct(float $timeout, \Throwable $previous = null)
    {
        $timeout = number_format($timeout, 3);

        parent::__construct(implode(' ', [
            "The idle timeout ($timeout seconds) has been exceeded.",
            'Maybe you should increase the "idle_timeout" option.',
        ]), 0, $previous);
    }
}
