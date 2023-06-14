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
class IlluminateToReturnDecorator implements IlluminateToQueryStructureDecorator
{
    public function decorate(Builder $illuminateBuilder, \PhpGraphGroup\CypherQueryBuilder\Contracts\Builder $cypherBuilder): void
    {
        $aggregate = $illuminateBuilder->aggregate;
        if ($aggregate) {
            $cypherBuilder->returningProcedure($aggregate['function'], 'aggregate', ...$aggregate['columns']);

            return;
        }

        $columns = $illuminateBuilder->columns;
        $columns = array_merge($columns, Arr::pluck($illuminateBuilder->groups, 'column'));

        $cypherBuilder->limiting($illuminateBuilder->limit);
        $cypherBuilder->skipping($illuminateBuilder->offset);
        $cypherBuilder->orderingBy(...$illuminateBuilder->orders);

        if ($illuminateBuilder->groups) {
            $cypherBuilder->orderingBy(...$illuminateBuilder->groups);
        }

        $cypherBuilder->distinct(count($illuminateBuilder->distinct) > 0);

        $cypherBuilder->returning(...$columns);
    }

    private function groups(Builder $builder, array $columns): array
    {
        if ($builder->groups) {
            $columns = array_merge(array_map(
                static fn ($group) => ['column' => $group, 'direction' => 'asc'],
                $builder->groups
            ), $columns);
        }

        return $columns;
    }
}
