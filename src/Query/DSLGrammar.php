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
use WikibaseSolutions\CypherDSL\Alias;
use WikibaseSolutions\CypherDSL\Assignment;
use WikibaseSolutions\CypherDSL\Clauses\CallClause;
use WikibaseSolutions\CypherDSL\Clauses\OrderByClause;
use WikibaseSolutions\CypherDSL\Clauses\ReturnClause;
use WikibaseSolutions\CypherDSL\Clauses\SetClause;
use WikibaseSolutions\CypherDSL\Clauses\WhereClause;
use WikibaseSolutions\CypherDSL\Clauses\WithClause;
use WikibaseSolutions\CypherDSL\ExpressionList;
use WikibaseSolutions\CypherDSL\Functions\FunctionCall;
use WikibaseSolutions\CypherDSL\Functions\RawFunction;
use WikibaseSolutions\CypherDSL\GreaterThanOrEqual;
use WikibaseSolutions\CypherDSL\In;
use WikibaseSolutions\CypherDSL\IsNotNull;
use WikibaseSolutions\CypherDSL\IsNull;
use WikibaseSolutions\CypherDSL\Label;
use WikibaseSolutions\CypherDSL\LessThanOrEqual;
use WikibaseSolutions\CypherDSL\Literals\Literal;
use WikibaseSolutions\CypherDSL\Not;
use WikibaseSolutions\CypherDSL\Parameter;
use WikibaseSolutions\CypherDSL\Patterns\Node;
use WikibaseSolutions\CypherDSL\Property;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\QueryConvertable;
use WikibaseSolutions\CypherDSL\RawExpression;
use WikibaseSolutions\CypherDSL\Types\AnyType;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\BooleanType;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\PropertyType;
use WikibaseSolutions\CypherDSL\Variable;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_shift;
use function array_unshift;
use function count;
use function end;
use function explode;
use function in_array;
use function is_array;
use function is_string;
use function preg_split;
use function reset;
use function sprintf;
use function str_ends_with;
use function str_starts_with;
use function stripos;
use function strtolower;
use function substr;
use function trim;

/**
 * Grammar implementing the public Laravel Grammar API but returning Query Cypher Objects instead of strings.
 *
 * Todo: json, fulltext, joinSub, having, relationships, unionAggregate, Raw
 */
final class DSLGrammar
{
    private string $tablePrefix = '';
    /** @var array<string, callable(Builder, array, Query, DSLContext): array{0: AnyType, 1: list<CallClause>} */
    private array $wheres;

