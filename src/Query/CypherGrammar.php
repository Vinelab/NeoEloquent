<?php /** @noinspection SenselessMethodDuplicationInspection */

/** @noinspection DuplicatedCode */

namespace Vinelab\NeoEloquent\Query;

use BadMethodCallException;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use RuntimeException;
use Vinelab\NeoEloquent\OperatorRepository;
use WikibaseSolutions\CypherDSL\Clauses\MatchClause;
use WikibaseSolutions\CypherDSL\Clauses\OptionalMatchClause;
use WikibaseSolutions\CypherDSL\Clauses\ReturnClause;
use WikibaseSolutions\CypherDSL\Equality;
use WikibaseSolutions\CypherDSL\Label;
use WikibaseSolutions\CypherDSL\Parameter;
use WikibaseSolutions\CypherDSL\Patterns\Node;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\QueryConvertable;
use WikibaseSolutions\CypherDSL\RawExpression;
use WikibaseSolutions\CypherDSL\Types\AnyType;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\BooleanType;
use function array_map;
use function array_merge;
use function array_values;
use function collect;
use function count;
use function end;
use function head;
use function implode;
use function is_string;
use function last;
use function reset;
use function str_contains;
use function str_replace;
use function strtolower;
use function substr;

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

    public function compileLabel(Builder $query, string $label): string
    {
        return $this->translateMatch($query)
            ->set(new Label($this->getMatchedNode()->getName(), [$label]))
            ->toQuery();
    }

    public function translateSelect(Builder $query, Query $dsl): void
    {
        $this->translateMatch($query, $dsl);

        $this->translateColumns($query, $query->columns ?? ['*'], $dsl);
        $this->translateOrders($query, $query->orders ?? [], $dsl);
        $this->translateLimit($query, $query->limit, $dsl);
        $this->translateOffset($query, $query->offset, $dsl);
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

    /**
     * Compile the components necessary for a select clause.
     */
    protected function translateComponents(Builder $query, Query $dsl): void
    {
    }

    protected function translateAggregate(Builder $query, array $aggregate, Query $dsl = null): void
    {
//        $column = $this->columnize($aggregate['columns']);
//
//        // If the query has a "distinct" constraint and we're not asking for all columns
//        // we need to prepend "distinct" onto the column name so that the query takes
//        // it into account when it performs the aggregating operations on the data.
//        if (is_array($query->distinct)) {
//            $column = 'distinct '.$this->columnize($query->distinct);
//        } elseif ($query->distinct && $column !== '*') {
//            $column = 'distinct '.$column;
//        }

        //return 'select '.$aggregate['function'].'('.$column.') as aggregate';
    }

    /**
     * Compile the "select *" portion of the query.
     *
     * @param Builder $query
     * @param array $columns
     */
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
        $this->node = Query::node();
        if (($query->from ?? $table) !== null) {
            $this->node->labeled($query->from ?? $table);
        }

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

    public function translateWheres(Builder $query, array $wheres, Query $dsl): void
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

        if ($expression) {
            $dsl->where($expression);
        }
    }

    /**
     * @param array $where
     */
    protected function whereRaw(Builder $query, $where): RawExpression
    {
        return new RawExpression($where['sql']);
    }

    /**
     * Compile a basic where clause.
     *
     * @param Builder $query
     * @param array $where
     */
    protected function whereBasic(Builder $query, $where): AnyType
    {
        $column = $this->getMatchedNode()->property($where['column']);
        $parameter = new Parameter('param' . str_replace('-', '', Str::uuid()));

        $query->addBinding([$parameter->getParameter() => $where['value']], 'where');

        return OperatorRepository::fromSymbol($where['operator'], $column, $parameter);
    }

    /**
     * Compile a bitwise operator where clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereBitwise(Builder $query, $where): string
    {
        return $this->whereBasic($query, $where);
    }

    /**
     * Compile a "where in" clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereIn(Builder $query, $where): string
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . $this->parameterize($where['values']) . ')';
        }

        return '0 = 1';
    }

    /**
     * Compile a "where not in" clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereNotIn(Builder $query, $where): string
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . $this->parameterize($where['values']) . ')';
        }

        return '1 = 1';
    }

    /**
     * Compile a "where not in raw" clause.
     *
     * For safety, whereIntegerInRaw ensures this method is only used with integer values.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereNotInRaw(Builder $query, $where): string
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' not in (' . implode(', ', $where['values']) . ')';
        }

        return '1 = 1';
    }

    /**
     * Compile a "where in raw" clause.
     *
     * For safety, whereIntegerInRaw ensures this method is only used with integer values.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereInRaw(Builder $query, $where): string
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']) . ' in (' . implode(', ', $where['values']) . ')';
        }

        return '0 = 1';
    }

    /**
     * Compile a "where null" clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereNull(Builder $query, $where): string
    {
        return $this->wrap($where['column']) . ' is null';
    }

    /**
     * Compile a "where not null" clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereNotNull(Builder $query, $where): string
    {
        return $this->wrap($where['column']) . ' is not null';
    }

    /**
     * Compile a "between" where clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereBetween(Builder $query, $where): string
    {
        $between = $where['not'] ? 'not between' : 'between';

        $min = $this->parameter(reset($where['values']));

        $max = $this->parameter(end($where['values']));

        return $this->wrap($where['column']) . ' ' . $between . ' ' . $min . ' and ' . $max;
    }

    /**
     * Compile a "between" where clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereBetweenColumns(Builder $query, $where): string
    {
        $between = $where['not'] ? 'not between' : 'between';

        $min = $this->wrap(reset($where['values']));

        $max = $this->wrap(end($where['values']));

        return $this->wrap($where['column']) . ' ' . $between . ' ' . $min . ' and ' . $max;
    }

    /**
     * Compile a "where date" clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereDate(Builder $query, $where): string
    {
        return $this->dateBasedWhere('date', $query, $where);
    }

    /**
     * Compile a "where time" clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereTime(Builder $query, $where): string
    {
        return $this->dateBasedWhere('time', $query, $where);
    }

    /**
     * Compile a "where day" clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereDay(Builder $query, $where): string
    {
        return $this->dateBasedWhere('day', $query, $where);
    }

    /**
     * Compile a "where month" clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereMonth(Builder $query, $where): string
    {
        return $this->dateBasedWhere('month', $query, $where);
    }

    /**
     * Compile a "where year" clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereYear(Builder $query, $where): string
    {
        return $this->dateBasedWhere('year', $query, $where);
    }

    /**
     * Compile a date based where clause.
     *
     * @param string $type
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function dateBasedWhere($type, Builder $query, $where): string
    {
        $value = $this->parameter($where['value']);

        return $type . '(' . $this->wrap($where['column']) . ') ' . $where['operator'] . ' ' . $value;
    }

    /**
     * Compile a where clause comparing two columns.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereColumn(Builder $query, $where): string
    {
        return $this->wrap($where['first']) . ' ' . $where['operator'] . ' ' . $this->wrap($where['second']);
    }

    /**
     * Compile a nested where clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereNested(Builder $query, $where): string
    {
        // Here we will calculate what portion of the string we need to remove. If this
        // is a join clause query, we need to remove the "on" portion of the SQL and
        // if it is a normal query we need to take the leading "where" of queries.
        $offset = $query instanceof JoinClause ? 3 : 6;

        return '(' . substr($this->compileWheres($where['query']), $offset) . ')';
    }

    /**
     * Compile a where condition with a sub-select.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereSub(Builder $query, $where): string
    {
        $select = $this->compileSelect($where['query']);

        return $this->wrap($where['column']) . ' ' . $where['operator'] . " ($select)";
    }

    /**
     * Compile a where exists clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereExists(Builder $query, $where): string
    {
        return 'exists (' . $this->compileSelect($where['query']) . ')';
    }

    /**
     * Compile a where exists clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereNotExists(Builder $query, $where): string
    {
        return 'not exists (' . $this->compileSelect($where['query']) . ')';
    }

    /**
     * Compile a where row values condition.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereRowValues(Builder $query, $where): string
    {
        $columns = $this->columnize($where['columns']);

        $values = $this->parameterize($where['values']);

        return '(' . $columns . ') ' . $where['operator'] . ' (' . $values . ')';
    }

    /**
     * Compile a "where JSON boolean" clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereJsonBoolean(Builder $query, $where): string
    {
        $column = $this->wrapJsonBooleanSelector($where['column']);

        $value = $this->wrapJsonBooleanValue(
            $this->parameter($where['value'])
        );

        return $column . ' ' . $where['operator'] . ' ' . $value;
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
        $not = $where['not'] ? 'not ' : '';

        return $not . $this->compileJsonContains(
                $where['column'],
                $this->parameter($where['value'])
            );
    }

    /**
     * Compile a "JSON contains" statement into SQL.
     *
     * @param string $column
     * @param string $value
     * @return string
     *
     * @throws RuntimeException
     */
    protected function compileJsonContains($column, $value): string
    {
        throw new RuntimeException('This database engine does not support JSON contains operations.');
    }

    /**
     * Prepare the binding for a "JSON contains" statement.
     *
     * @param mixed $binding
     * @return string
     */
    public function prepareBindingForJsonContains($binding): string
    {
        /** @noinspection PhpComposerExtensionStubsInspection */
        return json_encode($binding, JSON_THROW_ON_ERROR);
    }

    /**
     * Compile a "where JSON length" clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    protected function whereJsonLength(Builder $query, $where): string
    {
        return $this->compileJsonLength(
            $where['column'],
            $where['operator'],
            $this->parameter($where['value'])
        );
    }

    /**
     * Compile a "JSON length" statement into SQL.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return string
     *
     * @throws RuntimeException
     */
    protected function compileJsonLength($column, $operator, $value): string
    {
        throw new RuntimeException('This database engine does not support JSON length operations.');
    }

    /**
     * Compile a "where fulltext" clause.
     *
     * @param Builder $query
     * @param array $where
     * @return string
     */
    public function whereFullText(Builder $query, $where): string
    {
        throw new RuntimeException('This database engine does not support fulltext search operations.');
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
        $node = Query::node()->labeled($query->from);
        $assignments = [];
        foreach ($values as $key => $value) {
            $assignments[] = $node->property($key)->assign(Query::parameter($key));
        }

        return Query::new()
            ->create($node)
            ->set($assignments)
            ->returning(['id' => $node->property('id')])
            ->toQuery();
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
        return $this->compileInsert($query, $values);
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

        $expressions = [];
        $node = $this->getMatchedNode();
        foreach ($values as $key => $value) {
            $expressions[] = $node->property($key)->assign(Query::parameter($key));
        }
        $dsl->set($expressions);

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
        throw new RuntimeException('This database engine does not support upserts.');
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

        $this->translateWheres($query, $query->wheres ?? [], $dsl);
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
}