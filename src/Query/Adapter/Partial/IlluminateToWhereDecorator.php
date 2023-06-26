<?php

namespace Vinelab\NeoEloquent\Query\Adapter\Partial;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Str;
use PhpGraphGroup\CypherQueryBuilder\Common\ParameterStack;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\SubQueryBuilder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\WhereBuilder;
use PhpGraphGroup\CypherQueryBuilder\Where\BooleanOperator;
use Vinelab\NeoEloquent\Processors\Processor;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator;
use WikibaseSolutions\CypherDSL\Query;

use function array_map;
use function end;
use function explode;
use function reset;
use function str_contains;
use function str_replace;

/**
 * @psalm-type WhereCommonArray = array{boolean: string, type: string, not: bool, ...}
 * @psalm-type WhereBasicArray = WhereCommonArray&array{column: string, operator: string, value: mixed}
 * @psalm-type WhereColumnArray = WhereCommonArray&array{column: string, operator: string}
 * @psalm-type WhereBasicColumnArray = WhereCommonArray&array{first: string, second: string, operator: string}
 * @psalm-type WhereColumnInArray = WhereColumnArray&array{values: list<mixed>}
 * @psalm-type WhereColumns = WhereCommonArray&array{columns: list<string>, operator: string, values: list<mixed>}
 * @psalm-type WhereRawArray = WhereCommonArray&array{sql: string}
 *
 * @psalm-suppress ArgumentTypeCoercion
 */
class IlluminateToWhereDecorator implements IlluminateToQueryStructureDecorator
{
    private function where(Builder $builder, array $where, SubQueryBuilder $cypherBuilder): void
    {
        /** @psalm-suppress InternalProperty */
        $stack = $cypherBuilder->getStructure()->parameters;

        $method = Str::camel($where['type']);

        match ($method) {
            'raw' => $this->raw($where, $cypherBuilder),
            'basic' => $this->basic($builder, $where, $cypherBuilder),
            'in' => $this->in($builder, $where, $cypherBuilder),
            'notIn' => $this->notIn($builder, $where, $cypherBuilder),
            'inRaw' => $this->inRaw($builder, $where, $cypherBuilder),
            'notInRaw' => $this->notInRaw($builder, $where, $cypherBuilder),
            'null' => $this->null($builder, $where, $cypherBuilder),
            'notNull' => $this->notNull($builder, $where, $cypherBuilder),
            'rowValues' => $this->rowValues($builder, $where, $cypherBuilder),
            'notExists' => $this->notExists($builder, $where, $cypherBuilder),
            'exists' => $this->exists($builder, $where, $cypherBuilder),
            'count' => $this->count($builder, $where, $cypherBuilder),
            'year' => $this->year($builder, $where, $cypherBuilder, $stack),
            'month' => $this->month($builder, $where, $cypherBuilder, $stack),
            'day' => $this->day($builder, $where, $cypherBuilder, $stack),
            'time' => $this->time($builder, $where, $cypherBuilder, $stack),
            'date' => $this->date($builder, $where, $cypherBuilder, $stack),
            'column' => $this->column($builder, $where, $cypherBuilder),
            'betweenColumn' => $this->betweenColumn($builder, $where, $cypherBuilder),
            'between' => $this->between($builder, $where, $cypherBuilder, $stack),
            'nested' => $this->nested($builder, $where, $cypherBuilder),
        };
    }


    /**
     * @param WhereRawArray $where
     */
    private function raw(array $where, WhereBuilder $cypherBuilder): void
    {
        $cypherBuilder->whereRaw($where['sql'], $this->compileBoolean($where['boolean']));
    }

    /**
     * @param WhereBasicArray $where
     */
    private function basic(Builder $builder, array $where, WhereBuilder $cypherBuilder): void
    {
        $cypherBuilder->where(
            $where['column'],
            $where['operator'],
            $where['value'],
            $this->compileBoolean($where['boolean'])
        );
    }

