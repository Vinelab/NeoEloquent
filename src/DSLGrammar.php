<?php

namespace Vinelab\NeoEloquent;

use BadMethodCallException;
use Illuminate\Database\Grammar;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;
use WikibaseSolutions\CypherDSL\Clauses\MatchClause;
use WikibaseSolutions\CypherDSL\Clauses\MergeClause;
use WikibaseSolutions\CypherDSL\Clauses\OptionalMatchClause;
use WikibaseSolutions\CypherDSL\Clauses\ReturnClause;
use WikibaseSolutions\CypherDSL\Clauses\SetClause;
use WikibaseSolutions\CypherDSL\Clauses\WhereClause;
use WikibaseSolutions\CypherDSL\ExpressionList;
use WikibaseSolutions\CypherDSL\Functions\FunctionCall;
use WikibaseSolutions\CypherDSL\Functions\RawFunction;
use WikibaseSolutions\CypherDSL\In;
use WikibaseSolutions\CypherDSL\Label;
use WikibaseSolutions\CypherDSL\Literals\Literal;
use WikibaseSolutions\CypherDSL\Not;
use WikibaseSolutions\CypherDSL\Parameter;
use WikibaseSolutions\CypherDSL\Patterns\Node;
use WikibaseSolutions\CypherDSL\Property;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\QueryConvertable;
use WikibaseSolutions\CypherDSL\RawExpression;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\BooleanType;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\PropertyType;
use WikibaseSolutions\CypherDSL\Variable;
use function array_diff;
use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function collect;
use function count;
use function end;
use function explode;
use function head;
use function is_array;
use function is_string;
use function last;
use function preg_split;
use function reset;
use function str_ireplace;
use function str_replace;
use function stripos;
use function strtolower;
use function trim;

/**
 * Grammar implementing the public Laravel Grammar API but returning Query Cypher Objects instead of strings.
 */
final class DSLGrammar
{
    private string $tablePrefix = '';

    /**
     * @param  array  $values
     */
    public function wrapArray(array $values): ExpressionList
    {
        return new ExpressionList(array_map([$this, 'wrap'], $values));
    }

    /**
     * @see Grammar::wrapTable
     *
     * @param  Expression|QueryConvertable|string  $table
     */
    public function wrapTable($table): Node
    {
        if ($this->isExpression($table)) {
            return Query::node($this->getValue($table));
        }

        $table = $this->tablePrefix . $table;

        if (stripos($table, ' as ') !== false) {
            $segments = preg_split('/\s+as\s+/i', $table);

            return Query::node($segments[0])->named($this->tablePrefix.$segments[1]);
        }

        return Query::node($table);
    }

    /**
     * @see Grammar::wrap
     *
     * @param  Expression|QueryConvertable|string  $value
     *
     * @return RawExpression|Variable
     */
    public function wrap($value, bool $prefixAlias = false): QueryConvertable
    {
        if ($this->isExpression($value)) {
            return new RawExpression($this->getValue($value));
        }

        if (stripos($value, ' as ') !== false) {
            return $this->wrapAliasedValue($value, $prefixAlias);
        }

        return new RawExpression($this->wrapSegments(explode('.', $value)));
    }

    /**
     * Wrap a value that has an alias.
     */
    private function wrapAliasedValue(string $value, bool $prefixAlias = false): Variable
    {
        $segments = preg_split('/\s+as\s+/i', $value);

        // If we are wrapping a table we need to prefix the alias with the table prefix
        // as well in order to generate proper syntax. If this is a column of course
        // no prefix is necessary. The condition will be true when from wrapTable.
        if ($prefixAlias) {
            $segments[1] = $this->tablePrefix.$segments[1];
        }

        return new Variable(Query::escape($segments[0]) . ' AS ' . Query::escape($segments[1]));
    }

    /**
     * Wrap the given value segments.
     */
    private function wrapSegments(array $segments): string
    {
        return collect($segments)->map(function ($segment, $key) use ($segments) {
            return $key === 0 && count($segments) > 1
                ? $this->wrapTable($segment)->toQuery()
                : Query::escape($segment);
        })->implode('.');
    }

