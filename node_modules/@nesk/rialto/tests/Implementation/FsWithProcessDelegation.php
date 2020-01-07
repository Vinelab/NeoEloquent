<?php

namespace Nesk\Rialto\Tests\Implementation;

use Nesk\Rialto\ProcessSupervisor;
use Nesk\Rialto\AbstractEntryPoint;

class FsWithProcessDelegation extends AbstractEntryPoint
{
    protected $forbiddenOptions = ['stop_timeout', 'foo'];

    public function __construct(array $userOptions = [])
    {
        parent::__construct(__DIR__.'/FsConnectionDelegate.js', new FsProcessDelegate, [], $userOptions);
    }

    public function getProcessSupervisor(): ProcessSupervisor
    {
        return parent::getProcessSupervisor();
    }
}
