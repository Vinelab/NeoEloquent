<?php

namespace Vinelab\NeoEloquent\Query\Adapter\Partial;

use Illuminate\Contracts\Database\Query\Builder;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder as IBuilder;
use PhpGraphGroup\CypherQueryBuilder\Where\BooleanOperator;
use RuntimeException;
use Vinelab\NeoEloquent\Query\Contracts\IlluminateToQueryStructureDecorator;
use WikibaseSolutions\CypherDSL\Expressions\Property;
use WikibaseSolutions\CypherDSL\Expressions\RawExpression;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\Syntax\Alias;
use WikibaseSolutions\CypherDSL\Types\AnyType;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\BooleanType;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\PropertyType;
use Illuminate\Support\Str;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\StringType;
use PhpGraphGroup\CypherQueryBuilder\Contracts\Builder\WhereBuilder;

/**
 * @psalm-type WhereCommonArray = array{boolean: string, type: string}
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
    private function where(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
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
            'year' => $this->year($builder, $where, $cypherBuilder),
            'month' => $this->month($builder, $where, $cypherBuilder),
            'day' => $this->day($builder, $where, $cypherBuilder),
            'time' => $this->time($builder, $where, $cypherBuilder),
            'date' => $this->date($builder, $where, $cypherBuilder),
            'column' => $this->column($builder, $where, $cypherBuilder),
            'betweenColumn' => $this->betweenColumn($builder, $where, $cypherBuilder),
            'between' => $this->between($builder, $where, $cypherBuilder),
            'nested' => $this->nested($builder, $where, $cypherBuilder),
        };
    }


    /**
     * @param  WhereRawArray  $where
     */
    private function raw(array $where, IBuilder $cypherBuilder): void
    {
        $cypherBuilder->whereRaw($where['sql'], $this->compileBoolean($where['boolean']));
    }

    /**
     * @param  WhereBasicArray  $where
     */
    private function basic(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $cypherBuilder->where($builder->from . '.' . $where['column'], $where['operator'], $where['value'], $this->compileBoolean($where['boolean']));
    }

    /**
     * @param  WhereColumnInArray  $where
     */
    private function in(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $where['value'] = $where['values'];

        $this->basic($builder, $where, $cypherBuilder);
    }

    /**
     * @param  WhereColumnInArray  $where
     */
    private function notIn(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $cypherBuilder->whereNot(function (WhereBuilder $builder) use ($where, $cypherBuilder) {
             $this->in($builder, $where, $cypherBuilder);
        }, $where['boolean']);
    }

    /**
     * @param  WhereColumnInArray  $where
     */
    private function inRaw(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $value = Query::list(array_map(static fn ($x) => Query::literal($x), $where['values']));

        $this->whereBasicBinary('IN', $where['column'], $builder->from, $value, $stack);
    }

    /**
     * @param  WhereColumnInArray  $where
     */
    private function notInRaw(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        return $this->inRaw($stack, $where, $query)->not();
    }

    /**
     * @param  WhereColumnArray  $where
     */
    private function null(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        return $this->variables->wrapProperty($where['column'], $query->from)->isNull(false);
    }

    private function notNull(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        return $this->variables->wrapProperty($where['column'], $query->from)->isNotNull(false);
    }

    /**
     * @param  WhereColumns  $where
     */
    private function rowValues(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $lhs = $this->variables->columnize($where['columns'], $builder->from);

        return $this->fromBinarySymbol(
            $where['operator'],
            $lhs,
            $stack->addParameter($where['values'])
        );
    }

    private function notExists(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        return $this->exists($stack, $where)->not();
    }

    private function exists(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        ['query' => $query] = $where['query'];

        $join = $this->joinTranslator->translateStrict($query);

        $wheres = $this->combine($stack, $wheres);

        $sub = Query::new()
            ->addClause($match)
            ->where($wheres);

        return Query::exists($query, $query->wheres, false);
    }

    private function count(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $query = $this->exists($stack, $where)->toQuery();
        $query = 'COUNT'.substr($query, 6);

        return Query::rawExpression($query)->gte(Query::integer($where['count']));
    }

    /**
     * @param  WhereBasicArray  $where
     */
    private function year(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $where['column'] = $where['column'].'.year';

        return $this->basic($stack, $where, $builder);
    }

    /**
     * @param  WhereBasicArray  $where
     */
    private function month(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $where['column'] = $where['column'].'.month';

        return $this->basic($stack, $where, $builder);
    }

    /**
     * @param  WhereBasicArray  $where
     */
    private function day(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $where['column'] = $where['column'].'.day';

        return $this->basic($stack, $where, $builder);
    }

    /**
     * @param  WhereBasicArray  $where
     */
    private function time(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $where['column'] = $where['column'].'.time';

        return $this->basic($stack, $where, $builder);
    }

    /**
     * @param  WhereBasicArray  $where
     */
    private function date(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $where['column'] = $where['column'].'.date';

        return $this->basic($stack, $where, $builder);
    }

    /**
     * @param  WhereBasicColumnArray  $where
     */
    private function column(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $column = $this->variables->wrapProperty($where['first'], $builder->from, false);
        $value = $this->variables->wrapProperty($where['second'], $builder->from, false);

        return $this->fromBinarySymbol($where['operator'], $column, $value);
    }

    private function betweenColumn(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $first = reset($where['values']);
        $second = end($where['values']);

        $left = $this->column(['first' => $where['column'], 'second' => $first, 'operator' => '>='], $builder);
        $right = $this->column(['first' => $where['column'], 'second' => $second, 'operator' => '<='], $builder);

        $binary = $left->and($right);
        if ($where['not']) {
            $binary = $binary->not();
        }

        return $binary;
    }

    private function between(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $first = reset($where['values']);
        $second = end($where['values']);

        $left = $this->basic($stack, ['column' => $where['column'], 'value' => $first, 'operator' => '>='], $builder);
        $right = $this->basic($stack, ['column' => $where['column'], 'value' => $second, 'operator' => '<='], $builder);

        $binary = $left->and($right);
        if ($where['not']) {
            $binary = $binary->not();
        }

        return $binary;
    }

    private function nested(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        return $this->combine($stack, array_map(
            static fn (array $whereSub) => ['query' => $where['query'], 'where' => $whereSub],
            $where['query']->wheres)
        );
    }

    /**
     * @param  non-empty-list<array{builder: Builder, where: array}>  $wheres
     */
    public function combine(WhereBuilder $builder, array $where, IBuilder $cypherBuilder): void
    {
        $tbr = null;
        foreach ($wheres as $where) {
            ['builder' => $builder, 'where' => $where] = $where;

            $right = $this->where($builder, $where, $stack);
            $tbr = $this->compile($tbr, BooleanBinaryOperator::from(Str::upper($where['boolean'])), $right);
        }

        return $tbr;
    }

    private function compileBoolean(string $operator): BooleanOperator
    {
        return match (strtolower($operator)) {
            'and' => BooleanOperator::AND,
            'or' => BooleanOperator::OR,
            'xor' => BooleanOperator::XOR
        };
    }

    public function decorate(WhereBuilder $illuminateBuilder, IBuilder $cypherBuilder): void
    {
        foreach ($illuminateBuilder->wheres as $where) {
            $this->where($illuminateBuilder, $where, $cypherBuilder);
        }
        
        foreach ($illuminateBuilder->joins as $join) {
            $this->decorate($join, $cypherBuilder);
        }
    }
}
