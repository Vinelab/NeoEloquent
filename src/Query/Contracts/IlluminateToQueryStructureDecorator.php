<?php

namespace Vinelab\NeoEloquent\Query\Contracts;

use Illuminate\Contracts\Database\Query\Builder as IlluminateBuilder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder as CypherBuilder;

interface IlluminateToQueryStructureDecorator
{
    public function decorate(IlluminateBuilder $illuminateBuilder, CypherBuilder $cypherBuilder): void;
}
