<?php

namespace Vinelab\NeoEloquent\Query\Adapter\Partial;

use Illuminate\Contracts\Database\Query\Builder as IlluminateBuilder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder as CypherBuilder;
use PhpGraphGroup\QueryBuilder\QueryStructure;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator;
use Vinelab\NeoEloquent\Query\Grammar\VariableGrammar;
use WikibaseSolutions\CypherDSL\Query;

/**
 * Decorates the Return part of the query structure. (clauses RETURN, LIMIT, SKIP, ORDER BY)
 */
class InsertingDecorator implements IlluminateToQueryStructureDecorator
{
    public function __construct(private readonly array $values)
    {
    }

    public function decorate(IlluminateBuilder $illuminateBuilder, CypherBuilder $cypherBuilder): void
    {
        $cypherBuilder->create($this->values);
    }
}
