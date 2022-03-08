<?php /** @noinspection SenselessMethodDuplicationInspection */

/** @noinspection DuplicatedCode */

namespace Vinelab\NeoEloquent\Query;

use BadMethodCallException;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;
use Vinelab\NeoEloquent\LabelAction;
use Vinelab\NeoEloquent\OperatorRepository;
use WikibaseSolutions\CypherDSL\Clauses\MatchClause;
use WikibaseSolutions\CypherDSL\Clauses\MergeClause;
use WikibaseSolutions\CypherDSL\Clauses\OptionalMatchClause;
use WikibaseSolutions\CypherDSL\Clauses\ReturnClause;
use WikibaseSolutions\CypherDSL\Clauses\SetClause;
use WikibaseSolutions\CypherDSL\Clauses\WhereClause;
use WikibaseSolutions\CypherDSL\ExpressionList;
use WikibaseSolutions\CypherDSL\Functions\RawFunction;
use WikibaseSolutions\CypherDSL\In;
use WikibaseSolutions\CypherDSL\Label;
use WikibaseSolutions\CypherDSL\Not;
use WikibaseSolutions\CypherDSL\Parameter;
use WikibaseSolutions\CypherDSL\Patterns\Node;
use WikibaseSolutions\CypherDSL\Property;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\QueryConvertable;
use WikibaseSolutions\CypherDSL\RawExpression;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\BooleanType;
use function array_diff;
use function array_keys;
use function array_map;
use function array_merge;
use function array_values;
use function collect;
use function count;
use function end;
use function head;
use function is_string;
use function last;
use function reset;
use function str_contains;
use function str_replace;
use function strtolower;

class CypherGrammar extends Grammar
{
    /**
     * The components that make up a select clause.
     *
     * @var string[]
     */
    protected $selectComponents = [
        'from', // MATCH for single node
        'joins', // MATCH with relationship and another node

        'wheres', // WHERE
        'havings', // WHERE

        'groups', // WITH and aggregating function
        'aggregate', // WITH and aggregating function

        'columns', // RETURN
        'orders', // ORDER BY
        'limit', // LIMIT
        'offset', // SKIP
    ];

    private ?Node $node = null;
    private bool $usesLegacyIds = false;

    public function translateSelect(Builder $query, Query $dsl): void
    {
        $this->translateMatch($query, $dsl);

        if ($query->aggregate === []) {
            $this->translateColumns($query, $query->columns ?? ['*'], $dsl);
            $this->translateOrders($query, $query->orders ?? [], $dsl);
            $this->translateLimit($query, $query->limit, $dsl);
            $this->translateOffset($query, $query->offset, $dsl);
        }
    }

    /**
     * Compile a select query into SQL.
     */
    public function compileSelect(Builder $query): string
    {
        $dsl = Query::new();

        $this->translateSelect($query, $dsl);

        return $dsl->toQuery();
    }

