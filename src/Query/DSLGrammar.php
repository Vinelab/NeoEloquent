<?php

namespace Vinelab\NeoEloquent\Query;

use BadMethodCallException;
use Closure;
use Illuminate\Database\Grammar;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use RuntimeException;
use Vinelab\NeoEloquent\DSLContext;
use Vinelab\NeoEloquent\LabelAction;
use Vinelab\NeoEloquent\OperatorRepository;
use Vinelab\NeoEloquent\Query\Wheres\Where;
use Vinelab\NeoEloquent\WhereContext;
use WikibaseSolutions\CypherDSL\Alias;
use WikibaseSolutions\CypherDSL\Assignment;
use WikibaseSolutions\CypherDSL\Clauses\CallClause;
use WikibaseSolutions\CypherDSL\Clauses\MatchClause;
use WikibaseSolutions\CypherDSL\Clauses\MergeClause;
use WikibaseSolutions\CypherDSL\Clauses\OptionalMatchClause;
use WikibaseSolutions\CypherDSL\Clauses\OrderByClause;
use WikibaseSolutions\CypherDSL\Clauses\ReturnClause;
use WikibaseSolutions\CypherDSL\Clauses\SetClause;
use WikibaseSolutions\CypherDSL\Clauses\WhereClause;
use WikibaseSolutions\CypherDSL\Clauses\WithClause;
use WikibaseSolutions\CypherDSL\ExpressionList;
use WikibaseSolutions\CypherDSL\Functions\FunctionCall;
use WikibaseSolutions\CypherDSL\Functions\RawFunction;
use WikibaseSolutions\CypherDSL\In;
use WikibaseSolutions\CypherDSL\IsNotNull;
use WikibaseSolutions\CypherDSL\IsNull;
use WikibaseSolutions\CypherDSL\Label;
use WikibaseSolutions\CypherDSL\Literals\Literal;
use WikibaseSolutions\CypherDSL\Not;
use WikibaseSolutions\CypherDSL\Parameter;
use WikibaseSolutions\CypherDSL\Patterns\Node;
use WikibaseSolutions\CypherDSL\Property;
use WikibaseSolutions\CypherDSL\PropertyMap;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\QueryConvertable;
use WikibaseSolutions\CypherDSL\RawExpression;
use WikibaseSolutions\CypherDSL\Types\AnyType;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\BooleanType;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\PropertyType;
use WikibaseSolutions\CypherDSL\Variable;
use function array_diff;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function array_shift;
use function array_unshift;
use function array_values;
use function count;
use function end;
use function explode;
use function head;
use function in_array;
use function is_array;
use function is_string;
use function last;
use function preg_split;
use function reset;
use function stripos;
use function strtolower;
use function trim;

/**
 * Grammar implementing the public Laravel Grammar API but returning Query Cypher Objects instead of strings.
 */
final class DSLGrammar
{
    private string $tablePrefix = '';
    /** @var array<string, callable(Builder, array, Query, DSLContext): array{0: AnyType, 1: list<CallClause>} */
    private array $wheres;

    public function __construct()
    {
        $this->wheres = [
            'Raw' => Closure::fromCallable([$this, 'whereRaw']),
            'Basic' => Closure::fromCallable([$this, 'whereBasic']),
            'In' => Closure::fromCallable([$this, 'whereIn']),
            'NotIn' => Closure::fromCallable([$this, 'whereNotIn']),
            'InRaw' => Closure::fromCallable([$this, 'whereInRaw']),
            'NotInRaw' => Closure::fromCallable([$this, 'whereNotInRaw']),
            'Null' => Closure::fromCallable([$this, 'whereNull']),
            'NotNull' => Closure::fromCallable([$this, 'whereNotNull']),
            'Between' => Closure::fromCallable([$this, 'whereBetween']),
            'BetweenColumns' => Closure::fromCallable([$this, 'whereBetweenColumns']),
            'Date' => Closure::fromCallable([$this, 'whereDate']),
            'Time' => Closure::fromCallable([$this, 'whereTime']),
            'Day' => Closure::fromCallable([$this, 'whereDay']),
            'Month' => Closure::fromCallable([$this, 'whereMonth']),
            'Year' => Closure::fromCallable([$this, 'whereYear']),
            'Column' => Closure::fromCallable([$this, 'whereColumn']),
            'Nested' => Closure::fromCallable([$this, 'whereNested']),
            'Exists' => Closure::fromCallable([$this, 'whereExists']),
            'NotSub' => Closure::fromCallable([$this, 'whereNotExists']),
            'RowValues' => Closure::fromCallable([$this, 'whereRowValues']),
            'JsonBoolean' => Closure::fromCallable([$this, 'whereJsonBoolean']),
            'JsonContains' => Closure::fromCallable([$this, 'whereJsonContains']),
            'JsonLength' => Closure::fromCallable([$this, 'whereJsonLength']),
            'FullText' => Closure::fromCallable([$this, 'whereFullText']),
            'Sub' => Closure::fromCallable([$this, 'whereSub']),
        ];
    }

