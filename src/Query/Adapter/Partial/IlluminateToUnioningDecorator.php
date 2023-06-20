<?php

namespace Vinelab\NeoEloquent\Query\Adapter\Partial;

use Closure;
use Illuminate\Contracts\Database\Query\Builder;
use Vinelab\NeoEloquent\Query\Adapter\IlluminateToQueryStructurePipeline;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator;

use function call_user_func;

/**
 * Decorates the Return part of the query structure. (clauses RETURN, LIMIT, SKIP, ORDER BY)
 */
class IlluminateToUnioningDecorator implements IlluminateToQueryStructureDecorator
{
    /**
     * @param Closure(): IlluminateToQueryStructurePipeline $pipeline
     */
    public function __construct(private readonly Closure $pipeline)
    {

    }

    public function decorate(Builder $illuminateBuilder, \PhpGraphGroup\CypherQueryBuilder\Contracts\Builder $cypherBuilder): void
    {
        if (count($illuminateBuilder->unions) === 0) {
            return;
        }

        $pipeline = call_user_func($this->pipeline);

        foreach ($illuminateBuilder->unions as $union) {
            $cypherBuilder->unioning($pipeline->pipe($union));
        }
    }
}
