<?php

namespace Vinelab\NeoEloquent\Query\Adapter\Partial;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Arr;
use PhpGraphGroup\QueryBuilder\QueryStructure;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator;
use Vinelab\NeoEloquent\Query\Grammar\VariableGrammar;
use WikibaseSolutions\CypherDSL\Expressions\Procedures\Procedure;

/**
 * Decorates the Return part of the query structure. (clauses RETURN, LIMIT, SKIP, ORDER BY)
 */
class IlluminateToMergeDecorator implements IlluminateToQueryStructureDecorator
{
    public function decorate(Builder $illuminateBuilder, \PhpGraphGroup\CypherQueryBuilder\Contracts\Builder $cypherBuilder): void
    {

    }
}