    /**
     * @param array $values
     */
    public function wrapArray(array $values): ExpressionList
    {
        return Query::list(array_map([$this, 'wrap'], $values));
    }

    /**
     * @param Expression|QueryConvertable|string $table
     * @see Grammar::wrapTable
     */
    public function wrapTable($table): Node
    {
        if ($this->isExpression($table)) {
            $table = $this->getValue($table);
        }

        $alias = null;
        if (stripos(strtolower($table), ' as ') !== false) {
            $segments = preg_split('/\s+as\s+/i', $table);

            [$table, $alias] = $segments;
        }

        return Query::node($this->tablePrefix . $table)->named($this->tablePrefix . ($alias ?? $table));
    }

    /**
     * @param Expression|QueryConvertable|string $value
     *
     * @return Variable|Alias|Node
     *
     * @see Grammar::wrap
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function wrap($value, bool $prefixAlias = false, Builder $builder = null): AnyType
    {
        if ($this->isExpression($value)) {
            $value = $this->getValue($value);
        }

        if (stripos($value, ' as ') !== false) {
            return $this->wrapAliasedValue($value);
        }

        return $this->wrapSegments(explode('.', $value), $builder);
    }

    /**
     * Wrap a value that has an alias.
     */
    private function wrapAliasedValue(string $value): Alias
    {
        [$table, $alias] = preg_split('/\s+as\s+/i', $value);

        return Query::variable($table)->alias($alias);
    }

    /**
     * Wrap the given value segments.
     *
     * @return Property|Variable
     */
    private function wrapSegments(array $segments, ?Builder $query = null): AnyType
    {
        if (count($segments) === 1) {
            if (trim($segments[0]) === '*') {
                return Query::rawExpression('*');
            }

            if ($query !== null) {
                array_unshift($segments, $query->from);
            }
        }
        if ($query !== null && count($segments) === 1) {
            array_unshift($segments, $query->from);
        }
        $variable = $this->wrapTable(array_shift($segments));
        foreach ($segments as $segment) {
            $variable = $variable->property($segment);
        }

        return $variable;
    }

    /**
     * Convert an array of column names into a delimited string.
     *
     * @param string[] $columns
     *
     * @return array<Variable|Alias>
     */
    public function columnize(array $columns, Builder $builder = null): array
    {
        return array_map(fn ($x) => $this->wrap($x, false, $builder),  $columns);
    }

    /**
     * Create query parameter place-holders for an array.
     *
     * @param array $values
     *
     * @return Parameter[]
     */
    public function parameterize(array $values, ?DSLContext $context = null): array
    {
        $context ??= new DSLContext();
        return array_map(fn($x) => $this->parameter($x, $context), $values);
    }

    /**
     * Get the appropriate query parameter place-holder for a value.
     *
     * @param mixed $value
     */
    public function parameter($value, ?DSLContext $context = null): Parameter
    {
        $context ??= new DSLContext();

        $value = $this->isExpression($value) ? $this->getValue($value) : $value;

        return $context->addParameter($value);
    }

    /**
     * Quote the given string literal.
     *
     * @param string|array $value
     * @return PropertyType[]
     */
    public function quoteString($value): array
    {
        if (is_array($value)) {
            return Arr::flatten(array_map([$this, __FUNCTION__], $value));
        }

        return [Literal::string($value)];
    }

    /**
     * Determine if the given value is a raw expression.
     *
     * @param mixed $value
     */
    public function isExpression($value): bool
    {
        return $value instanceof Expression;
    }