    protected function translateAggregate(Builder $query, array $aggregate, Query $dsl = null): void
    {
        if ($query->distinct) {
            $columns = [];
            foreach ($aggregate['columns'] as $column) {
                $columns[$column] = $column;
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

    protected function translateColumns(Builder $query, array $columns, Query $dsl): void
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

    protected function translateFrom(Builder $query, ?string $table, Query $dsl): void
    {
        $this->initialiseNode($query, $table);

        $dsl->match($this->node);
    }

    protected function translateJoins(Builder $query, array $joins, Query $dsl = null): void
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

    public function translateWheres(Builder $query, array $wheres): WhereClause
    {
        /** @var BooleanType $expression */
        $expression = null;
        foreach ($wheres as $where) {
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
    protected function whereRaw(Builder $query, $where): RawExpression
    {
        return new RawExpression($where['sql']);
    }

    /**
     * @param array $where
     */
    protected function whereBasic(Builder $query, $where): BooleanType
    {
        $column = $this->column($where['column']);
        $parameter = $this->whereParameter($query, $where['value']);

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter);
    }

    /**
     * @param array $where
     */
    protected function whereBitwise(Builder $query, $where): RawFunction
    {
        return new RawFunction('apoc.bitwise.op', [
            $this->column($where['column']),
            Query::literal($where['operator']),
            $this->whereParameter($query, $where['value'])
        ]);
    }

    /**
     * @param array $where
     */
    protected function whereIn(Builder $query, $where): In
    {
        return new In(
            $this->column($where['column']),
            $this->whereParameter($query, $where['values'])
        );
    }

    /**
     * @param array $where
     */
    protected function whereNotIn(Builder $query, $where): Not
    {
        return new Not($this->whereIn($query, $where));
    }

    /**
     * @param array $where
     */
    protected function whereNotInRaw(Builder $query, $where): Not
    {
        return new Not($this->whereInRaw($query, $where));
    }

    /**
     * @param array $where
     */
    protected function whereInRaw(Builder $query, $where): In
    {
        return new In(
            $this->column($where['column']),
            new ExpressionList(array_map(static fn ($x) => Query::literal($x), $where['values']))
        );
    }

    /**
     * @param array $where
     */
    protected function whereNull(Builder $query, $where): RawExpression
    {
        return new RawExpression($this->column($where['column'])->toQuery() . ' IS NULL');
    }

    /**
     * @param array $where
     */
    protected function whereNotNull(Builder $query, $where): RawExpression
    {
        return new RawExpression($this->column($where['column'])->toQuery() . ' IS NOT NULL');
    }

    /**
     * @param array $where
     */
    protected function whereBetween(Builder $query, $where): BooleanType
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
    protected function whereBetweenColumns(Builder $query, $where): BooleanType
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
    protected function whereDate(Builder $query, $where): BooleanType
    {
        return $this->whereBasic($query, $where);
    }

    /**
     * @param array $where
     */
    protected function whereTime(Builder $query, $where): BooleanType
    {
        return $this->dateBasedWhere('epochMillis', $query, $where);
    }

    /**
     * @param array $where
     */
    protected function whereDay(Builder $query, $where): BooleanType
    {
        return $this->dateBasedWhere('day', $query, $where);
    }

    /**
     * @param array $where
     */
    protected function whereMonth(Builder $query, $where): BooleanType
    {
        return $this->dateBasedWhere('month', $query, $where);
    }

    /**
     * @param array $where
     */
    protected function whereYear(Builder $query, $where): BooleanType
    {
        return $this->dateBasedWhere('year', $query, $where);
    }

    /**
     * @param array $where
     */
    protected function dateBasedWhere($type, Builder $query, $where): BooleanType
    {
        $column = new RawExpression($this->column($where['column'])->toQuery() . '.' . Query::escape($type));
        $parameter = $this->whereParameter($query, $where['value']);

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter);
    }

    /**
     * @param array $where
     */
    protected function whereColumn(Builder $query, $where): BooleanType
    {
        $x = $this->column($where['first']);
        $y = $this->column($where['second']);

        return OperatorRepository::fromSymbol($where['operator'], $x, $y);
    }

    /**
     * @param array $where
     */
    protected function whereNested(Builder $query, $where): BooleanType
    {
        $where['query']->wheres[count($where['query']->wheres) - 1]['boolean'] = 'and';

        return $this->translateWheres($where['query'], $where['query']->wheres)->getExpression();
    }

    /**
     * @param array $where
     */
    protected function whereSub(Builder $query, $where): BooleanType
    {
        throw new BadMethodCallException('Sub selects are not supported at the moment');
    }

    /**
     * @param array $where
     */
    protected function whereExists(Builder $query, $where): BooleanType
    {
        throw new BadMethodCallException('Exists on queries are not supported at the moment');
    }

    /**
     * @param array $where
     */
    protected function whereNotExists(Builder $query, $where): BooleanType
    {
        return new Not($this->whereExists($query, $where));
    }

    /**
     * @param array $where
     */
    protected function whereRowValues(Builder $query, $where): BooleanType
    {
        $expressions = [];
        foreach ($where['columns'] as $column) {
            $expressions[] = $this->column($column);
        }
        $lhs = new ExpressionList($expressions);

        $expressions = [];
        foreach ($where['values'] as $value) {
            $expressions[] = $this->whereParameter($query, $value);
        }
        $rhs = new ExpressionList($expressions);

        return OperatorRepository::fromSymbol($where['operator'], $lhs, $rhs);
    }

    /**
     * @param array $where
     */
    protected function whereJsonBoolean(Builder $query, $where): string
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
    protected function whereJsonContains(Builder $query, $where): string
    {
        throw new BadMethodCallException('Where JSON contains are not supported at the moment');
    }

    /**
     * @param string $column
     * @param string $value
     */
    protected function compileJsonContains($column, $value): string
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
    protected function whereJsonLength(Builder $query, $where): string
    {
        throw new BadMethodCallException('JSON operations are not supported at the moment');
    }

    /**
     * @param string $column
     * @param string $operator
     * @param string $value
     */
    protected function compileJsonLength($column, $operator, $value): string
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

    protected function translateGroups(Builder $query, array $groups, Query $dsl): void
    {
//        return 'group by '.$this->columnize($groups);
    }

    /**
     * Compile the "having" portions of the query.
     */
    protected function translateHavings(Builder $query, array $havings, Query $dsl): void
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
    protected function compileHaving(array $having): string
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
    protected function compileBasicHaving($having): string
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
    protected function compileHavingBetween($having): string
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
    protected function translateOrders(Builder $query, array $orders, Query $dsl): void
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
    protected function compileOrdersToArray(Builder $query, $orders): array
    {
        return array_map(function ($order) {
            return $order['sql'] ?? ($this->wrap($order['column']) . ' ' . $order['direction']);
        }, $orders);
    }

    /**
     * Compile the random statement into SQL.
     *
     * @param string $seed
     * @return string
     */
    public function compileRandom($seed): string
    {
        return 'RANDOM()';
    }

    /**
     * Compile the "limit" portions of the query.
     *
     * @param Builder $query
     * @param string|int $limit
     */
    protected function translateLimit(Builder $query, $limit, Query $dsl): void
    {
//        return 'limit '.(int) $limit;
    }

    /**
     * Compile the "offset" portions of the query.
     *
     * @param Builder $query
     * @param string|int $offset
     */
    protected function translateOffset(Builder $query, $offset, Query $dsl): void
    {
//        return 'offset '.(int) $offset;
    }

    /**
     * Compile the "union" queries attached to the main query.
     */
    protected function translateUnions(Builder $query, array $unions, Query $dsl): void
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
    protected function compileUnion(array $union): string
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
    protected function wrapUnion($sql): string
    {
        return '(' . $sql . ')';
    }

    /**
     * Compile a union aggregate query into SQL.
     */
    protected function translateUnionAggregate(Builder $query, Query $dsl): void
    {
//        $sql = $this->compileAggregate($query, $query->aggregate);
//
//        $query->aggregate = null;
//
//        return $sql.' from ('.$this->compileSelect($query).') as '.$this->wrapTable('temp_table');
    }

    /**
     * Compile an exists statement into SQL.
     *
     * @param Builder $query
     * @return string
     */
    public function compileExists(Builder $query): string
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

        return $dsl->toQuery();
    }

    /**
     * Compile an insert statement into SQL.
     *
     * @param Builder $query
     * @param array $values
     * @return string
     */
    public function compileInsert(Builder $query, array $values): string
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

        return $tbr->toQuery();
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
    public function compileInsertOrIgnore(Builder $query, array $values): string
    {
        throw new BadMethodCallException('This database engine does not support inserting while ignoring errors.');
    }

    /**
     * Compile an insert and get ID statement into SQL.
     *
     * @param Builder $query
     * @param array $values
     * @param string $sequence
     * @return string
     */
    public function compileInsertGetId(Builder $query, $values, $sequence): string
    {
        $node = $this->initialiseNode($query, $query->from);

        $tbr = Query::new()
            ->create($node);

        $this->decorateUpdateAndRemoveExpressions($values, $tbr);

        return $tbr->returning(['id' => $node->property('id')])->toQuery();
    }

    /**
     * Compile an insert statement using a subquery into SQL.
     *
     * @param Builder $query
     * @param array $columns
     * @param string $sql
     * @return string
     */
    public function compileInsertUsing(Builder $query, array $columns, string $sql): string
    {
        throw new BadMethodCallException('CompileInsertUsing not implemented yet');
    }

    private function getMatchedNode(): Node
    {
        return $this->node;
    }

    /**
     * Compile an update statement into SQL.
     *
     * @param Builder $query
     * @param array $values
     * @return string
     */
    public function compileUpdate(Builder $query, array $values): string
    {
        $dsl = Query::new();

        $this->translateMatch($query, $dsl);

        $this->decorateUpdateAndRemoveExpressions($values, $dsl);

        return $dsl->toQuery();
    }

    /**
     * Compile the columns for an update statement.
     *
     * @param Builder $query
     * @param array $values
     * @return string
     */
    protected function compileUpdateColumns(Builder $query, array $values): string
    {
        return collect($values)->map(function ($value, $key) {
            return $this->wrap($key) . ' = ' . $this->parameter($value);
        })->implode(', ');
    }

    /**
     * Compile an "upsert" statement into SQL.
     *
     * @param Builder $query
     * @param array $values
     * @param array $uniqueBy
     * @param array $update
     * @return string
     *
     * @throws RuntimeException
     */
    public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update): string
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

        $tbr = Query::new()
            ->raw('UNWIND', '$valueSets as values')
            ->addClause($merge);

        return $tbr->toQuery();
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
        $cleanBindings = Arr::except($bindings, ['select', 'join']);

        return array_values(
            array_merge($bindings['join'], $values, Arr::flatten($cleanBindings))
        );
    }

