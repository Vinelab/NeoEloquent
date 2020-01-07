<?php

namespace Nesk\Rialto\Tests\Implementation;

use Nesk\Rialto\Traits\UsesBasicResourceAsDefault;
use Nesk\Rialto\Interfaces\ShouldHandleProcessDelegation;

class FsProcessDelegate implements ShouldHandleProcessDelegation
{
    use UsesBasicResourceAsDefault;

    public function resourceFromOriginalClassName(string $className): ?string
    {
        $class = __NAMESPACE__."\\Resources\\$className";

        return class_exists($class) ? $class : null;
    }
}