    /**
     * Get the format for database stored dates.
     *
     * @note This function is not needed in Neo4J as we will immediately return DateTime objects.
     */
    public function getDateFormat(): string
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Get the grammar's table prefix.
     */
    public function getTablePrefix(): string
    {
        return $this->tablePrefix;
    }

    /**
     * Set the grammar's table prefix.
     *
     * @param string $prefix
     * @return self
     */
    public function setTablePrefix(string $prefix): self
    {
        $this->tablePrefix = $prefix;

        return $this;
    }

    public function compileSelect(Builder $builder, ?DSLContext $context = null): Query
    {
        $context ??= new DSLContext();

        if ($builder->unions) {
            return $this->translateUnions($builder, $builder->unions, $context);
        }

        $query = Query::new();

        $this->translateMatch($builder, $query, $context);


        if ($builder->aggregate) {
            $this->compileAggregate($builder, $query);
        } else {
            $this->translateColumns($builder, $query);

            if ($builder->orders) {
                $this->translateOrders($builder, $query);
            }

            if ($builder->limit) {
                $this->translateLimit($builder, $query);
            }

            if ($builder->offset) {
                $this->translateOffset($builder, $query);
            }
        }

        return $query;
    }

    private function compileAggregate(Builder $query, Query $dsl): void
    {
        $tbr = new ReturnClause();

        $columns = $this->wrapColumns($query, $query->aggregate['columns']);

        // All the aggregating functions used by laravel and mysql allow combining multiple columns as parameters.
        // In reality, they are a shorthand to check against a combination with null in them.
        // https://dba.stackexchange.com/questions/127564/how-to-use-count-with-multiple-columns
        // While neo4j does not directly support multiple parameters for the aggregating functions
        // provided in SQL, it does provide WITH and WHERE to achieve the same result.
        if (count($columns) > 1) {
            $this->buildWithClause($query, $columns, $dsl);

            $this->addWhereNotNull($columns, $dsl);

            $columns = [Query::rawExpression('*')];
        }

        $function = $query->aggregate['function'];
        $tbr->addColumn(Query::function()::raw($function, $columns)->alias('aggregate'));

        $dsl->addClause($tbr);
    }

    private function translateColumns(Builder $query, Query $dsl): void
    {
        $return = new ReturnClause();

        $return->setDistinct($query->distinct);

        foreach ($this->wrapColumns($query, $query->columns ?? ['*']) as $column) {
            $return->addColumn($column);
        }

        $dsl->addClause($return);
    }

    /**
     * @param Builder $query
     * @param Query $dsl
     */
    private function translateFrom(Builder $query, Query $dsl, DSLContext $context): void
    {
        $node = $this->wrapTable($query->from);
        $context->addVariable($node->getName());

        $dsl->match($node);

        /** @var JoinClause $join */
        foreach ($query->joins ?? [] as $join) {
            $dsl->with($context->getVariables());

            $node = $this->wrapTable($join->table);
            $context->addVariable($node->getName());
            if ($join->type === 'cross') {
                $dsl->match($node);
            } elseif ($join->type === 'inner') {
                $dsl->match($node);
                $dsl->addClause($this->compileWheres($join, false, $dsl, $context));
            }
        }
    }

    /**
     * TODO - can HAVING and WHERE be treated as the same in Neo4J?
     *
     * @param Builder $builder
     * @return WhereClause
     */
    public function compileWheres(Builder $builder, bool $surroundParentheses, Query $query, DSLContext $context): WhereClause
    {
        /** @var BooleanType $expression */
        $expression = null;
        foreach ($builder->wheres as $i => $where) {
            if (!array_key_exists($where['type'], $this->wheres)) {
                throw new RuntimeException(sprintf('Cannot find where operation named: "%s"', $where['type']));
            }

            $dslWhere = $this->wheres[$where['type']]($builder, $where, $context, $query);
            if (is_array($dslWhere)) {
                [$dslWhere, $calls] = $dslWhere;
                foreach ($calls as $call) {
                    $query->addClause($call);
                }
            }

            if ($expression === null) {
                $expression = $dslWhere;
            } elseif (strtolower($where['boolean']) === 'and') {
                $expression = $expression->and($dslWhere, (count($builder->wheres) - 1) === $i && $surroundParentheses);
            } else {
                $expression = $expression->or($dslWhere, (count($builder->wheres) - 1) === $i && $surroundParentheses);
            }
        }

        $where = new WhereClause();
        if ($expression !== null) {
            $where->setExpression($expression);
        }

        return $where;
    }

