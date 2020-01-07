<?php

namespace Nesk\Rialto;

use Nesk\Rialto\Interfaces\ShouldHandleProcessDelegation;

abstract class AbstractEntryPoint
{
    use Traits\CommunicatesWithProcessSupervisor;

    /**
     * Forbidden options for the user.
     *
     * @var string[]
     */
    protected $forbiddenOptions = ['stop_timeout'];

    /**
     * Instanciate the entry point of the implementation.
     */
    public function __construct(
        string $connectionDelegatePath,
        ?ShouldHandleProcessDelegation $processDelegate = null,
        array $implementationOptions = [],
        array $userOptions = []
    ) {
        $process = new ProcessSupervisor(
            $connectionDelegatePath,
            $processDelegate,
            $this->consolidateOptions($implementationOptions, $userOptions)
        );

        $this->setProcessSupervisor($process);
    }

    /**
     * Clean the user options.
     */
    protected function consolidateOptions(array $implementationOptions, array $userOptions): array
    {
        // Filter out the forbidden option
        $userOptions = array_diff_key($userOptions, array_flip($this->forbiddenOptions));

        // Merge the user options with the implementation ones
        return array_merge($implementationOptions, $userOptions);
    }
}
