<?php

namespace Vinelab\NeoEloquent\Query\Adapter\Partial;

use Illuminate\Contracts\Database\Query\Builder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder as CypherBuilder;
use PhpGraphGroup\QueryBuilder\QueryStructure;
use Vinelab\NeoEloquent\Query\Adapter\IlluminateQueryPatternIterator;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator;
use Vinelab\NeoEloquent\Query\Grammar\VariableGrammar;

/**
 * Decorates the Return part of the query structure. (clauses RETURN, LIMIT, SKIP, ORDER BY)
 */
class DeletingDecorator implements IlluminateToQueryStructureDecorator
{
    public function decorate(Builder $illuminateBuilder, CypherBuilder $cypherBuilder): void
    {
        $parts = $cypherBuilder->getStructure()->graphPattern->chunk('match');
        foreach ($parts as $part) {
            $cypherBuilder->deleting($part->name->name);
        }
    }
}