    private function whereRaw(Builder $query, array $where): RawExpression
    {
        return new RawExpression($where['sql']);
    }

    private function whereBasic(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $column = $this->wrap($where['column'], false, $query);
        $parameter = $this->parameter($where['value'], $context);

        if (in_array($where['operator'], ['&', '|', '^', '~', '<<', '>>', '>>>'])) {
            return new RawFunction('apoc.bitwise.op', [
                $this->wrap($where['column']),
                Query::literal($where['operator']),
                $this->parameter($query, $where['value'])
            ]);
        }

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter, false);
    }

    private function whereIn(Builder $query, array $where, DSLContext $context): In
    {
        return new In($this->wrap($where['column']), $this->parameter($where['values'], $context));
    }

    private function whereNotIn(Builder $query, array $where, DSLContext $context): Not
    {
        return new Not($this->whereIn($query, $where, $context));
    }

    private function whereNotInRaw(Builder $query, array $where, DSLContext $context): Not
    {
        return new Not($this->whereInRaw($query, $where, $context));
    }

    private function whereInRaw(Builder $query, array $where, DSLContext $context): In
    {
        $list = new ExpressionList(array_map(static fn($x) => Query::literal($x), $where['values']));

        return new In($this->wrap($where['column']), $list);
    }

    private function whereNull(Builder $query, array $where): IsNull
    {
        return new IsNull($this->wrap($where['column']));
    }

    private function whereNotNull(Builder $query, array $where): IsNotNull
    {
        return new IsNotNull($this->wrap($where['column']));
    }

    private function whereBetween(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $min = Query::literal(reset($where['values']));
        $max = Query::literal(end($where['values']));

        $tbr = $this->whereBasic($query, ['column' => $where['column'], 'operator' => '>=', 'value' => $min], $context)
            ->and($this->whereBasic($query, ['column' => $where['column'], 'operator' => '<=', 'value' => $max], $context));

        if ($where['not']) {
            return new Not($tbr);
        }

        return $tbr;
    }

    private function whereBetweenColumns(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $min = reset($where['values']);
        $max = end($where['values']);

        $tbr = $this->whereColumn($query, ['column' => $where['column'], 'operator' => '>=', 'value' => $min], $context)
            ->and($this->whereColumn($query, ['column' => $where['column'], 'operator' => '<=', 'value' => $max], $context));

        if ($where['not']) {
            return new Not($tbr);
        }

        return $tbr;
    }

    private function whereDate(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $column = $this->wrap($where['column'], false, $query);
        $parameter = Query::function()::date($this->parameter($where['value'], $context));

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter, false);
    }

    private function whereTime(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $column = $this->wrap($where['column'], false, $query);
        $parameter = Query::function()::time($this->parameter($where['value'], $context));

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter, false);
    }

    private function whereDay(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $column = $this->wrap($where['column'], false, $query)->property('day');
        $parameter = $this->parameter($where['value'], $context);

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter, false);
    }

    private function whereMonth(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $column = $this->wrap($where['column'], false, $query)->property('month');
        $parameter = $this->parameter($where['value'], $context);

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter, false);
    }

    private function whereYear(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $column = $this->wrap($where['column'], false, $query)->property('year');
        $parameter = $this->parameter($where['value'], $context);

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter, false);
    }

    private function whereColumn(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $x = $this->wrap($where['first'], false, $query);
        $y = $this->wrap($where['second'], false, $query);

        return OperatorRepository::fromSymbol($where['operator'], $x, $y, false);
    }

    private function whereNested(Builder $query, array $where, DSLContext $context): array
    {
        /** @var Builder $nestedQuery */
        $nestedQuery = $where['query'];

        $sub = Query::new()->match($this->wrapTable($query->from));
        $calls = [];
        $tbr = $this->compileWheres($nestedQuery, true, $sub, $context)->getExpression();
        foreach ($sub->getClauses() as $clause) {
            if ($clause instanceof CallClause) {
                $calls[] = $clause;
            }
        }

        foreach ($nestedQuery->getBindings() as $key => $binding) {
            $query->addBinding([$key => $binding]);
        }

        return [$tbr, $calls];
    }

    private function whereSub(Builder $builder, array $where, DSLContext $context): array
    {
        /** @var Alias $subresult */
        $subresult = null;
        // Calls can be added subsequently without a WITH in between. Since this is the only comparator in
        // the WHERE series that requires a preceding clause, we don't need to worry about WITH statements between
        // possible multiple whereSubs in the same query depth.
        $sub = Query::new();
        if (!isset($where['query']->from)) {
            $where['query']->from = $builder->from;
        }
        $select = $this->compileSelect($where['query']);

        $sub->with($context->getVariables());
        foreach ($select->getClauses() as $clause) {
            if ($clause instanceof ReturnClause) {
                $subresult = $clause->getColumns()[0];
                if ($subresult instanceof Alias) {
                    $context->createSubResult($subresult);
                } else {
                    $subresult = $context->createSubResult($subresult);
                    $clause->addColumn($subresult);
                }
            }
            $sub->addClause($clause);
        }

        return [OperatorRepository::fromSymbol($where['operator'], $this->wrap($where['column'], false, $builder), $subresult->getVariable()), [new CallClause($sub)]];
    }

    private function whereExists(WhereContext $context): BooleanType
    {
        /** @var Alias $subresult */
        $subresult = null;
        // Calls can be added subsequently without a WITH in between. Since this is the only comparator in
        // the WHERE series that requires a preceding clause, we don't need to worry about WITH statements between
        // possible multiple whereSubs in the same query depth.
        $context->getQuery()->call(function (Query $sub) use ($context, &$subresult) {
            $select = $this->compileSelect($context->getWhere()['query']);

            $sub->with($context->getContext()->getVariables());
            foreach ($select->getClauses() as $clause) {
                if ($clause instanceof ReturnClause) {
                    $collect = Query::function()::raw('collect', [$clause->getColumns()[0]]);
                    $subresult = $context->getContext()->createSubResult($collect);

                    $clause = new ReturnClause();
                    $clause->addColumn($subresult);
                }
                $sub->addClause($clause);
            }
        });

        $where = $context->getWhere();

        return $subresult->getVariable()->property('length')->equals($this->wrap($where['column']));
    }

    private function whereNotExists(WhereContext $context): BooleanType
    {
        return new Not($this->whereExists($context));
    }

    /**
     * @param array $where
     */
    private function whereRowValues(Builder $builder, array $where, DSLContext $context): BooleanType
    {
        $lhs = (new ExpressionList($this->columnize($where['columns'], $builder)))->toQuery();
        $rhs = (new ExpressionList($this->parameterize($where['values'], $context)))->toQuery();

        return OperatorRepository::fromSymbol($where['operator'], new RawExpression($lhs), new RawExpression($rhs), false);
    }

    /**
     * @param array $where
     */
    private function whereJsonBoolean(Builder $query, array $where): string
    {
        throw new BadMethodCallException('Where on JSON types are not supported at the moment');
    }

    /**
     * Compile a "where JSON contains" clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    private function whereJsonContains(Builder $query, array $where): string
    {
        throw new BadMethodCallException('Where JSON contains are not supported at the moment');
    }

    /**
     * @param string $column
     * @param string $value
     */
    private function compileJsonContains(string $column, string $value): string
    {
        throw new BadMethodCallException('This database engine does not support JSON contains operations.');
    }

    /**
     * @param mixed $binding
     */
    public function prepareBindingForJsonContains($binding): string
    {
        throw new BadMethodCallException('JSON operations are not supported at the moment');
    }

    /**
     * @param array $where
     */
    private function whereJsonLength(Builder $query, array $where): string
    {
        throw new BadMethodCallException('JSON operations are not supported at the moment');
    }

    /**
     * @param string $column
     * @param string $operator
     * @param string $value
     */
    private function compileJsonLength(string $column, string $operator, string $value): string
    {
        throw new BadMethodCallException('JSON operations are not supported at the moment');
    }

    /**
     * @param array $where
     */
    public function whereFullText(Builder $query, array $where): string
    {
        throw new BadMethodCallException('Fulltext where operations are not supported at the moment');
    }

    private function translateGroups(Builder $query, array $groups, Query $dsl): void
    {
//        return 'group by '.$this->columnize($groups);
    }

    /**
     * Compile the "having" portions of the query.
     */
    private function translateHavings(Builder $query, array $havings, Query $dsl): void
    {
//        $sql = implode(' ', array_map([$this, 'compileHaving'], $havings));
//
//        return 'having '.$this->removeLeadingBoolean($sql);
    }

    /**
     * Compile a single having clause.
     *
     * @param array $having
     * @return string
     */
    private function compileHaving(array $having): string
    {
        // If the having clause is "raw", we can just return the clause straight away
        // without doing any more processing on it. Otherwise, we will compile the
        // clause into SQL based on the components that make it up from builder.
        if ($having['type'] === 'Raw') {
            return $having['boolean'] . ' ' . $having['sql'];
        }

        if ($having['type'] === 'between') {
            return $this->compileHavingBetween($having);
        }

        return $this->compileBasicHaving($having);
    }

    /**
     * Compile a basic having clause.
     *
     * @param array $having
     * @return string
     */
    private function compileBasicHaving(array $having): string
    {
        $column = $this->wrap($having['column']);

        $parameter = $this->parameter($having['value']);

        return $having['boolean'] . ' ' . $column . ' ' . $having['operator'] . ' ' . $parameter;
    }

    /**
     * Compile a "between" having clause.
     *
     * @param array $having
     * @return string
     */
    private function compileHavingBetween(array $having): string
    {
        $between = $having['not'] ? 'not between' : 'between';

        $column = $this->wrap($having['column']);

        $min = $this->parameter(head($having['values']));

        $max = $this->parameter(last($having['values']));

        return $having['boolean'] . ' ' . $column . ' ' . $between . ' ' . $min . ' and ' . $max;
    }

    /**
     * Compile the "order by" portions of the query.
     */
    private function translateOrders(Builder $query, Query $dsl, array $orders = null): void
    {
        $orderBy = new OrderByClause();
        $orders ??= $query->orders;
        $columns = $this->wrapColumns($query, Arr::pluck($orders, 'column'));
        $dirs = Arr::pluck($orders, 'direction');
        foreach ($columns as $i => $column) {
            $orderBy->addProperty($column, $dirs[$i] === 'asc' ? null : 'desc');
        }

        $dsl->addClause($orderBy);
    }

    /**
     * Compile the query orders to an array.
     *
     * @param Builder $query
     * @param array $orders
     * @return array
     */
    private function compileOrdersToArray(Builder $query, array $orders): array
    {
        return array_map(function ($order) {
            return $order['sql'] ?? ($this->wrap($order['column']) . ' ' . $order['direction']);
        }, $orders);
    }

    public function compileRandom(string $seed): FunctionCall
    {
        return Query::function()::raw('rand', []);
    }

    /**
     * Compile the "limit" portions of the query.
     */
    private function translateLimit(Builder $query, Query $dsl, int $limit = null): void
    {
        $dsl->limit(Query::literal()::decimal($limit ?? (int)$query->limit));
    }

    /**
     * Compile the "offset" portions of the query.
     */
    private function translateOffset(Builder $query, Query $dsl, int $offset = null): void
    {
        $dsl->skip(Query::literal()::decimal($offset ?? (int)$query->offset));
    }

    /**
     * Compile the "union" queries attached to the main query.
     */
    private function translateUnions(Builder $builder, array $unions, DSLContext $context): Query
    {
        $builder->unions = [];

        $query = $this->compileSelect($builder, $context);
        foreach ($unions as $union) {
            $toUnionize = $this->compileSelect($union['query'], $context);
            $query->union($toUnionize, (bool)($union['all'] ?? false));
        }

        $builder->unions = $unions;

        if (!empty($builder->unionOrders)) {
            $this->translateOrders($builder, $query, $builder->unionOrders);
        }

        if (isset($builder->unionLimit)) {
            $this->translateLimit($builder, $query, (int)$builder->unionLimit);
        }

        if (isset($builder->unionOffset)) {
            $this->translateOffset($builder, $query, (int)$builder->unionOffset);
        }

        return $query;
    }

    /**
     * Compile a union aggregate query into SQL.
     */
    private function translateUnionAggregate(Builder $query, Query $dsl): void
    {
//        $sql = $this->compileAggregate($query, $query->aggregate);
//
//        $query->aggregate = null;
//
//        return $sql.' from ('.$this->compileSelect($query).') as '.$this->wrapTable('temp_table');
    }

    public function compileExists(Builder $query): Query
    {
        $dsl = Query::new();

        $this->translateMatch($query, $dsl, new DSLContext());

        if (count($dsl->clauses) && $dsl->clauses[count($dsl->clauses) - 1] instanceof ReturnClause) {
            unset($dsl->clauses[count($dsl->clauses) - 1]);
        }

        $return = new ReturnClause();
        $return->addColumn(new RawExpression('count(*) > 0'), 'exists');
        $dsl->addClause($return);

        return $dsl;
    }

    public function compileInsert(Builder $builder, array $values): Query
    {
        $query = Query::new();

        $i = 0;
        foreach ($values as $rowNumber => $keys) {
            $node = $this->wrapTable($builder->from)->named($builder->from . $rowNumber);
            $query->create($node);

            $sets = [];
            foreach ($keys as $key => $value) {
                $sets[] = $node->property($key)->assign(Query::parameter('param'.$i));
            }

            $query->set($sets);

            ++$i;
        }

        return $query;
    }

    /**
     * Compile an insert ignore statement into SQL.
     *
     * @param Builder $query
     * @param array $values
     *
     * @throws RuntimeException
     */
    public function compileInsertOrIgnore(Builder $query, array $values): Query
    {
        return $this->compileInsert($query, $values);
    }

    /**
     * @param array $values
     * @param string $sequence
     */
    public function compileInsertGetId(Builder $query, array $values, string $sequence): Query
    {
        throw new BadMethodCallException('Neo4j driver does not support last insert id functionality');
    }

    public function compileInsertUsing(Builder $query, array $columns, string $sql): Query
    {
        throw new BadMethodCallException('CompileInsertUsing not implemented yet');
    }

    public function compileUpdate(Builder $query, array $values): Query
    {
        $dsl = Query::new();

        $context = new DSLContext();
        $node = $this->wrapTable($query->from);

        $this->translateMatch($query, $dsl, $context);

        $this->decorateUpdateAndRemoveExpressions($values, $dsl, $node, $context);

        return $dsl;
    }

    public function compileUpsert(Builder $builder, array $values, array $uniqueBy, array $update): Query
    {
        $query = Query::new();

        $paramCount = 0;
        foreach ($values as $i => $valueRow) {
            $node = $this->wrapTable($builder->from)->named($builder->from . $i);
            $keyMap = [];

            $onCreate = new SetClause();
            foreach ($valueRow as $key => $value) {
                $keyMap[$key] = Query::parameter('param'.$paramCount);
                $onCreate->addAssignment(new Assignment($node->getName()->property($key), $keyMap[$key]));
                ++$paramCount;
            }

            foreach ($uniqueBy as $uniqueAttribute) {
                $node->withProperty($uniqueAttribute, $keyMap[$uniqueAttribute]);
            }

            $onUpdate = null;
            if (!empty($update)) {
                $onUpdate = new SetClause();
                foreach ($update as $key) {
                    $onUpdate->addAssignment(new Assignment($node->getName()->property($key), $keyMap[$key]));
                }
            }

            $query->merge($node, $onCreate, $onUpdate);
        }

        return $query;
    }

    /**
     * Compile a delete statement into SQL.
     *
     * @param Builder $query
     * @return Query
     */
    public function compileDelete(Builder $query): Query
    {
        $original = $query->columns;
        $query->columns = null;

        $dsl = $this->compileSelect($query);

        $query->columns = $original;

        return $dsl->delete($this->getMatchedNode());
    }

    /**
     * Compile a truncate table statement into SQL.
     *
     * @param Builder $query
     * @return Query[]
     */
    public function compileTruncate(Builder $query): array
    {
        $delete = Query::new()
            ->match(Query::node($query->from))
            ->delete(Query::node($query->from));

        return [$delete];
    }

    /**
     * Prepare the bindings for a delete statement.
     *
     * @param array $bindings
     * @return array
     */
    public function prepareBindingsForDelete(array $bindings): array
    {
        return $this->valuesToKeys($bindings);
    }

    public function supportsSavepoints(): bool
    {
        return false;
    }

    public function compileSavepoint(string $name): string
    {
        throw new BadMethodCallException('Savepoints aren\'t supported in Neo4J');
    }

    public function compileSavepointRollBack(string $name): string
    {
        throw new BadMethodCallException('Savepoints aren\'t supported in Neo4J');
    }

    /**
     * Wrap the given JSON selector.
     *
     * @param string $value
     * @return string
     *
     * @throws RuntimeException
     */
    private function wrapJsonSelector(string $value): string
    {
        throw new RuntimeException('This database engine does not support JSON operations.');
    }

    /**
     * Get the value of a raw expression.
     *
     * @param Expression $expression
     * @return mixed
     */
    public function getValue(Expression $expression)
    {
        return $expression->getValue();
    }

    private function translateMatch(Builder $builder, Query $query, DSLContext $context): void
    {
        if (($builder->unions || $builder->havings) && $builder->aggregate) {
            $this->translateUnionAggregate($builder, $query);
        }
        $this->translateFrom($builder, $query, $context);

        $query->addClause($this->compileWheres($builder, false, $query, $context));
        $this->translateHavings($builder, $builder->havings ?? [], $query);

        $this->translateGroups($builder, $builder->groups ?? [], $query);
    }

    private function decorateUpdateAndRemoveExpressions(array $values, Query $dsl, Node $node, DSLContext $context): void
    {
        $expressions = [];
        $removeExpressions = [];

        foreach ($values as $key => $value) {
            if ($value instanceof LabelAction) {
                $labelExpression = new Label($node->getName(), [$value->getLabel()]);

                if ($value->setsLabel()) {
                    $expressions[] = $labelExpression;
                } else {
                    $removeExpressions[] = $labelExpression;
                }
            } else {
                $expressions[] = $node->property($key)->assign($context->addParameter($value));
            }
        }

        if (count($expressions) > 0) {
            $dsl->set($expressions);
        }

        if (count($removeExpressions) > 0) {
            $dsl->remove($removeExpressions);
        }
    }

    private function valuesToKeys(array $values): array
    {
        return Collection::make($values)
            ->map(static fn(array $value) => array_keys($value))
            ->flatten()
            ->filter(static fn($x) => is_string($x))
            ->unique()
            ->toArray();
    }

    private function buildMergeExpression(array $uniqueBy, Node $node, Builder $query): RawExpression
    {
        $map = Query::map([]);
        foreach ($uniqueBy as $column) {
            $map->addProperty($column, new RawExpression('values.' . Node::escape($column)));
        }
        $label = new Label($node->getName(), [$query->from]);

        return new RawExpression('(' . $label->toQuery() . ' ' . $map->toQuery() . ')');
    }

    private function buildSetClause(array $update, Node $node): SetClause
    {
        $setClause = new SetClause();
        foreach ($update as $key) {
            $assignment = $node->property($key)->assign(new RawExpression('values.' . Node::escape($key)));

            $setClause->addAssignment($assignment);
        }

        return $setClause;
    }

    public function getBitwiseOperators(): array
    {
        return OperatorRepository::bitwiseOperations();
    }

    public function getOperators(): array
    {
        return [];
    }

    /**
     * @param list<string>|string $columns
     *
     * @return PropertyType
     */
    private function wrapColumns(Builder $query, $columns): array
    {
        $tbr = [];
        foreach (Arr::wrap($columns) as $column) {
            $tbr[] = $this->wrap($column, false, $query);
        }

        return $tbr;
    }

    /**
     * @param PropertyType $columns
     */
    private function buildWithClause(Builder $query, array $columns, Query $dsl): void
    {
        $with = new WithClause();

        if ($query->distinct) {
            $with->addEntry(Query::rawExpression('DISTINCT'));
        }

        foreach ($columns as $column) {
            $with->addEntry($column);
        }

        $dsl->addClause($with);
    }

    /**
     * @param PropertyType $columns
     * @param Query $dsl
     * @return void
     */
    private function addWhereNotNull(array $columns, Query $dsl): void
    {
        $expression = null;
        foreach ($columns as $column) {
            $test = $column->isNotNull(false);
            if ($expression === null) {
                $expression = $test;
            } else {
                $expression = $expression->or($test, false);
            }
        }

        $where = new WhereClause();
        $where->setExpression($expression);
        $dsl->addClause($where);
    }

    /**
     * @param DSLContext $context
     * @param Builder $builder
     * @return void
     */
    private function storeBindingsInBuilder(DSLContext $context, Builder $builder): void
    {
    }
}