    /**
     * Compile a delete statement into SQL.
     *
     * @param Builder $query
     * @return string
     */
    public function compileDelete(Builder $query): string
    {
        $original = $query->columns;
        $query->columns = null;
        $dsl = Query::new();

        $this->translateSelect($query, $dsl);

        $query->columns = $original;

        return $dsl->delete($this->getMatchedNode());
    }

    /**
     * Compile a truncate table statement into SQL.
     *
     * @param Builder $query
     * @return array
     */
    public function compileTruncate(Builder $query): array
    {
        $node = Query::node()->labeled($query->from);

        return [Query::new()->match($node)->delete($node)->toQuery()];
    }

    /**
     * Compile the lock into SQL.
     *
     * @param Builder $query
     * @param bool|string $value
     * @return string
     */
    protected function compileLock(Builder $query, $value): string
    {
        return is_string($value) ? $value : '';
    }

    /**
     * Determine if the grammar supports savepoints.
     *
     * @return bool
     */
    public function supportsSavepoints(): bool
    {
        return false;
    }

    /**
     * Compile the SQL statement to define a savepoint.
     *
     * @param string $name
     * @return string
     */
    public function compileSavepoint($name): string
    {
        throw new BadMethodCallException('Savepoints aren\'t supported in Neo4J');
    }