    /**
     * @param WhereColumnInArray $where
     */
    private function in(Builder $builder, array $where, WhereBuilder $cypherBuilder): void
    {
        $where['value'] = $where['values'];

        $where['operator'] = 'in';

        $this->basic($builder, $where, $cypherBuilder);
    }

    /**
     * @param WhereColumnInArray $where
     */
    private function notIn(Builder $builder, array $where, WhereBuilder $cypherBuilder): void
    {
        $cypherBuilder->whereNot(function (SubQueryBuilder $cypherBuilder) use ($builder, $where) {
            $this->in($builder, $where, $cypherBuilder);
        }, $this->compileBoolean($where['boolean']));
    }

    /**
     * @param WhereColumnInArray $where
     */
    private function inRaw(Builder $builder, array $where, WhereBuilder $cypherBuilder): void
    {
        $this->in($builder, $where, $cypherBuilder);
    }

    /**
     * @param WhereColumnInArray $where
     */
    private function notInRaw(Builder $builder, array $where, WhereBuilder $cypherBuilder): void
    {
        $cypherBuilder->whereNot(function (SubQueryBuilder $cypherBuilder) use ($builder, $where) {
            $this->inRaw($builder, $where, $cypherBuilder);
        }, $this->compileBoolean($where['boolean']));
    }

    /**
     * @param WhereColumnArray $where
     */
    private function null(Builder $builder, array $where, WhereBuilder $cypherBuilder): void
    {
        $cypherBuilder->whereNull($where['column'], $this->compileBoolean($where['boolean']));
    }

    private function notNull(Builder $builder, array $where, WhereBuilder $cypherBuilder): void
    {
        $cypherBuilder->whereNot(function (SubQueryBuilder $cypherBuilder) use ($builder, $where) {
            $this->null($builder, $where, $cypherBuilder);
        }, $this->compileBoolean($where['boolean']));
    }

    /**
     * @param WhereColumns $where
     */
    private function rowValues(Builder $builder, array $where, WhereBuilder $cypherBuilder): void
    {
        foreach ($where['columns'] as $i => $column) {
            $cypherBuilder->whereEquals($column, $where['values'][$i], BooleanOperator::AND);
        }
    }

    private function notExists(Builder $builder, array $where, WhereBuilder $cypherBuilder): void
    {
        $cypherBuilder->whereNot(function (SubQueryBuilder $cypherBuilder) use ($builder, $where) {
            $this->exists($builder, $where, $cypherBuilder);
        }, $where['boolean']);
    }

    private function exists(Builder $builder, array $where, WhereBuilder $cypherBuilder): void
    {
        $cypherBuilder->whereExists(function (SubQueryBuilder $subQueryBuilder) use ($builder) {
            $this->decorate($builder, $subQueryBuilder);
        }, $this->compileBoolean($where['boolean']));
    }

    private function count(Builder $builder, array $where, WhereBuilder $cypherBuilder): void
    {
        $cypherBuilder->whereCount(function (SubQueryBuilder $subQueryBuilder) use ($builder) {
            $this->decorate($builder, $subQueryBuilder);
        }, $where['count'], '>=', $this->compileBoolean($where['boolean']));
    }

    /**
     * @param WhereBasicArray $where
     */
    private function year(Builder $builder, array $where, WhereBuilder $cypherBuilder, ParameterStack $stack): void
    {
        $this->temporal($builder, $where, $cypherBuilder, 'year', $stack);
    }

    /**
     * @param WhereBasicArray $where
     */
    private function temporal(Builder $builder, array $where, WhereBuilder $cypherBuilder, string $attribute, ParameterStack $stack): void
    {
        $cypher = Query::variable(str_replace(['<', '>'], '', explode(':', $builder->from))[0])
                       ->property($where['column'])
                       ->property($attribute)
                       ->equals($stack->add($where['value']))
                       ->toQuery();

        $cypherBuilder->whereRaw($cypher, $this->compileBoolean($where['boolean']));
    }

