<?php

namespace Vinelab\NeoEloquent\Query\Adapter\Partial;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PhpGraphGroup\CypherQueryBuilder\Common\RawExpression;
use Vinelab\NeoEloquent\Grammars\CypherGrammar;
use Vinelab\NeoEloquent\Processors\Processor;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator;

use function array_search;
use function in_array;
use function str_contains;

/**
 * Decorates the Return part of the query structure. (clauses RETURN, LIMIT, SKIP, ORDER BY)
 */
class IlluminateToReturnDecorator implements IlluminateToQueryStructureDecorator
{
    /**
     *  @psalm-suppress RedundantConditionGivenDocblockType
     *  @psalm-suppress DocblockTypeContradiction
     */
    public function decorate(Builder $illuminateBuilder, \PhpGraphGroup\CypherQueryBuilder\Contracts\Builder $cypherBuilder): void
    {
        $aggregate = $illuminateBuilder->aggregate;
        if ($aggregate) {
            if (in_array('*', $aggregate['columns'])) {
                $aggregate['columns'] = [ new RawExpression('*') ];
            }

            if ($illuminateBuilder->distinct) {
                $aggregate['columns'] = [ new RawExpression('DISTINCT'), ... $aggregate['columns']];
            }

            $cypherBuilder->returningProcedure($aggregate['function'], 'aggregate', ...$aggregate['columns']);

            return;
        }

        $columns = array_merge($illuminateBuilder->columns ?? [], $illuminateBuilder->groups ?? []);

        if ($illuminateBuilder->limit !== null) {
            $cypherBuilder->limiting($illuminateBuilder->limit);
        }

        if ($illuminateBuilder->offset !== null) {
            $cypherBuilder->skipping($illuminateBuilder->offset);
        }

        $direction = 'asc';
        $first = Arr::first($illuminateBuilder->orders);
        if (is_array($first)) {
            $direction = $first['direction'];
        }
        $direction = Str::upper($direction);
        $orders = array_merge(Arr::pluck($illuminateBuilder->orders ?? [], 'column'), $illuminateBuilder->groups ?? []);

        if (count($orders) > 0) {
            /** @psalm-suppress ArgumentTypeCoercion */
            $cypherBuilder->orderingBy($direction, ...$orders);
        }

        $distinct = $illuminateBuilder->distinct;
        $cypherBuilder->distinct(is_bool($distinct) ? $distinct : count($distinct) > 0);

        if (count($columns) > 0) {
            $usedRaw = false;
            foreach ($columns as $column) {
                if ($column instanceof Expression) {
                    $column = $column->getValue(new CypherGrammar());
                }
                if (!$usedRaw && str_contains($column, '*')) {
                    $cypherBuilder->returningRaw('*');
                    $usedRaw = true;
                } else {
                    $cypherBuilder->returning(Processor::standardiseColumn($column));
                }
            }
        } else {
            $cypherBuilder->returningAll();
        }
    }
}