    /**
     * Convert an array of column names into a delimited string.
     *
     * @param string[]  $columns
     *
     * @return array<Variable|RawExpression>
     */
    public function columnize(array $columns): array
    {
        return array_map([$this, 'wrap'], $columns);
    }

    /**
     * Create query parameter place-holders for an array.
     *
     * @param  array  $values
     *
     * @return Parameter[]
     */
    public function parameterize(array $values): array
    {
        return array_map([$this, 'parameter'], $values);
    }

    /**
     * Get the appropriate query parameter place-holder for a value.
     *
     * @param  mixed  $value
     */
    public function parameter($value, Builder $query = null): Parameter
    {
        $parameter = $this->isExpression($value) ?
            new Parameter($this->getValue($value)) :
            new Parameter(str_replace('-', '', 'param'.Str::uuid()->toString()));

        if ($query) {
            $query->addBinding([$parameter->getParameter() => $value], 'where');
        }

        return $parameter;
    }

    /**
     * Quote the given string literal.
     *
     * @param  string|array  $value
     * @return PropertyType[]
     */
    public function quoteString($value): array
    {
        if (is_array($value)) {
            return Arr::flatten(array_map([$this, __FUNCTION__], $value));
        }

        return [Query::literal($value)];
    }

    /**
     * Determine if the given value is a raw expression.
     *
     * @param  mixed  $value
     */
    public function isExpression($value): bool
    {
        return $value instanceof Expression || $value instanceof QueryConvertable;
    }

    /**
     * Get the format for database stored dates.
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
     * @param  string  $prefix
     * @return self
     */
    public function setTablePrefix(string $prefix): self
    {
        $this->tablePrefix = $prefix;

        return $this;
    }

    private ?Node $node = null;

    public function compileSelect(Builder $builder): Query
    {
        $dsl = Query::new();

        $this->translateMatch($builder, $dsl);

        if ($builder->aggregate === []) {
            $this->translateColumns($builder, $builder->columns ?? ['*'], $dsl);
            $this->translateOrders($builder, $builder->orders ?? [], $dsl);
            $this->translateLimit($builder, $builder->limit, $dsl);
            $this->translateOffset($builder, $builder->offset, $dsl);
        }

        return $dsl;
    }

    private function translateAggregate(Builder $query, array $aggregate): void
    {
        if ($query->distinct) {
            $columns = [];
            foreach ($aggregate['columns'] as $column) {
                $columns[$column] = $this->column($column);
            }
            $dsl->with($columns);
        }
        $column = $this->columnize($aggregate['columns']);

        // If the query has a "distinct" constraint and we're not asking for all columns
        // we need to prepend "distinct" onto the column name so that the query takes
        // it into account when it performs the aggregating operations on the data.
        if (is_array($query->distinct)) {
            $column = 'distinct '.$this->columnize($query->distinct);
        } elseif ($query->distinct && $column !== '*') {
            $column = 'distinct '.$column;
        }

        //return 'select '.$aggregate['function'].'('.$column.') as aggregate';
    }

    private function translateColumns(Builder $query, array $columns, Query $dsl): void
    {
        $return = new ReturnClause();
        $return->setDistinct($query->distinct);
        $dsl->addClause($return);

        if ($columns === ['*']) {
            /** @noinspection NullPointerExceptionInspection */
            $return->addColumn($this->getMatchedNode()->getName());
        } else {
            foreach ($columns as $column) {
                $alias = '';
                if (str_contains(strtolower($column), ' as ')) {
                    [$column, $alias] = explode(' as ', str_ireplace(' as ', ' as ', $column));
                }

                $return->addColumn($this->getMatchedNode()->property($column), $alias);
            }
        }
    }

    private function translateFrom(Builder $query, ?string $table, Query $dsl): void
    {
        $this->initialiseNode($query, $table);

        $dsl->match($this->node);
    }

