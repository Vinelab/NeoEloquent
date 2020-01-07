<?php

namespace Nesk\Rialto\Tests\Implementation;

use Nesk\Rialto\AbstractEntryPoint;

class FsWithoutProcessDelegation extends AbstractEntryPoint
{
    public function __construct()
    {
        parent::__construct(__DIR__.'/FsConnectionDelegate.js');
    }
}