    public function __construct()
    {
        $this->wheres = [
            'Raw'            => Closure::fromCallable([$this, 'whereRaw']),
            'Basic'          => Closure::fromCallable([$this, 'whereBasic']),
            'In'             => Closure::fromCallable([$this, 'whereIn']),
            'NotIn'          => Closure::fromCallable([$this, 'whereNotIn']),
            'InRaw'          => Closure::fromCallable([$this, 'whereInRaw']),
            'NotInRaw'       => Closure::fromCallable([$this, 'whereNotInRaw']),
            'Null'           => Closure::fromCallable([$this, 'whereNull']),
            'NotNull'        => Closure::fromCallable([$this, 'whereNotNull']),
            'Between'        => Closure::fromCallable([$this, 'whereBetween']),
            'BetweenColumns' => Closure::fromCallable([$this, 'whereBetweenColumns']),
            'Date'           => Closure::fromCallable([$this, 'whereDate']),
            'Time'           => Closure::fromCallable([$this, 'whereTime']),
            'Day'            => Closure::fromCallable([$this, 'whereDay']),
            'Month'          => Closure::fromCallable([$this, 'whereMonth']),
            'Year'           => Closure::fromCallable([$this, 'whereYear']),
            'Column'         => Closure::fromCallable([$this, 'whereColumn']),
            'Nested'         => Closure::fromCallable([$this, 'whereNested']),
            'Exists'         => Closure::fromCallable([$this, 'whereExists']),
            'NotExists'      => Closure::fromCallable([$this, 'whereNotExists']),
            'RowValues'      => Closure::fromCallable([$this, 'whereRowValues']),
            'JsonBoolean'    => Closure::fromCallable([$this, 'whereJsonBoolean']),
            'JsonContains'   => Closure::fromCallable([$this, 'whereJsonContains']),
            'JsonLength'     => Closure::fromCallable([$this, 'whereJsonLength']),
            'FullText'       => Closure::fromCallable([$this, 'whereFullText']),
            'Sub'            => Closure::fromCallable([$this, 'whereSub']),
            'Relationship'   => Closure::fromCallable([$this, 'whereRelationship']),
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
     *
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

        return Query::node($this->tablePrefix.$table)->named($this->tablePrefix.($alias ?? $table));
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
        if ($value instanceof AnyType) {
            return $value;
        }

        if ($this->isExpression($value)) {
            return $this->getValue($value);
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
        return array_map(fn($x) => $this->wrap($x, false, $builder), $columns);
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
     *
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
     *
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

        $columns  = [];
        $segments = Arr::wrap($query->aggregate['columns']);
        if (count($segments) === 1 && trim($segments[0]) === '*') {
            $columns[] = Query::rawExpression('*');
        } else {
            foreach ($segments as $column) {
                $columns[] = $this->wrap($column, false, $query);
            }
        }

        $function = $query->aggregate['function'];
        if ($columns !== ['*'] && $query->distinct) {
            $columns = [Query::rawExpression('DISTINCT'), ...$columns];
        }
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

        // We need to check for right joins first
        // A right join forces us to us OPTIONAL MATCH for the currently matched node
        $containsRightJoin = false;
        foreach ($query->joins ?? [] as $join) {
            if ($join->type === 'right') {
                $containsRightJoin = true;
                break;
            }
        }

        if ($containsRightJoin) {
            $dsl->optionalMatch($node);
        } else {
            $dsl->match($node);
        }


        /** @var JoinClause $join */
        foreach ($query->joins ?? [] as $join) {
            $dsl->with($context->getVariables());

            $node = $this->wrapTable($join->table);
            $context->addVariable($node->getName());
            if ($join->type === 'cross') {
                $dsl->match($node);
            } elseif ($join->type === 'inner' || $join->type === 'right') {
                $dsl->match($node);
                $dsl->addClause($this->compileWheres($join, false, $dsl, $context));
            } elseif ($join->type === 'left') {
                $dsl->optionalMatch($node);
                $dsl->addClause($this->compileWheres($join, false, $dsl, $context));
            }
        }

        if (count($query->joins ?? [])) {
            $dsl->with($context->getVariables());
        }
    }

    /**
     * @param Builder $builder
     *
     * @return WhereClause
     */
    public function compileWheres(
        Builder $builder,
        bool $surroundParentheses,
        Query $query,
        DSLContext $context
    ): WhereClause {
        /** @var BooleanType $expression */
        $expression = null;
        foreach ($builder->wheres as $i => $where) {
            if ( ! array_key_exists($where['type'], $this->wheres)) {
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
        $column    = $this->wrap($where['column'], false, $query);
        $parameter = $this->parameter($where['value'], $context);

        if (in_array($where['operator'], ['&', '|', '^', '~', '<<', '>>', '>>>'])) {
            return new RawFunction('apoc.bitwise.op', [
                $this->wrap($where['column']),
                Query::literal($where['operator']),
                $this->parameter($query, $where['value']),
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

        return new In($this->wrap($where['column'], true, $query), $list);
    }

    private function whereNull(Builder $query, array $where): IsNull
    {
        return new IsNull($this->wrap($where['column'], true, $query));
    }

    private function whereNotNull(Builder $query, array $where): IsNotNull
    {
        return new IsNotNull($this->wrap($where['column'], true, $query));
    }

    private function whereBetween(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $min = Query::literal(reset($where['values']));
        $max = Query::literal(end($where['values']));

        $tbr = $this->whereBasic($query, ['column' => $where['column'], 'operator' => '>=', 'value' => $min], $context)
                    ->and(
                        $this->whereBasic(
                            $query,
                            ['column' => $where['column'], 'operator' => '<=', 'value' => $max],
                            $context
                        )
                    );

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
                    ->and(
                        $this->whereColumn(
                            $query,
                            ['column' => $where['column'], 'operator' => '<=', 'value' => $max],
                            $context
                        )
                    );

        if ($where['not']) {
            return new Not($tbr);
        }

        return $tbr;
    }

    private function whereDate(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $column    = $this->wrap($where['column'], false, $query);
        $parameter = Query::function()::date($this->parameter($where['value'], $context));

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter, false);
    }

    private function whereTime(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $column    = $this->wrap($where['column'], false, $query);
        $parameter = Query::function()::time($this->parameter($where['value'], $context));

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter, false);
    }

    private function whereDay(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $column    = $this->wrap($where['column'], false, $query)->property('day');
        $parameter = $this->parameter($where['value'], $context);

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter, false);
    }

    private function whereMonth(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $column    = $this->wrap($where['column'], false, $query)->property('month');
        $parameter = $this->parameter($where['value'], $context);

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter, false);
    }

    private function whereYear(Builder $query, array $where, DSLContext $context): BooleanType
    {
        $column    = $this->wrap($where['column'], false, $query)->property('year');
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

        $sub   = Query::new()->match($this->wrapTable($query->from));
        $calls = [];
        $tbr   = $this->compileWheres($nestedQuery, true, $sub, $context)->getExpression();
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
        if ( ! isset($where['query']->from)) {
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

        return [
            OperatorRepository::fromSymbol(
                $where['operator'],
                $this->wrap($where['column'], false, $builder),
                $subresult->getVariable()
            ),
            [new CallClause($sub)],
        ];
    }

    private function whereExists(Builder $builder, array $where, DSLContext $context, Query $query): BooleanType
    {
        /** @var Alias $subresult */
        $subresult = null;
        // Calls can be added subsequently without a WITH in between. Since this is the only comparator in
        // the WHERE series that requires a preceding clause, we don't need to worry about WITH statements between
        // possible multiple whereSubs in the same query depth.
        $query->call(function (Query $sub) use ($context, &$subresult, $where) {
            $select = $this->compileSelect($where['query']);

            $sub->with($context->getVariables());
            foreach ($select->getClauses() as $i => $clause) {
                if ($clause instanceof ReturnClause && $i + 1 === count($select->getClauses())) {
                    $subresult = $context->createSubResult($clause->getColumns()[0]);

                    $clause = new ReturnClause();
                    $clause->addColumn($subresult);
                }
                $sub->addClause($clause);
            }
        });

        return Query::rawExpression('exists('.$subresult->getVariable()->toQuery().')');
    }

    private function whereNotExists(Builder $builder, array $where, DSLContext $context, Query $query): BooleanType
    {
        return new Not($this->whereExists($builder, $where, $context, $query));
    }

    /**
     * @param array $where
     */
    private function whereRowValues(Builder $builder, array $where, DSLContext $context): BooleanType
    {
        $lhs = (new ExpressionList($this->columnize($where['columns'], $builder)))->toQuery();
        $rhs = (new ExpressionList($this->parameterize($where['values'], $context)))->toQuery();

        return OperatorRepository::fromSymbol(
            $where['operator'],
            new RawExpression($lhs),
            new RawExpression($rhs),
            false
        );
    }

    /**
     * @param array $where
     */
    public function whereRelationship(Builder $query, array $where, DSLContext $context): BooleanType
    {
        ['target' => $target, 'relationship' => $relationship] = $where;

        $from   = (new Node())->named($this->wrapTable($query->from)->getName()->getName());
        $target = (new Node())->named($this->wrapTable($target)->getName()->getName());

        if (str_ends_with($relationship, '>')) {
            return new RawExpression($from->relationshipTo($target, substr($relationship, 0, -1))->toQuery());
        }

        if (str_starts_with($relationship, '<')) {
            return new RawExpression($from->relationshipFrom($target, substr($relationship, 1))->toQuery());
        }

        return new RawExpression($from->relationshipUni($target, $relationship)->toQuery());
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
     *
     * @return string
     */
    private function whereJsonContains(Builder $query, array $where): string
    {
        throw new BadMethodCallException('Where JSON contains are not supported at the moment');
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
     * @param array $where
     */
    public function whereFullText(Builder $query, array $where): string
    {
        throw new BadMethodCallException('Fulltext where operations are not supported at the moment');
    }

    private function translateGroups(Builder $builder, Query $query, DSLContext $context): void
    {
        $groups = array_map(fn(string $x) => $this->wrap($x, false, $builder)->alias($x), $builder->groups ?? []);
        if (count($groups)) {
            $with    = $context->getVariables();
            $table   = $this->wrapTable($builder->from);
            $with    = array_filter($with, static fn(Variable $v) => $v->getName() !== $table->getName()->getName());
            $collect = Query::function()::raw('collect', [$table->getName()])->alias('groups');

            $query->with([...$with, ...$groups, $collect]);
        }
    }

    /**
     * Compile the "having" portions of the query.
     */
    private function translateHavings(Builder $builder, Query $query, DSLContext $context): void
    {
        /** @var BooleanType $expression */
        $expression = null;
        foreach ($builder->havings ?? [] as $i => $having) {
            // If the having clause is "raw", we can just return the clause straight away
            // without doing any more processing on it. Otherwise, we will compile the
            // clause into SQL based on the components that make it up from builder.
            if ($having['type'] === 'Raw') {
                $dslWhere = new RawExpression($having['sql']);
            } elseif ($having['type'] === 'between') {
                $dslWhere = $this->compileHavingBetween($having, $context);
            } else {
                $dslWhere = $this->compileBasicHaving($having, $context);
            }

            if ($expression === null) {
                $expression = $dslWhere;
            } elseif (strtolower($having['boolean']) === 'and') {
                $expression = $expression->and($dslWhere, (count($builder->wheres) - 1) === $i);
            } else {
                $expression = $expression->or($dslWhere, (count($builder->wheres) - 1) === $i);
            }
        }

        $where = new WhereClause();
        if ($expression !== null) {
            $where->setExpression($expression);
            $query->addClause($where);
        }
    }

    /**
     * Compile a basic having clause.
     */
    private function compileBasicHaving(array $having, DSLContext $context): BooleanType
    {
        $column    = new Variable($having['column']);
        $parameter = $this->parameter($having['value'], $context);

        if (in_array($having['operator'], ['&', '|', '^', '~', '<<', '>>', '>>>'])) {
            return new RawFunction('apoc.bitwise.op', [
                $column,
                Query::literal($having['operator']),
                $parameter,
            ]);
        }

        return OperatorRepository::fromSymbol($having['operator'], $column, $parameter, false);
    }

    /**
     * Compile a "between" having clause.
     */
    private function compileHavingBetween(array $having, DSLContext $context): BooleanType
    {
        $min = reset($having['values']);
        $max = end($having['values']);

        $gte = new GreaterThanOrEqual(new Variable($having['column']), $context->addParameter($min));
        $lte = new LessThanOrEqual(new Variable($having['column']), $context->addParameter($max));
        $tbr = $gte->and($lte);

        if ($having['not']) {
            return new Not($tbr);
        }

        return $tbr;
    }

    /**
     * Compile the "order by" portions of the query.
     */
    private function translateOrders(Builder $query, Query $dsl, array $orders = null): void
    {
        $orderBy = new OrderByClause();
        $orders  ??= $query->orders;
        $columns = $this->wrapColumns($query, Arr::pluck($orders, 'column'));
        $dirs    = Arr::pluck($orders, 'direction');
        foreach ($columns as $i => $column) {
            $orderBy->addProperty($column, $dirs[$i] === 'asc' ? null : 'desc');
        }

        $dsl->addClause($orderBy);
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

        if ( ! empty($builder->unionOrders)) {
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
            $node = $this->wrapTable($builder->from)->named($builder->from.$rowNumber);
            $query->create($node);

            $sets = [];
            foreach ($keys as $key => $value) {
                $sets[] = $node->property($key)->assign(Query::parameter('param'.$i));
                ++$i;
            }

            $query->set($sets);
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
        // There is no insert get id method in Neo4j
        // But you can just return the sequence property instead
        return $this->compileInsert($query, [$values])
            ->returning(
                $this->wrapTable($query->from)
                     ->named($query->from.'0')
                     ->property($sequence)
                     ->alias($sequence)
            );
    }

    public function compileInsertUsing(Builder $query, array $columns, string $sql): Query
    {
        throw new BadMethodCallException('CompileInsertUsing not implemented yet');
    }

    public function compileUpdate(Builder $builder, array $values): Query
    {
        $query = Query::new();

        $context = new DSLContext();

        $this->translateMatch($builder, $query, $context);

        $this->decorateUpdateAndRemoveExpressions($values, $query, $builder, $context);
        $this->decorateRelationships($builder, $query, $context);

        return $query;
    }

    public function compileUpsert(Builder $builder, array $values, array $uniqueBy, array $update): Query
    {
        $query = Query::new();

        $paramCount = 0;
        foreach ($values as $i => $valueRow) {
            $node   = $this->wrapTable($builder->from)->named($builder->from.$i);
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
            if ( ! empty($update)) {
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
     * @param Builder $builder
     *
     * @return Query
     */
    public function compileDelete(Builder $builder): Query
    {
        $original         = $builder->columns;
        $builder->columns = null;
        $query            = Query::new();

        $this->translateMatch($builder, $query, new DSLContext());

        $builder->columns = $original;

        return $query->delete($this->wrapTable($builder->from)->getName());
    }

    /**
     * Compile a truncate table statement into SQL.
     *
     * @param Builder $query
     *
     * @return Query[]
     */
    public function compileTruncate(Builder $query): array
    {
        $node   = $this->wrapTable($query->from);
        $delete = Query::new()
                       ->match($node)
                       ->delete($node->getName());

        return [$delete->toQuery() => []];
    }

    /**
     * Prepare the bindings for a delete statement.
     *
     * @param array $bindings
     *
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
     * Get the value of a raw expression.
     *
     * @param Expression $expression
     *
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

        $this->translateGroups($builder, $query, $context);
        $this->translateHavings($builder, $query, $context);

        if (count($builder->havings ?? [])) {
            $query->raw('UNWIND', 'groups AS '.$this->wrapTable($builder->from)->getName()->getName());
        }
    }

    private function decorateUpdateAndRemoveExpressions(
        array $values,
        Query $query,
        Builder $builder,
        DSLContext $context
    ): void {
        $expressions = [];

        foreach ($values as $key => $value) {
            $expressions[] = $this->wrap($key, true, $builder)->assign($context->addParameter($value));
        }

        if (count($expressions) > 0) {
            $query->set($expressions);
        }
    }

    private function decorateRelationships(Builder $builder, Query $query, DSLContext $context): void
    {
        $toRemove = [];
        $from     = $this->wrapTable($builder->from)->getName();
        foreach ($builder->relationships ?? [] as $relationship) {
            if ($relationship['target'] === null) {
                $toRemove[] = $relationship;
            } else {
                $to = Query::node()->named($this->wrapTable($relationship['target'])->getName()->getName());
                if ($relationship['direction'] === '<') {
                    $query->merge($from->relationshipFrom($to, $relationship['type']));
                } else {
                    $query->merge($from->relationshipTo($to, $relationship['type']));
                }
            }
        }

        if (count($toRemove) > 0) {
            $query->remove($toRemove);
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
     */
    private function wrapColumns(Builder $query, $columns): array
    {
        $tbr = [];
        foreach (Arr::wrap($columns) as $column) {
            $tbr[] = $this->wrap($column, false, $query);
        }

        return $tbr;
    }

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
}