    /**
     * Compile the SQL statement to execute a savepoint rollback.
     *
     * @param string $name
     * @return string
     */
    public function compileSavepointRollBack($name): string
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
    protected function wrapJsonSelector($value): string
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

        return parent::getValue($expression);
    }

    protected function translateMatch(Builder $query, Query $dsl = null): Query
    {
        $dsl ??= Query::new();

        if (($query->unions || $query->havings) && $query->aggregate) {
            $this->translateUnionAggregate($query, $dsl);
        }

        if ($query->unions) {
            $this->translateUnions($query, $query->unions, $dsl);
        }

        $this->translateFrom($query, $query->from, $dsl);
        $this->translateJoins($query, $query->joins ?? [], $dsl);

        $dsl->addClause($this->translateWheres($query, $query->wheres ?? []));
        $this->translateHavings($query, $query->havings ?? [], $dsl);

        $this->translateGroups($query, $query->groups ?? [], $dsl);
        $this->translateAggregate($query, $query->aggregate ?? [], $dsl);

        return $dsl;
    }

    public function isUsingLegacyIds(): bool
    {
        return $this->usesLegacyIds;
    }

    public function useLegacyIds(bool $useLegacyIds = true): void
    {
        $this->usesLegacyIds = $useLegacyIds;
    }

    public function parameter($value): string
    {
        if ($this->isExpression($value)) {
            $value = $this->getValue($value);
        }

        if ($value === 'id' && $this->isUsingLegacyIds()) {
            $value = 'idn';
        }

        return Query::parameter($value)->toQuery();
    }

    /**
     * @param array $values
     * @param Query $dsl
     * @return void
     */
    protected function decorateUpdateAndRemoveExpressions(array $values, Query $dsl): void
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

    protected function initialiseNode(Builder $query, ?string $table = null): Node
    {
        $this->node = Query::node();
        if (($query->from ?? $table) !== null) {
            $this->node->labeled($query->from ?? $table);
        }

        return $this->node;
    }

    protected function valuesToKeys(array $values): array
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
     * @return Property
     */
    protected function column(string $column): Property
    {
        return $this->getMatchedNode()->property($column);
    }

    /**
     * @param mixed $value
     */
    protected function whereParameter(Builder $query, $value): Parameter
    {
        $parameter = new Parameter('param' . str_replace('-', '', Str::uuid()));
        $query->addBinding([$parameter->getParameter() => $value], 'where');

        return $parameter;
    }
}