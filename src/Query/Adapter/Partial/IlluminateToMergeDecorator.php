<?php

namespace Vinelab\NeoEloquent\Query\Adapter\Partial;

use Illuminate\Contracts\Database\Query\Builder;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator;

/**
 * Decorates the Return part of the query structure. (clauses RETURN, LIMIT, SKIP, ORDER BY)
 */
class IlluminateToMergeDecorator implements IlluminateToQueryStructureDecorator
{
    public function __construct(private readonly array $values, private readonly array $uniqueBy, private readonly array $update) {

    }

    public function decorate(Builder $illuminateBuilder, \PhpGraphGroup\CypherQueryBuilder\Contracts\Builder $cypherBuilder): void
    {
        $cypherBuilder->merging($this->uniqueBy)
            ->onCreating($this->values)
            ->onMatching($this->update);
    }
}
