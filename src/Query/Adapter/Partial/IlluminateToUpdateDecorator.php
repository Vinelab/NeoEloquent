<?php

namespace Vinelab\NeoEloquent\Query\Adapter\Partial;

use Illuminate\Contracts\Database\Query\Builder;
use PhpGraphGroup\QueryBuilder\QueryStructure;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator;
use Vinelab\NeoEloquent\Query\Grammar\VariableGrammar;
use WikibaseSolutions\CypherDSL\Expressions\Procedures\Procedure;

/**
 * Decorates the Return part of the query structure. (clauses RETURN, LIMIT, SKIP, ORDER BY)
 */
class IlluminateToUpdateDecorator implements IlluminateToQueryStructureDecorator
{
    public function __construct(
        private readonly VariableGrammar $variables,
    ) {
    }

    public function decorate(Builder $illuminateBuilder, QueryStructure $cypherBuilder): QueryStructure
    {
        $from = $this->variables->toNodeOrRelationship($illuminateBuilder->from);
        if ($illuminateBuilder->aggregate) {
            $columns = $this->variables->columnize($illuminateBuilder->aggregate['columns'], $from);
            $function = $illuminateBuilder->aggregate['function'];

            $cypherBuilder->return = [Procedure::raw($function, $columns)->alias('aggregate')];

            return $cypherBuilder;
        }

        $columns = $illuminateBuilder->columns;

        $columns = $this->groups($illuminateBuilder, $columns);

        $cypherBuilder->limit = $illuminateBuilder->limit;
        $cypherBuilder->skip = $illuminateBuilder->offset;
        $cypherBuilder->orderBys = $illuminateBuilder->orders;

        if ($illuminateBuilder->groups) {
            /** @psalm-suppress PropertyTypeCoercion */
            $cypherBuilder->orderBys = array_merge($cypherBuilder->orderBys, $illuminateBuilder->groups);
        }
        if (is_array($illuminateBuilder->distinct)) {
            $cypherBuilder->distinct = count($illuminateBuilder->distinct) > 0;
        } else {
            $cypherBuilder->distinct = $illuminateBuilder->distinct;
        }

        $cypherBuilder->return = $this->variables->columnize($columns, $from);

        return $cypherBuilder;
    }

    public function groups(Builder $builder, array $columns): array
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
