<?php

namespace Vinelab\NeoEloquent\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use WikibaseSolutions\CypherDSL\Patterns\Node;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\RawExpression;
use WikibaseSolutions\CypherDSL\Types\AnyType;
use WikibaseSolutions\CypherDSL\Variable;
use function collect;
use function count;
use function is_array;
use function is_null;
use function preg_split;
use function stripos;
use function trim;

class CypherGrammar
{
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

        $node = $this->translateFrom($builder, $query);
        /** @var Variable $nodeVariable */
        $nodeVariable = $node->getName();

        $this->translateReturning($builder, $query, $nodeVariable);

        return $query->build();
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param  Expression|string  $value
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

    /**
     * Compile the "where" portions of the query.
     *
     * @param Builder $query
     * @return string
     */
    public function compileWheres(Builder $query)
    {
        // Each type of where clauses has its own compiler function which is responsible
        // for actually creating the where clauses SQL. This helps keep the code nice
        // and maintainable since each clause has a very small method that it uses.
        if (is_null($query->wheres)) {
            return '';
        }

        // If we actually have some where clauses, we will strip off the first boolean
        // operator, which is added by the query builders for convenience so we can
        // avoid checking for the first clauses in each of the compilers methods.
        if (count($sql = $this->compileWheresToArray($query)) > 0) {
            return $this->concatenateWhereClauses($query, $sql);
        }

        return '';
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
}