    /**
     * @param WhereBasicArray $where
     */
    private function month(Builder $builder, array $where, WhereBuilder $cypherBuilder, ParameterStack $stack): void
    {
        $this->temporal($builder, $where, $cypherBuilder, 'month', $stack);
    }

    /**
     * @param WhereBasicArray $where
     */
    private function day(Builder $builder, array $where, WhereBuilder $cypherBuilder, ParameterStack $stack): void
    {
        $this->temporal($builder, $where, $cypherBuilder, 'day', $stack);
    }

    /**
     * @param WhereBasicArray $where
     */
    private function time(Builder $builder, array $where, WhereBuilder $cypherBuilder, ParameterStack $stack): void
    {
        $this->temporal($builder, $where, $cypherBuilder, 'time', $stack);
    }

    /**
     * @param WhereBasicArray $where
     */
    private function date(Builder $builder, array $where, WhereBuilder $cypherBuilder, ParameterStack $stack): void
    {
        $this->temporal($builder, $where, $cypherBuilder, 'date', $stack);
    }

    /**
     * @param WhereBasicColumnArray $where
     */
    private function column(Builder $builder, array $where, WhereBuilder $cypherBuilder): void
    {
        $cypherBuilder->wherePropertiesEquals(
            $where['first'],
            $where['second'],
            $this->compileBoolean($where['boolean'])
        );
    }

    /**
     * @param WhereColumns $where
     */
    private function betweenColumn(Builder $builder, array $where, SubQueryBuilder $cypherBuilder): void
    {
        $callable = function (SubQueryBuilder $cypherBuilder) use ($builder, $where): void {
            $first  = reset($where['values']);
            $second = end($where['values']);

            $where['first']  = $where['column'];
            $where['second'] = $first;
            $this->column($builder, $where, $cypherBuilder);

            $where['second'] = $second;
            $this->column($builder, $where, $cypherBuilder);
        };

        if ($where['not']) {
            $cypherBuilder->whereNot($callable);
        } else {
            $callable($cypherBuilder);
        }
    }

    /**
     * @param WhereColumnArray $where
     */
    private function between(Builder $builder, array $where, SubQueryBuilder $cypherBuilder): void
    {
        $callable = function (SubQueryBuilder $cypherBuilder) use ($builder, $where): void {
            $first  = reset($where['values']);
            $second = end($where['values']);

            $where['operator'] = '=';
            $where['value'] = $first;
            $this->basic($builder, $where, $cypherBuilder);

            $where['value'] = $second;
            $this->basic($builder, $where, $cypherBuilder);
        };

        if ($where['not']) {
            $cypherBuilder->whereNot($callable);
        } else {
            $callable($cypherBuilder);
        }
    }

    private function nested(Builder $builder, array $where, WhereBuilder $cypherBuilder): void
    {
        $cypherBuilder->whereInner(function (SubQueryBuilder $cypherBuilder) use ($builder) {
            $this->decorate($builder, $cypherBuilder);
        }, $this->compileBoolean($where['boolean']));
    }

    private function compileBoolean(string $operator): BooleanOperator
    {
        return match (strtolower($operator)) {
            'and' => BooleanOperator::AND,
            'or' => BooleanOperator::OR,
            'xor' => BooleanOperator::XOR
        };
    }

    public function decorate(Builder $illuminateBuilder, SubQueryBuilder $cypherBuilder): void
    {
        /** @psalm-suppress RedundantConditionGivenDocblockType */
        foreach (array_merge($illuminateBuilder->wheres, ($illuminateBuilder->havings ?? [])) as $where) {
            $this->where($illuminateBuilder, $where, $cypherBuilder);
        }

        /** @psalm-suppress RedundantConditionGivenDocblockType */
        foreach (($illuminateBuilder->joins ?? []) as $join) {
            $this->decorate($join, $cypherBuilder);
        }
    }
}
