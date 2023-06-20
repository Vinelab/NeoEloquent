<?php

namespace Vinelab\NeoEloquent\Query\Adapter\Partial;

use Illuminate\Contracts\Database\Query\Builder as IlluminateBuilder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder as CypherBuilder;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator;

/**
 * Decorates the Return part of the query structure. (clauses RETURN, LIMIT, SKIP, ORDER BY)
 */
class IlluminateToCreatingDecorating implements IlluminateToQueryStructureDecorator
{
    public function __construct(private readonly array $values, private readonly bool $batch)
    {
    }

    public function decorate(IlluminateBuilder $illuminateBuilder, CypherBuilder $cypherBuilder): void
    {
        if ($this->batch) {
            /** @psalm-suppress ArgumentTypeCoercion */
            $cypherBuilder->batchCreating($this->values);
        } else {
            $cypherBuilder->creating($this->values);
        }
    }
}