    private function translateJoins(Builder $query, array $joins, Query $dsl = null): void
    {
//        return collect($joins)->map(function ($join) use ($query) {
//            $table = $this->wrapTable($join->table);
//
//            $nestedJoins = is_null($join->joins) ? '' : ' '.$this->compileJoins($query, $join->joins);
//
//            $tableAndNestedJoins = is_null($join->joins) ? $table : '('.$table.$nestedJoins.')';
//
//            return trim("{$join->type} join {$tableAndNestedJoins} {$this->compileWheres($join)}");
//        })->implode(' ');
    }

    public function compileWheres(Builder $query): WhereClause
    {
        /** @var BooleanType $expression */
        $expression = null;
        foreach ($query->wheres as $where) {
            $dslWhere = $this->{"where{$where['type']}"}($query, $where);
            if ($expression === null) {
                $expression = $dslWhere;
            } elseif (strtolower($where['boolean']) === 'and') {
                $expression = $expression->and($dslWhere);
            } else {
                $expression = $expression->or($dslWhere);
            }
        }

        $where = new WhereClause();
        if ($expression !== null) {
            $where->setExpression($expression);
        }

        return $where;
    }

    /**
     * @param array $where
     */
    private function whereRaw(Builder $query, $where): RawExpression
    {
        return new RawExpression($where['sql']);
    }

