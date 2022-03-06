<?php

namespace Vinelab\NeoEloquent\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Traits\Macroable;
use Laudis\Neo4j\Databags\Pair;
use WikibaseSolutions\CypherDSL\BinaryOperator;
use WikibaseSolutions\CypherDSL\Patterns\Node;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\RawExpression;
use WikibaseSolutions\CypherDSL\Types\AnyType;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\BooleanType;
use WikibaseSolutions\CypherDSL\Variable;
use function count;
use function is_a;
use function preg_split;
use function stripos;

class CypherGrammar
{
    use Macroable;

    /**
     * The components that make up a select clause.
     *
     * @var string[]
     */
    protected $selectComponents = [
        'from',
        // TODO - 'aggregate',
        // TODO - 'joins',
        'wheres',
        // TODO - 'groups',
        // TODO - 'havings',
        // TODO - 'lock',
        'columns',
        'orders',
        'limit',
        'offset',
    ];

    /**
     * Compile a select query into SQL.
     */
    public function compileSelect(Builder $builder): string
    {
        $query = Query::new();

        /** @var Variable $node */
        $node = $this->translateFrom($builder, $query)->getName();

        $this->translateWheres($builder, $query, $node);
        $this->translateReturning($builder, $query, $node);

        return $query->build();
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param Expression|string $value
     */
    private function wrap($value, Variable $node): AnyType
    {
        if ($value instanceof Expression) {
            return new RawExpression($value->getValue());
        }

        if (stripos($value, ' as ') !== false) {
            $segments = preg_split('/\s+as\s+/i', $value);
            $property = $node->property($segments[0])->toQuery();

            return Query::rawExpression($property . ' AS ' . $segments[1]);
        }

        return $node->property($value);
    }

    private function translateWheres(Builder $builder, Query $query, Variable $node): void
    {
        if ($builder->wheres === []) {
            return;
        }

        $i = 0;
        /** @var BooleanType|null $expression */
        $expression = null;
        do {
            $expression = $this->buildFromWhere($builder->wheres[$i], $expression);

            ++$i;
        } while (count($builder->wheres) > $i);

        $query->where($expression);
    }

    private function translateReturning(Builder $builder, Query $query, Variable $node): void
    {
        $columns = $builder->columns ?? ['*'];
        // Distinct is only possible for an entire return
        $distinct = $builder->distinct !== false;

        if ($columns === ['*']) {
            $query->returning($node, $distinct);
        } else {
            $query->returning(array_map(fn($x) => $this->wrap($x, $node), $columns), $distinct);
        }
    }

    private function translateFrom(Builder $builder, Query $query): Node
    {
        $node = Query::node()->labeled($builder->from);

        $query->match($node);

        return $node;
    }

    private function buildFromWhere(array $where, ?BooleanType $expression): BooleanType
    {
        $newClass = $where['type'];
        if (is_a($newClass, BinaryOperator::class, true)) {
            $newExpression = new $newClass($where['']);
        }

        if ($expression) {
            return $expression->and($newExpression);
        }

        return $newExpression;
    }
}