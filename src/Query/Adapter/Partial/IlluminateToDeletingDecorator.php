<?php

namespace Vinelab\NeoEloquent\Query\Adapter\Partial;

use Illuminate\Contracts\Database\Query\Builder;
use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder as CypherBuilder;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator;

/**
 * Decorates the Return part of the query structure. (clauses RETURN, LIMIT, SKIP, ORDER BY)
 */
class IlluminateToDeletingDecorator implements IlluminateToQueryStructureDecorator
{
    public function decorate(Builder $illuminateBuilder, CypherBuilder $cypherBuilder): void
    {
        /**
         * @psalm-suppress InternalProperty
         * @psalm-suppress InternalMethod
         */
        $parts = $cypherBuilder->getStructure()->graphPattern->chunk('match');
        foreach ($parts as $part) {
            if (! $part instanceof RawExpression) {
                $cypherBuilder->deleting($part->name->name);
            }
        }
    }
}
