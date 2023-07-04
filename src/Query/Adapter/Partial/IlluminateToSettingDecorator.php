<?php

namespace Vinelab\NeoEloquent\Query\Adapter\Partial;

use Illuminate\Contracts\Database\Query\Builder;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator;

/**
 * Decorates the Return part of the query structure. (clauses RETURN, LIMIT, SKIP, ORDER BY)
 */
class IlluminateToSettingDecorator implements IlluminateToQueryStructureDecorator
{
    public function __construct(private readonly array $values)
    {
    }

    public function decorate(Builder $illuminateBuilder, \PhpGraphGroup\CypherQueryBuilder\Contracts\Builder $cypherBuilder): void
    {
        foreach ($this->values as $property => $value) {
            $cypherBuilder->setting($property, $value);
        }
    }
}