    /**
     * @param array $where
     */
    private function whereBasic(Builder $query, $where): BooleanType
    {
        $column = $this->column($where['column']);
        $parameter = $this->addParameter($query, $where['value']);

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter);
    }

    /**
     * @param array $where
     */
    private function whereBitwise(Builder $query, $where): RawFunction
    {
        return new RawFunction('apoc.bitwise.op', [
            $this->column($where['column']),
            Query::literal($where['operator']),
            $this->addParameter($query, $where['value'])
        ]);
    }

    /**
     * @param array $where
     */
    private function whereIn(Builder $query, $where): In
    {
        return new In(
            $this->column($where['column']),
            $this->addParameter($query, $where['values'])
        );
    }

    /**
     * @param array $where
     */
    private function whereNotIn(Builder $query, $where): Not
    {
        return new Not($this->whereIn($query, $where));
    }

    /**
     * @param array $where
     */
    private function whereNotInRaw(Builder $query, $where): Not
    {
        return new Not($this->whereInRaw($query, $where));
    }

    /**
     * @param array $where
     */
    private function whereInRaw(Builder $query, $where): In
    {
        return new In(
            $this->column($where['column']),
            new ExpressionList(array_map(static fn ($x) => Query::literal($x), $where['values']))
        );
    }

    /**
     * @param array $where
     */
    private function whereNull(Builder $query, $where): RawExpression
    {
        return new RawExpression($this->column($where['column'])->toQuery() . ' IS NULL');
    }

    /**
     * @param array $where
     */
    private function whereNotNull(Builder $query, $where): RawExpression
    {
        return new RawExpression($this->column($where['column'])->toQuery() . ' IS NOT NULL');
    }

    /**
     * @param array $where
     */
    private function whereBetween(Builder $query, $where): BooleanType
    {
        $min = Query::literal(reset($where['values']));
        $max = Query::literal(end($where['values']));

        $tbr = $this->whereBasic($query, ['column' => $where['column'], 'operator' => '>=', 'value' => $min])
            ->and($this->whereBasic($query, ['column' => $where['column'], 'operator' => '<=', 'value' => $max]));

        if ($where['not']) {
            return new Not($tbr);
        }

        return $tbr;
    }

    /**
     * @param array $where
     */
    private function whereBetweenColumns(Builder $query, $where): BooleanType
    {
        $min = reset($where['values']);
        $max = end($where['values']);

        $tbr = $this->whereColumn($query, ['column' => $where['column'], 'operator' => '>=', 'value' => $min])
            ->and($this->whereColumn($query, ['column' => $where['column'], 'operator' => '<=', 'value' => $max]));

        if ($where['not']) {
            return new Not($tbr);
        }

        return $tbr;
    }

    /**
     * @param array $where
     */
    private function whereDate(Builder $query, $where): BooleanType
    {
        return $this->whereBasic($query, $where);
    }

    /**
     * @param array $where
     */
    private function whereTime(Builder $query, $where): BooleanType
    {
        return $this->dateBasedWhere('epochMillis', $query, $where);
    }

    /**
     * @param array $where
     */
    private function whereDay(Builder $query, $where): BooleanType
    {
        return $this->dateBasedWhere('day', $query, $where);
    }

    /**
     * @param array $where
     */
    private function whereMonth(Builder $query, $where): BooleanType
    {
        return $this->dateBasedWhere('month', $query, $where);
    }

    /**
     * @param array $where
     */
    private function whereYear(Builder $query, $where): BooleanType
    {
        return $this->dateBasedWhere('year', $query, $where);
    }

    /**
     * @param array $where
     */
    private function dateBasedWhere($type, Builder $query, $where): BooleanType
    {
        $column = new RawExpression($this->column($where['column'])->toQuery() . '.' . Query::escape($type));
        $parameter = $this->addParameter($query, $where['value']);

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter);
    }

    /**
     * @param array $where
     */
    private function whereColumn(Builder $query, $where): BooleanType
    {
        $x = $this->column($where['first']);
        $y = $this->column($where['second']);

        return OperatorRepository::fromSymbol($where['operator'], $x, $y);
    }

    /**
     * @param array $where
     */
    private function whereNested(Builder $query, $where): BooleanType
    {
        $where['query']->wheres[count($where['query']->wheres) - 1]['boolean'] = 'and';

        return $this->compileWheres($where['query'], $where['query']->wheres)->getExpression();
    }

    /**
     * @param array $where
     */
    private function whereSub(Builder $query, $where): BooleanType
    {
        throw new BadMethodCallException('Sub selects are not supported at the moment');
    }

    /**
     * @param array $where
     */
    private function whereExists(Builder $query, $where): BooleanType
    {
        throw new BadMethodCallException('Exists on queries are not supported at the moment');
    }

    /**
     * @param array $where
     */
    private function whereNotExists(Builder $query, $where): BooleanType
    {
        return new Not($this->whereExists($query, $where));
    }

    /**
     * @param array $where
     */
    private function whereRowValues(Builder $query, $where): BooleanType
    {
        $expressions = [];
        foreach ($where['columns'] as $column) {
            $expressions[] = $this->column($column);
        }
        $lhs = new ExpressionList($expressions);

        $expressions = [];
        foreach ($where['values'] as $value) {
            $expressions[] = $this->addParameter($query, $value);
        }
        $rhs = new ExpressionList($expressions);

        return OperatorRepository::fromSymbol($where['operator'], $lhs, $rhs);
    }

    /**
     * @param array $where
     */
    private function whereJsonBoolean(Builder $query, $where): string
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
    private function whereJsonContains(Builder $query, $where): string
    {
        throw new BadMethodCallException('Where JSON contains are not supported at the moment');
    }

    /**
     * @param string $column
     * @param string $value
     */
    private function compileJsonContains($column, $value): string
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
    private function whereJsonLength(Builder $query, $where): string
    {
        throw new BadMethodCallException('JSON operations are not supported at the moment');
    }

    /**
     * @param string $column
     * @param string $operator
     * @param string $value
     */
    private function compileJsonLength($column, $operator, $value): string
    {
        throw new BadMethodCallException('JSON operations are not supported at the moment');
    }

    /**
     * @param array $where
     */
    public function whereFullText(Builder $query, $where): string
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
    private function compileBasicHaving($having): string
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
    private function compileHavingBetween($having): string
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
    private function translateOrders(Builder $query, array $orders, Query $dsl): void
    {
//        if (! empty($orders)) {
//            return 'order by '.implode(', ', $this->compileOrdersToArray($query, $orders));
//        }
//
//        return '';
    }

    /**
     * Compile the query orders to an array.
     *
     * @param Builder $query
     * @param array $orders
     * @return array
     */
    private function compileOrdersToArray(Builder $query, $orders): array
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
     *
     * @param Builder $query
     * @param string|int $limit
     */
    private function translateLimit(Builder $query, $limit, Query $dsl): void
    {
//        return 'limit '.(int) $limit;
    }

    /**
     * Compile the "offset" portions of the query.
     *
     * @param Builder $query
     * @param string|int $offset
     */
    private function translateOffset(Builder $query, $offset, Query $dsl): void
    {
//        return 'offset '.(int) $offset;
    }

    /**
     * Compile the "union" queries attached to the main query.
     */
    private function translateUnions(Builder $query, array $unions, Query $dsl): void
    {
//        $sql = '';
//
//        foreach ($query->unions as $union) {
//            $sql .= $this->compileUnion($union);
//        }
//
//        if (! empty($query->unionOrders)) {
//            $sql .= ' '.$this->compileOrders($query, $query->unionOrders);
//        }
//
//        if (isset($query->unionLimit)) {
//            $sql .= ' '.$this->compileLimit($query, $query->unionLimit);
//        }
//
//        if (isset($query->unionOffset)) {
//            $sql .= ' '.$this->compileOffset($query, $query->unionOffset);
//        }
//
//        return ltrim($sql);
    }

    /**
     * Compile a single union statement.
     *
     * @param array $union
     * @return string
     */
    private function compileUnion(array $union): string
    {
        $conjunction = $union['all'] ? ' union all ' : ' union ';

        return $conjunction . $this->wrapUnion($union['query']->toSql());
    }

    /**
     * Wrap a union subquery in parentheses.
     *
     * @param string $sql
     * @return string
     */
    private function wrapUnion($sql): string
    {
        return '(' . $sql . ')';
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

        $this->translateSelect($query, $dsl);

        foreach ($dsl->clauses as $i => $clause) {
            if ($clause instanceof MatchClause) {
                $optional = new OptionalMatchClause();
                foreach ($clause->getPatterns() as $pattern) {
                    $optional->addPattern($pattern);
                }
                $dsl->clauses[$i] = $optional;
            }
        }

        if (count($dsl->clauses) && $dsl->clauses[count($dsl->clauses) - 1] instanceof ReturnClause) {
            unset($dsl->clauses[count($dsl->clauses) - 1]);
        }

        $return = new ReturnClause();
        $return->addColumn(new RawExpression('count(*) > 0'), 'exists');
        $dsl->addClause($return);

        return $dsl;
    }

    public function compileInsert(Builder $query, array $values): Query
    {
        $node = $this->initialiseNode($query);

        $keys = Collection::make($values)
            ->map(static fn (array $value) => array_keys($value))
            ->flatten()
            ->filter(static fn ($x) => is_string($x))
            ->unique()
            ->toArray();

        $tbr = Query::new()
            ->raw('UNWIND', '$valueSets as values')
            ->create($node);

        $sets = [];
        foreach ($keys as $key) {
            $sets[] = $node->property($key)->assign(new RawExpression('values.' . Node::escape($key)));
        }

        if (count($sets) > 0) {
            $tbr->set($sets);
        }

        return $tbr;
    }

    /**
     * Compile an insert ignore statement into SQL.
     *
     * @param Builder $query
     * @param array $values
     * @return string
     *
     * @throws RuntimeException
     */
    public function compileInsertOrIgnore(Builder $query, array $values): Query
    {
        throw new BadMethodCallException('This database engine does not support inserting while ignoring errors.');
    }

    /**
     * @param array $values
     * @param string $sequence
     */
    public function compileInsertGetId(Builder $query, $values, $sequence): Query
    {
        $node = $this->initialiseNode($query, $query->from);

        $tbr = Query::new()->create($node);

        $this->decorateUpdateAndRemoveExpressions($values, $tbr);

        return $tbr->returning(['id' => $node->property('id')]);
    }

    public function compileInsertUsing(Builder $query, array $columns, string $sql): Query
    {
        throw new BadMethodCallException('CompileInsertUsing not implemented yet');
    }

    private function getMatchedNode(): Node
    {
        return $this->node;
    }

    public function compileUpdate(Builder $query, array $values): Query
    {
        $dsl = Query::new();

        $this->translateMatch($query, $dsl);

        $this->decorateUpdateAndRemoveExpressions($values, $dsl);

        return $dsl;
    }

    public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update): Query
    {
        $node = $this->initialiseNode($query);
        $createKeys = array_values(array_diff($this->valuesToKeys($values), $uniqueBy, $update));

        $mergeExpression = $this->buildMergeExpression($uniqueBy, $node, $query);
        $onMatch = $this->buildSetClause($update, $node);
        $onCreate = $this->buildSetClause($createKeys, $node);

        $merge = new MergeClause();
        $merge->setPattern($mergeExpression);

        if (count($onMatch->getExpressions()) > 0) {
            $merge->setOnMatch($onMatch);
        }

        if (count($onCreate->getExpressions()) > 0) {
            $merge->setOnCreate($onCreate);
        }

        return Query::new()
            ->raw('UNWIND', '$valueSets as values')
            ->addClause($merge);
    }

    /**
     * Prepare the bindings for an update statement.
     *
     * @param array $bindings
     * @param array $values
     * @return array
     */
    public function prepareBindingsForUpdate(array $bindings, array $values): array
    {
        return array_merge($this->valuesToKeys($bindings), $this->valuesToKeys($values));
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
     * @param  array  $bindings
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
    private function wrapJsonSelector($value): string
    {
        throw new RuntimeException('This database engine does not support JSON operations.');
    }

    /**
     * Determine if the given value is a raw expression.
     *
     * @param mixed $value
     * @return bool
     */
    public function isExpression($value): bool
    {
        return parent::isExpression($value) || $value instanceof QueryConvertable;
    }

    /**
     * Get the value of a raw expression.
     *
     * @param Expression|QueryConvertable $expression
     * @return mixed
     */
    public function getValue($expression)
    {
        if ($expression instanceof QueryConvertable) {
            return $expression->toQuery();
        }

        return $expression->getValue();
    }

    private function translateMatch(Builder $builder, Query $query): Query
    {
        if (($builder->unions || $builder->havings) && $builder->aggregate) {
            $this->translateUnionAggregate($builder, $query);
        }

        if ($builder->unions) {
            $this->translateUnions($builder, $builder->unions, $query);
        }

        $this->translateFrom($builder, $builder->from, $query);
        $this->translateJoins($builder, $builder->joins ?? [], $query);

        $query->addClause($this->compileWheres($builder, $builder->wheres ?? []));
        $this->translateHavings($builder, $builder->havings ?? [], $query);

        $this->translateGroups($builder, $builder->groups ?? [], $query);
        $this->translateAggregate($builder, $builder->aggregate ?? [], $query);

        return $query;
    }

    private function decorateUpdateAndRemoveExpressions(array $values, Query $dsl): void
    {
        $node = $this->getMatchedNode();
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
                $expressions[] = $node->property($key)->assign(Query::parameter($key));
            }
        }

        if (count($expressions) > 0) {
            $dsl->set($expressions);
        }

        if (count($removeExpressions) > 0) {
            $dsl->remove($removeExpressions);
        }
    }

    private function initialiseNode(Builder $query, ?string $table = null): Node
    {
        $this->node = Query::node();
        if (($query->from ?? $table) !== null) {
            $this->node->labeled($query->from ?? $table);
        }

        return $this->node;
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

    /**
     * @return Property[]
     */
    private function column(string $column): array
    {
        return [$this->getMatchedNode()->property($column)];
    }

    public function getBitwiseOperators(): array
    {
        return OperatorRepository::bitwiseOperations();
    }

    public function getOperators(): array
    {
        return [];
    }
}