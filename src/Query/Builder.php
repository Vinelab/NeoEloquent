<?php

namespace Vinelab\NeoEloquent\Query;

use Closure;
use DateTime;
use Carbon\Carbon;
use BadMethodCallException;
use Illuminate\Database\Concerns\BuildsQueries;
use Illuminate\Support\Traits\ForwardsCalls;
use Illuminate\Support\Traits\Macroable;
use InvalidArgumentException;
use Laudis\Neo4j\Databags\SummarizedResult;
use Vinelab\NeoEloquent\Connection;
use Vinelab\NeoEloquent\Eloquent\Collection;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\OperatorRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Vinelab\NeoEloquent\Traits\ResultTrait;
use WikibaseSolutions\CypherDSL\Clauses\ReturnClause;
use WikibaseSolutions\CypherDSL\Clauses\WhereClause;
use WikibaseSolutions\CypherDSL\Exists;
use WikibaseSolutions\CypherDSL\Literals\Literal;
use WikibaseSolutions\CypherDSL\Not;
use WikibaseSolutions\CypherDSL\Patterns\Node;
use WikibaseSolutions\CypherDSL\Query;
use WikibaseSolutions\CypherDSL\Types\PropertyTypes\BooleanType;
use WikibaseSolutions\CypherDSL\Variable;

use function bin2hex;
use function func_get_args;
use function is_array;
use function is_callable;
use function is_string;
use function random_bytes;

class Builder
{
    use ResultTrait, BuildsQueries, ForwardsCalls, Macroable {
        __call as macroCall;
    }

    protected Connection $connection;

    /**
     * The matches constraints for the query.
     *
     * @var array
     */
    public array $matches = [];

    /**
     * The WITH parts of the query.
     *
     * @var array
     */
    public array $with = [];

    protected array $bindings = [];

    /**
     * An aggregate function and column to be run.
     *
     * @var array
     */
    public array $aggregate = [];

    /**
     * The groupings for the query.
     *
     * @var array
     */
    public array $groups = [];

    /**
     * The having constraints for the query.
     *
     * @var array
     */
    public array $havings = [];

    /**
     * The query union statements.
     */
    public array $unions = [];

    /**
     * The maximum number of union records to return.
     */
    public int $unionLimit = 0;

    /**
     * The number of union records to skip.
     */
    public int $unionOffset = 0;

    /**
     * The orderings for the union query.
     */
    public array $unionOrders = [];

    public ?BooleanType $wheres = null;

    /**
     * Indicates whether row locking is being used.
     */
    public bool $lock = false;

    /**
     * The binding backups currently in use.
     */
    protected array $bindingBackups = [];

    /**
     * The callbacks that should be invoked before the query is executed.
     */
    protected array $beforeQueryCallbacks = [];

    protected Query $dsl;
    protected Variable $current;
    private Node $currentNode;
    private ?ReturnClause $return = null;

    /**
     * Create a new query builder instance.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->current = Query::variable(bin2hex(random_bytes(64)));
        $this->currentNode = Query::node();
        $this->dsl = Query::new()->match($this->currentNode);
    }

    /**
     * Set the columns to be selected.
     *
     * @param array|mixed $columns
     *
     * @return static
     */
    public function select(iterable $columns = ['*']): self
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        $return = $this->returning();
        foreach ($columns as $as => $column) {
            if (is_string($as) && $this->isQueryable($column)) {
                $this->selectSub($column, $as);
            } else {
                $return->addColumn($this->current->property($column));
            }
        }

        return $this;
    }

    /**
     * Determine if the value is a query builder instance or a Closure.
     *
     * @param  mixed  $value
     */
    protected function isQueryable($value): bool
    {
        return $value instanceof self ||
            $value instanceof \Vinelab\NeoEloquent\Eloquent\Builder ||
            $value instanceof \Vinelab\NeoEloquent\Eloquent\Relations\Relation ||
            is_callable($value);
    }

    /**
     * Add a new "raw" select expression to the query.
     *
     * @param string $expression
     * @param array  $bindings
     */
    public function selectRaw(string $expression, array $bindings = []): self
    {
        // - TODO
//        $this->addSelect(new Expression($expression));
//
//        if ($bindings) {
//            $this->addBinding($bindings, 'select');
//        }
//
//        return $this;

        return $this;
    }

    /**
     * Add a subselect expression to the query.
     *
     * @param callable|Builder|string $query
     */
    public function selectSub($query, string $as): self
    {
        // - TODO
//        if (is_callable($query)) {
//            $callback = $query;
//
//            $callback($query = $this->newQuery());
//        }
//
//        if ($query instanceof self) {
//            $bindings = $query->getBindings();
//
//            $query = $query->toCypher();
//        } elseif (is_string($query)) {
//            $bindings = [];
//        } else {
//            throw new InvalidArgumentException();
//        }
//
//        return $this->selectRaw('('.$query.') as '.$this->grammar->wrap($as), $bindings);

        return $this;
    }

    /**
     * Add a new select column to the query.
     *
     * @param mixed $column
     *
     * @return static
     */
    public function addSelect($column): self
    {
        $columns = is_array($column) ? $column : func_get_args();

        return $this->select($columns);
    }

    /**
     * Force the query to only return distinct results.
     *
     * @return static
     */
    public function distinct(): self
    {
        $this->returning()->setDistinct(true);

        return $this;
    }

    /**
     * Set the node's label which the query is targeting.
     *
     * @param string $label
     *
     * @return Builder|static
     */
    public function from(string $label): self
    {
        $this->currentNode->labeled($label);

        return $this;
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param array  $values
     */
    public function insertGetId(array $values): int
    {
        $variable = Query::variable('x');

        $properties = $this->prepareProperties($values);

        $query = $this->dsl->create(
                Query::node()->labeled($this->getLabel())
                    ->named($variable)
                    ->withProperties($properties)
            )
            ->returning($variable)
            ->build();

        $results = $this->connection->insert($query, $this->bindings);

        return $results->getAsCypherMap(0)->getAsNode('x')->getId();
    }

    /**
     * Update a record in the database.
     *
     * @return int
     */
    public function update(array $values): int
    {
        $assignments = $this->prepareAssignments($values);
        $cypher = $this->dsl->set($assignments)->build();

        $updated = $this->connection->update($cypher, $this->getBindings());

        return (int) $updated->getSummary()->getCounters()->containsUpdates();
    }

    /**
     * Get the current query value bindings in a flattened array
     * of $key => $value.
     *
     * @return array
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Add a basic where clause to the query.
     *
     * @param string|array|callable $column
     * @param mixed  $value
     * @param mixed  $operator
     *
     * @return static
     */
    public function where($column, $operator = null, $value = null, string $boolean = 'and'): self
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($column)) {
            return $this->whereNested(function (self $query) use ($column) {
                foreach ($column as $key => $value) {
                    $query->where($key, '=', $value);
                }
            }, $boolean);
        }

        if (func_num_args() === 2) {
            [$value, $operator] = [$operator, '='];
        }

        // If the columns is actually a Closure instance, we will assume the developer
        // wants to begin a nested where statement which is wrapped in parenthesis.
        // We'll add that Closure to the query then return back out immediately.
        if (is_callable($column)) {
            return $this->whereNested($column, $boolean);
        }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if (!OperatorRepository::symbolExists($operator)) {
            [$value, $operator] = [$operator, '='];
        }

        // If the value is a Closure, it means the developer is performing an entire
        // sub-select within the query and we will need to compile the sub-select
        // within the where clause to get the appropriate query record results.
        if ($value instanceof Closure) {
            return $this->whereSub($column, $operator, $value, $boolean);
        }

        // If the value is "null", we will just assume the developer wants to add a
        // where null clause to the query. So, we will allow a short-cut here to
        // that method for convenience so the developer doesn't have to check.
        if (is_null($value)) {
            return $this->whereNull($column, $boolean, $operator !== '=');
        }

        // Also if the $column is already a form of id(n) we'd have to type-cast the value into int.
        if (preg_match('/^id\(.*\)$/', $column)) {
            $value = (int) $value;
        }

        if ($this->wheres === null) {
            $this->wheres = OperatorRepository::fromSymbol($operator);
            $clause = new WhereClause();
            $clause->setExpression($this->wheres);
            $this->dsl->addClause($clause);
        } elseif ($boolean === 'and') {
            $this->wheres->and(OperatorRepository::fromSymbol($operator));
        } else {
            $this->wheres->or(OperatorRepository::fromSymbol($operator));
        }

        return $this;
    }

    /**
     * Add an "or where" clause to the query.
     *
     * @param string|array|callable $column
     * @param mixed  $value
     * @param mixed  $operator
     *
     * @return static
     */
    public function orWhere($column, $operator = null, $value = null): self
    {
        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add a raw where clause to the query.
     *
     * @param string $cypher
     * @param array  $bindings
     * @param string $boolean
     *
     * @return static
     */
    public function whereRaw(string $cypher, array $bindings = [], string $boolean = 'and'): self
    {
        $this->addBindings($bindings);
        return $this->where('', 'RAW', $cypher, $boolean);
    }

    /**
     * Add a raw or where clause to the query.
     *
     * @return static
     */
    public function orWhereRaw(string $sql, array $bindings = []): self
    {
        return $this->whereRaw($sql, $bindings, 'or');
    }

    /**
     * Add a where not between statement to the query.
     *
     * @return static
     */
    public function whereNotBetween(string $column, array $values, string $boolean = 'and'): self
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement to the query.
     *
     * @param string $column
     * @param array  $values
     *
     * @return static
     */
    public function orWhereNotBetween(string $column, array $values): self
    {
        return $this->whereNotBetween($column, $values, 'or');
    }

    /**
     * Add a nested where statement to the query.
     *
     * @param callable $callback
     * @param string   $boolean
     *
     * @return static
     */
    public function whereNested($callback, string $boolean = 'and'): self
    {
        // To handle nested queries we'll actually create a brand new query instance
        // and pass it off to the Closure that we have. The Closure can simply do
        // do whatever it wants to a query then we will store it for compiling.
        $query = $this->newQuery();

        $callback($query);

        return $this->addNestedWhereQuery($query, $boolean);
    }

    /**
     * Add another query builder as a nested where to the query builder.
     *
     * @param static $query
     *
     * @return static
     */
    public function addNestedWhereQuery(Builder $query, string $boolean = 'and'): self
    {
        if ($query->wheres) {
            if ($boolean === 'and') {
                $this->wheres->and($query->wheres);
            } else {
                $this->wheres->or($query->wheres);
            }
        }

        return $this;
    }

    /**
     * Add an or where between statement to the query.
     *
     * @param string $column
     * @param array  $values
     *
     * @return static
     */
    public function orWhereBetween(string $column, array $values): self
    {
        return $this->whereBetween($column, $values, 'or');
    }

    /**
     * Add a full sub-select to the query.
     *
     * @return static
     */
    protected function whereSub(string $column, string $operator, callable $callback,string $boolean): self
    {
        // TODO - might be impossible

        return $this;
    }

    /**
     * Add an exists clause to the query.
     *
     * @param callable $callback
     *
     * @return static
     */
    public function whereExists($callback, string $boolean = 'and', bool $not = false): self
    {
        $query = $this->forSubQuery();
        $callback($query);

        $exists = new Exists($query->match, $query->wheres);
        if ($not) {
            $exists = new Not($exists);
        }

        if (strtolower($boolean) === 'and') {
            $this->wheres->and($exists);
        } else {
            $this->wheres->or($exists);
        }

        return $this;
    }

    /**
     * Add an or exists clause to the query.
     *
     * @param callable $callback
     * @param bool     $not
     *
     * @return static
     */
    public function orWhereExists($callback, bool $not = false): self
    {
        return $this->whereExists($callback, 'or', $not);
    }

    /**
     * Add a where not exists clause to the query.
     *
     * @param callable $callback
     *
     * @return static
     */
    public function whereNotExists(callable $callback, string $boolean = 'and'): self
    {
        return $this->whereExists($callback, $boolean, true);
    }

    /**
     * Add a where not exists clause to the query.
     *
     * @param callable $callback
     *
     * @return static
     */
    public function orWhereNotExists($callback): self
    {
        return $this->orWhereExists($callback, true);
    }

    /**
     * Add an "or where in" clause to the query.
     *
     * @param string $column
     * @param mixed  $values
     *
     * @return static
     */
    public function orWhereIn($column, $values): self
    {
        return $this->whereIn($column, $values, 'or');
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param mixed  $values
     *
     * @return static
     */
    public function whereNotIn(string $column, $values, string $boolean = 'and'): self
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param string $column
     * @param mixed  $values
     *
     * @return static
     */
    public function orWhereNotIn(string $column, $values): self
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    /**
     * Add a where in with a sub-select to the query.
     *
     * @param callable $callback
     *
     * @return static
     */
    protected function whereInSub(string $column, $callback, string $boolean, bool $not): self
    {
        // TODO

        return $this;
    }

    /**
     * Add an "or where null" clause to the query.
     *
     * @return static
     */
    public function orWhereNull(string $column): self
    {
        return $this->whereNull($column, 'or');
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @return static
     */
    public function whereNotNull(string $column, string $boolean = 'and'): self
    {
        return $this->whereNull($column, $boolean, true);
    }

    /**
     * Add an "or where not null" clause to the query.
     *
     * @param string $column
     *
     * @return Builder|static
     */
    public function orWhereNotNull(string $column)
    {
        return $this->whereNotNull($column, 'or');
    }

    /**
     * Add a "where date" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param int    $value
     * @param string $boolean
     *
     * @return Builder|static
     */
    public function whereDate($column, $operator, $value, $boolean = 'and')
    {
        return $this->addDateBasedWhere('Date', $column, $operator, $value, $boolean);
    }

    /**
     * Add a "where day" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param int    $value
     * @param string $boolean
     *
     * @return Builder|static
     */
    public function whereDay($column, $operator, $value, $boolean = 'and')
    {
        return $this->addDateBasedWhere('Day', $column, $operator, $value, $boolean);
    }

    /**
     * Add a "where month" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param int    $value
     * @param string $boolean
     *
     * @return Builder|static
     */
    public function whereMonth($column, $operator, $value, $boolean = 'and')
    {
        return $this->addDateBasedWhere('Month', $column, $operator, $value, $boolean);
    }

    /**
     * Add a "where year" statement to the query.
     *
     * @param string $column
     * @param string $operator
     * @param int    $value
     * @param string $boolean
     *
     * @return Builder|static
     */
    public function whereYear($column, $operator, $value, $boolean = 'and')
    {
        return $this->addDateBasedWhere('Year', $column, $operator, $value, $boolean);
    }

    /**
     * Add a date based (year, month, day) statement to the query.
     *
     * @param string $type
     * @param string $column
     * @param string $operator
     * @param int    $value
     * @param string $boolean
     *
     * @return $this
     */
    protected function addDateBasedWhere($type, $column, $operator, $value, $boolean = 'and')
    {
        $this->wheres[] = compact('column', 'type', 'boolean', 'operator', 'value');

        $this->addBinding($value, 'where');

        return $this;
    }

    /**
     * Handles dynamic "where" clauses to the query.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return $this
     */
    public function dynamicWhere($method, $parameters)
    {
        $finder = substr($method, 5);

        $segments = preg_split('/(And|Or)(?=[A-Z])/', $finder, -1, PREG_SPLIT_DELIM_CAPTURE);

        // The connector variable will determine which connector will be used for the
        // query condition. We will change it as we come across new boolean values
        // in the dynamic method strings, which could contain a number of these.
        $connector = 'and';

        $index = 0;

        foreach ($segments as $segment) {
            // If the segment is not a boolean connector, we can assume it is a column's name
            // and we will add it to the query as a new constraint as a where clause, then
            // we can keep iterating through the dynamic method string's segments again.
            if ($segment != 'And' && $segment != 'Or') {
                $this->addDynamic($segment, $connector, $parameters, $index);

                ++$index;
            }

            // Otherwise, we will store the connector so we know how the next where clause we
            // find in the query should be connected to the previous ones, meaning we will
            // have the proper boolean connector to connect the next where clause found.
            else {
                $connector = $segment;
            }
        }

        return $this;
    }

    /**
     * Add a single dynamic where clause statement to the query.
     *
     * @param string $segment
     * @param string $connector
     * @param array  $parameters
     * @param int    $index
     */
    protected function addDynamic($segment, $connector, $parameters, $index)
    {
        // Once we have parsed out the columns and formatted the boolean operators we
        // are ready to add it to this query as a where clause just like any other
        // clause on the query. Then we'll increment the parameter index values.
        $bool = strtolower($connector);

        $this->where(Str::snake($segment), '=', $parameters[$index], $bool);
    }

    /**
     * Add a "group by" clause to the query.
     *
     * @param array|string $column,...
     *
     * @return $this
     */
    public function groupBy()
    {
        foreach (func_get_args() as $arg) {
            $this->groups = array_merge((array) $this->groups, is_array($arg) ? $arg : [$arg]);
        }

        return $this;
    }

    /**
     * Add a "having" clause to the query.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param string $boolean
     *
     * @return $this
     */
    public function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        $type = 'basic';

        $this->havings[] = compact('type', 'column', 'operator', 'value', 'boolean');

        if (!$value instanceof Expression) {
            $this->addBinding($value, 'having');
        }

        return $this;
    }

    /**
     * Add a "or having" clause to the query.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     *
     * @return Builder|static
     */
    public function orHaving($column, $operator = null, $value = null)
    {
        return $this->having($column, $operator, $value, 'or');
    }

    /**
     * Add a raw having clause to the query.
     *
     * @param string $sql
     * @param array  $bindings
     * @param string $boolean
     *
     * @return $this
     */
    public function havingRaw($sql, array $bindings = [], $boolean = 'and')
    {
        $type = 'raw';

        $this->havings[] = compact('type', 'sql', 'boolean');

        $this->addBinding($bindings, 'having');

        return $this;
    }

    /**
     * Add a raw or having clause to the query.
     *
     * @param string $sql
     * @param array  $bindings
     *
     * @return Builder|static
     */
    public function orHavingRaw($sql, array $bindings = [])
    {
        return $this->havingRaw($sql, $bindings, 'or');
    }

    /**
     * Add an "order by" clause to the query.
     *
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $property = $this->unions ? 'unionOrders' : 'orders';
        $direction = strtolower($direction) == 'asc' ? 'asc' : 'desc';

        $this->{$property}[] = compact('column', 'direction');

        return $this;
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     *
     * @param string $column
     *
     * @return Builder|static
     */
    public function latest($column = 'created_at')
    {
        return $this->orderBy($column, 'desc');
    }

    /**
     * Add an "order by" clause for a timestamp to the query.
     *
     * @param string $column
     *
     * @return static
     */
    public function oldest(string $column = 'created_at'): self
    {
        return $this->orderBy($column, 'asc');
    }

    /**
     * Add a raw "order by" clause to the query.
     *
     * @param string $sql
     * @param array  $bindings
     *
     * @return static
     */
    public function orderByRaw(string $sql, array $bindings = []): self
    {
        $property = $this->unions ? 'unionOrders' : 'orders';

        $type = 'raw';

        $this->{$property}[] = compact('type', 'sql');

        $this->addBinding($bindings, 'order');

        return $this;
    }

    /**
     * Set the "offset" value of the query.
     *
     * @param int $value
     *
     * @return static
     */
    public function offset(int $value): self
    {
        $this->dsl->skip(Literal::decimal($value));

        return $this;
    }

    /**
     * Alias to set the "offset" value of the query.
     *
     * @param int $value
     *
     * @return static
     */
    public function skip(int $value): self
    {
        return $this->offset($value);
    }

    /**
     * Set the "limit" value of the query.
     *
     * @param int $value
     *
     * @return static
     */
    public function limit(int $value): self
    {
        $this->dsl->limit(Literal::decimal($value));

        return $this;
    }

    /**
     * Alias to set the "limit" value of the query.
     *
     * @param int $value
     *
     * @return static
     */
    public function take(int $value): self
    {
        return $this->limit($value);
    }

    /**
     * Set the limit and offset for a given page.
     *
     * @param int $page
     * @param int $perPage
     *
     * @return static
     */
    public function forPage(int $page, int $perPage = 15): self
    {
        return $this->skip(($page - 1) * $perPage)->take($perPage);
    }

    /**
     * Add a union statement to the query.
     *
     * @param self|callable $query
     * @param bool         $all
     *
     * @return static
     */
    public function union($query, bool $all = false): self
    {
        // todo

        return $this;
    }

    /**
     * Add a union all statement to the query.
     *
     * @param Builder|callable $query
     *
     * @return static
     */
    public function unionAll($query): self
    {
        return $this->union($query, true);
    }

    /**
     * Lock the selected rows in the table.
     *
     * @return static
     */
    public function lock(bool $value = true): self
    {
        $this->lock = $value;

        return $this;
    }

    /**
     * Lock the selected rows in the table for updating.
     *
     * @return static
     */
    public function lockForUpdate(): self
    {
        return $this->lock();
    }

    /**
     * Share lock the selected rows in the table.
     *
     * @return static
     */
    public function sharedLock(): self
    {
        return $this->lock(false);
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param mixed $id
     * @param array $columns
     *
     * @return mixed|static
     */
    public function find($id, $columns = ['*'])
    {
        return $this->where('id', '=', $id)->first($columns);
    }

    /**
     * Get a single column's value from the first result of a query.
     *
     * @param string $column
     *
     * @return mixed
     */
    public function value(string $column)
    {
        $result = $this->first([$column]) ?? [];

        return Arr::first($result);
    }

    /**
     * Get a single column's value from the first result of a query.
     *
     * This is an alias for the "value" method.
     *
     * @param string $column
     *
     * @return mixed
     *
     * @deprecated since version 5.1.
     */
    public function pluck(string $column)
    {
        return $this->value($column);
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param array $columns
     */
    public function get(array $columns = ['*']): \Illuminate\Support\Collection
    {
        $this->select($columns);

        return collect($this->runSelect());
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page', ?int $page = null)
    {
        $page = $page ?: Paginator::resolveCurrentPage($pageName);

        $total = $this->getCountForPagination($columns);

        $results = $this->forPage($page, $perPage)->get($columns);

        return new LengthAwarePaginator($results, $total, $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Get a paginator only supporting simple next and previous links.
     *
     * This is more efficient on larger data-sets, etc.
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate(int $perPage = 15, array $columns = ['*'], string $pageName = 'page')
    {
        $page = Paginator::resolveCurrentPage($pageName);

        $this->skip(($page - 1) * $perPage)->take($perPage + 1);

        return new Paginator($this->get($columns), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Get the count of the total records for the paginator.
     *
     * @param array $columns
     *
     * @return int
     */
    public function getCountForPagination($columns = ['*'])
    {
        $this->backupFieldsForCount();

        $this->aggregate = ['function' => 'count', 'columns' => $columns];

        $results = $this->get();

        $this->aggregate = null;

        $this->restoreFieldsForCount();

        if (isset($this->groups)) {
            return count($results);
        }

        return isset($results[0]) ? (int) array_change_key_case((array) $results[0])['aggregate'] : 0;
    }

    /**
     * Chunk the results of the query.
     *
     * @param int      $count
     * @param callable $callback
     */
    public function chunk($count, callable $callback)
    {
        $results = $this->forPage($page = 1, $count)->get();

        while (count($results) > 0) {
            // On each chunk result set, we will pass them to the callback and then let the
            // developer take care of everything within the callback, which allows us to
            // keep the memory low for spinning through large result sets for working.
            if (call_user_func($callback, $results) === false) {
                break;
            }

            ++$page;

            $results = $this->forPage($page, $count)->get();
        }
    }

    /**
     * Get an array with the values of a given column.
     *
     * @param string $column
     * @param string $key
     *
     * @return array
     */
    public function lists($column, $key = null)
    {
        $columns = $this->getListSelect($column, $key);

        $results = new Collection($this->get($columns));

        return $results->pluck($columns[0], Arr::get($columns, 1))->all();
    }

    /**
     * Get the columns that should be used in a list array.
     *
     * @param string $column
     * @param string $key
     *
     * @return array
     */
    protected function getListSelect($column, $key)
    {
        $select = is_null($key) ? [$column] : [$column, $key];

        // If the selected column contains a "dot", we will remove it so that the list
        // operation can run normally. Specifying the table is not needed, since we
        // really want the names of the columns as it is in this resulting array.
        return array_map(function ($column) {
            $dot = strpos($column, '.');

            return $dot === false ? $column : substr($column, $dot + 1);
        }, $select);
    }

    /**
     * Concatenate values of a given column as a string.
     *
     * @param string $column
     * @param string $glue
     *
     * @return string
     */
    public function implode($column, $glue = null)
    {
        if (is_null($glue)) {
            return implode($this->lists($column));
        }

        return implode($glue, $this->lists($column));
    }

    /**
     * Determine if any rows exist for the current query.
     *
     * @return bool
     */
    public function exists()
    {
        $limit = $this->limit;

        $result = $this->limit(1)->count() > 0;

        $this->limit($limit);

        return $result;
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param string $columns
     *
     * @return int
     */
    public function count($columns = '*')
    {
        if (!is_array($columns)) {
            $columns = [$columns];
        }

        return (int) $this->aggregate(__FUNCTION__, $columns);
    }

    /**
     * Retrieve the minimum value of a given column.
     *
     * @param string $column
     *
     * @return float|int
     */
    public function min($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Retrieve the maximum value of a given column.
     *
     * @param string $column
     *
     * @return float|int
     */
    public function max($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param string $column
     *
     * @return float|int
     */
    public function sum($column)
    {
        $result = $this->aggregate(__FUNCTION__, [$column]);

        return $result ?: 0;
    }

    /**
     * Retrieve the average of the values of a given column.
     *
     * @param string $column
     *
     * @return float|int
     */
    public function avg($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Increment a column's value by a given amount.
     *
     * @param string $column
     * @param int    $amount
     * @param array  $extra
     *
     * @return int
     */
    public function increment($column, $amount = 1, array $extra = [])
    {
        $wrapped = $this->grammar->wrap($column);

        $columns = array_merge([$column => $this->raw("$wrapped + $amount")], $extra);

        return $this->update($columns);
    }

    /**
     * Decrement a column's value by a given amount.
     *
     * @param string $column
     * @param int    $amount
     * @param array  $extra
     *
     * @return int
     */
    public function decrement($column, $amount = 1, array $extra = [])
    {
        $wrapped = $this->grammar->wrap($column);

        $columns = array_merge([$column => $this->raw("$wrapped - $amount")], $extra);

        return $this->update($columns);
    }

    /**
     * Delete a record from the database.
     *
     * @param mixed $id
     *
     * @return int
     */
    public function delete($id = null)
    {
        // If an ID is passed to the method, we will set the where clause to check
        // the ID to allow developers to simply and quickly remove a single row
        // from their database without manually specifying the where clauses.
        if (!is_null($id)) {
            $this->where('id', '=', $id);
        }

        $cypher = $this->grammar->compileDelete($this);

        $result = $this->connection->delete($cypher, $this->getBindings());

        if ($result instanceof Result) {
            $result = true;
        }

        return $result;
    }

    /**
     * Run a truncate statement on the table.
     */
    public function truncate(): void
    {
        $label = $this->getLabel();
        $node = Query::node();
        if ($label) {
            $node = $node->labeled($label);
        }
        $cypher = Query::new()->match($node)->detachDelete($node)->toQuery();

        $this->connection->statement($cypher, $this->bindings);
    }

    /**
     * Get the raw array of bindings.
     *
     * @return array
     */
    public function getRawBindings(): array
    {
        return $this->bindings;
    }

    /**
     * Set the bindings on the query builder.
     *
     * @param array  $bindings
     * @param string $type
     *
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function setBindings(array $bindings, $type = 'where')
    {
        if (!array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        $this->bindings[$type] = $bindings;

        return $this;
    }

    /**
     * Merge an array of bindings into our bindings.
     *
     * @param Builder $query
     *
     * @return $this
     */
    public function mergeBindings(Builder $query)
    {
        $this->bindings = array_merge_recursive($this->bindings, $query->bindings);

        return $this;
    }

    /**
     * Get the number of occurrences of a column in where clauses.
     *
     * @param string $column
     *
     * @return int
     */
    protected function columnCountForWhereClause($column)
    {
        if (is_array($this->wheres)) {
            return count(array_filter($this->wheres, function ($where) use ($column) {
                return $where['column'] == $column;
            }));
        }
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param string $column
     * @param mixed  $values
     * @param string $boolean
     * @param bool   $not
     *
     * @return Builder|static
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotIn' : 'In';

        // If the value of the where in clause is actually a Closure, we will assume that
        // the developer is using a full sub-select for this "in" statement, and will
        // execute those Closures, then we can re-construct the entire sub-selects.
        if ($values instanceof Closure) {
            return $this->whereInSub($column, $values, $boolean, $not);
        }

        if ($values instanceof Arrayable) {
            $values = $values->toArray();
        }

        $property = $column;

        if ($column == 'id') {
            $column = 'id('.$this->modelAsNode().')';
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        $property = $this->wrap($property);

        $this->addBinding([$property => $values], 'where');

        return $this;
    }

    /**
     * Add a where between statement to the query.
     *
     * @param string $column
     * @param array  $values
     * @param string $boolean
     * @param bool   $not
     *
     * @return Builder|static
     */
    public function whereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $type = 'between';

        $property = $column;

        if ($column === 'id') {
            $column = 'id('.$this->modelAsNode().')';
        }

        $this->wheres[] = compact('column', 'type', 'boolean', 'not');

        $this->addBinding([$property => $values], 'where');

        return $this;
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param string $column
     * @param string $boolean
     * @param bool   $not
     *
     * @return Builder|static
     */
    public function whereNull($column, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';

        if ($column == 'id') {
            $column = 'id('.$this->modelAsNode().')';
        }

        $binding = $this->prepareBindingColumn($column);

        $this->wheres[] = compact('type', 'column', 'boolean', 'binding');

        return $this;
    }

    /**
     * Add a WHERE statement with carried identifier to the query.
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @param string $boolean
     *
     * @return Builder|static
     */
    public function whereCarried($column, $operator = null, $value = null, $boolean = 'and')
    {
        $type = 'Carried';

        $this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');

        return $this;
    }

    /**
     * Add a WITH clause to the query.
     *
     * @param array $parts
     *
     * @return Builder|static
     */
    public function with(array $parts)
    {
        if(Arr::isAssoc($parts)) {
            foreach ($parts as $key => $part) {
                if (!in_array($part, $this->with)) {
                    $this->with[$key] = $part;
                }
            }
        } else {
            foreach ($parts as $part) {
                if (!in_array($part, $this->with)) {
                    $this->with[] = $part;
                }
            }
        }

        return $this;
    }

    /**
     * Insert a new record into the database.
     *
     * @param array $values
     *
     * @return bool
     */
    public function insert(array $values)
    {
        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient for building these
        // inserts statements by verifying the elements are actually an array.
        if (!is_array(reset($values))) {
            $values = array($values);
        }

        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient for building these
        // inserts statements by verifying the elements are actually an array.
        else {
            foreach ($values as $key => $value) {
                $value = $this->formatValue($value);
                ksort($value);
                $values[$key] = $value;
            }
        }

        // We'll treat every insert like a batch insert so we can easily insert each
        // of the records into the database consistently. This will make it much
        // easier on the grammars to just handle one type of record insertion.
        $bindings = array();

        foreach ($values as $record) {
            $bindings[] = $record;
        }

        $cypher = $this->grammar->compileInsert($this, $values);

        // Once we have compiled the insert statement's Cypher we can execute it on the
        // connection and return a result as a boolean success indicator as that
        // is the same type of result returned by the raw connection instance.
        $bindings = $this->cleanBindings($bindings);

        $results = $this->connection->insert($cypher, $bindings);

        return !!$results;
    }

    /**
     * Create a new node with related nodes with one database hit.
     *
     * @param array $model
     * @param array $related
     *
     * @return Model
     */
    public function createWith(array $model, array $related)
    {
        $cypher = $this->grammar->compileCreateWith($this, compact('model', 'related'));

        // Indicate that we need the result returned as is.
        return $this->connection->statement($cypher, [], true);
    }

    /**
     * Run the query as a "select" statement against the connection.
     *
     * @return array
     */
    protected function runSelect()
    {
        return $this->connection->select($this->toCypher(), $this->getBindings());
    }

    /**
     * Get the Cypher representation of the traversal.
     *
     * @return string
     */
    public function toCypher()
    {
        return $this->grammar->compileSelect($this);
    }

    /**
     * Add a relationship MATCH clause to the query.
     *
     * @param Model $parent       The parent model of the relationship
     * @param Model $related      The related model
     * @param string                              $relatedNode  The related node' placeholder
     * @param string                              $relationship The relationship title
     * @param string                              $property     The parent's property we are matching against
     * @param string                              $value
     * @param string                              $direction    Possible values are in, out and in-out
     * @param string                              $boolean      And, or operators
     *
     * @return Builder|static
     */
    public function matchRelation($parent, $related, $relatedNode, $relationship, $property, $value = null, $direction = 'out', $boolean = 'and')
    {
        $parentLabels = $parent->nodeLabel();
        $relatedLabels = $related->nodeLabel();
        $parentNode = $this->modelAsNode($parentLabels);

        $this->matches[] = array(
            'type' => 'Relation',
            'optional' => $boolean,
            'property' => $property,
            'direction' => $direction,
            'relationship' => $relationship,
            'parent' => array(
                'node' => $parentNode,
                'labels' => $parentLabels,
            ),
            'related' => array(
                'node' => $relatedNode,
                'labels' => $relatedLabels,
            ),
        );

        $this->addBinding(array($this->wrap($property) => $value), 'matches');

        return $this;
    }

    public function matchMorphRelation($parent, $relatedNode, $property, $value = null, $direction = 'out', $boolean = 'and')
    {
        $parentLabels = $parent->nodeLabel();
        $parentNode = $this->modelAsNode($parentLabels);

        $this->matches[] = array(
            'type' => 'MorphTo',
            'optional' => 'and',
            'property' => $property,
            'direction' => $direction,
            'related' => array('node' => $relatedNode),
            'parent' => array(
                'node' => $parentNode,
                'labels' => $parentLabels,
            ),
        );

        $this->addBinding(array($property => $value), 'matches');

        return $this;
    }

    /**
     * the percentile of a given value over a group,
     * with a percentile from 0.0 to 1.0.
     * It uses a rounding method, returning the nearest value to the percentile.
     *
     * @param string $column
     *
     * @return mixed
     */
    public function percentileDisc($column, $percentile = 0.0)
    {
        return $this->aggregate(__FUNCTION__, array($column), $percentile);
    }

    /**
     * Retrieve the percentile of a given value over a group,
     * with a percentile from 0.0 to 1.0. It uses a linear interpolation method,
     * calculating a weighted average between two values,
     * if the desired percentile lies between them.
     *
     * @param string $column
     *
     * @return mixed
     */
    public function percentileCont($column, $percentile = 0.0)
    {
        return $this->aggregate(__FUNCTION__, array($column), $percentile);
    }

    /**
     * Retrieve the standard deviation for a given column.
     *
     * @param string $column
     *
     * @return mixed
     */
    public function stdev($column)
    {
        return $this->aggregate(__FUNCTION__, array($column));
    }

    /**
     * Retrieve the standard deviation of an entire group for a given column.
     *
     * @param string $column
     *
     * @return mixed
     */
    public function stdevp($column)
    {
        return $this->aggregate(__FUNCTION__, array($column));
    }

    /**
     * Get the collected values of the give column.
     *
     * @param string $column
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function collect($column)
    {
        $row = $this->aggregate(__FUNCTION__, array($column));

        $collected = [];

        foreach ($row as $value) {
            $collected[] = $value;
        }

        return new Collection($collected);
    }

    /**
     * Get the count of the disctinct values of a given column.
     *
     * @param string $column
     *
     * @return int
     */
    public function countDistinct($column)
    {
        return (int) $this->aggregate(__FUNCTION__, array($column));
    }

    /**
     * Execute an aggregate function on the database.
     *
     * @param string $function
     * @param array  $columns
     *
     * @return mixed
     */
    public function aggregate($function, $columns = array('*'), $percentile = null)
    {
        $this->aggregate = array_merge([
            'label' => $this->from,
        ], compact('function', 'columns', 'percentile'));

        $previousColumns = $this->columns;

        $results = $this->get($columns);

        // Once we have executed the query, we will reset the aggregate property so
        // that more select queries can be executed against the database without
        // the aggregate value getting in the way when the grammar builds it.
        $this->aggregate = null;

        $this->columns = $previousColumns;

        $values = $this->getRecordsByPlaceholders($results);

        $value = reset($values);
        if(is_array($value)) {
            return current($value);
        } else {
            return $value;
        }
    }

    /**
     * Merge an array of where clauses and bindings.
     *
     * @param array $wheres
     * @param array $bindings
     */
    public function mergeWheres(array $wheres, array $bindings): void
    {
        $this->wheres = array_merge((array) $this->wheres, (array) $wheres);

        $this->bindings['where'] = array_merge_recursive($this->bindings['where'], (array) $bindings);
    }

    /**
     * Get a new instance of the query builder.
     *
     * @return Builder
     */
    public function newQuery(): self
    {
        return new self($this->connection);
    }

    /**
     * Format the value into its string representation.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function formatValue($value)
    {
        // If the value is a date we'll format it according to the specified
        // date format.
        if ($value instanceof DateTime || $value instanceof Carbon) {
            $value = $value->format($this->grammar->getDateFormat());
        }

        return $value;
    }

    /**
     * Add/Drop labels
     * @param array  $labels array of strings(labels)
     * @param string $operation 'add' or 'drop'
     * @return bool true if success, otherwise false
     */
    public function updateLabels(array $labels, $operation = 'add'): bool
    {
        $cypher = $this->grammar->compileUpdateLabels($this, $labels, $operation);

        $result = $this->connection->update($cypher, $this->getBindings());

        return (bool) $result;
    }

    public function getNodesCount(SummarizedResult $result): int
    {
        return count($this->getNodeRecords($result));
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     *
     * @throws BadMethodCallException
     */
    public function __call(string $method, array $parameters): self
    {
        if (Str::startsWith($method, 'where')) {
            return $this->dynamicWhere($method, $parameters);
        }

        return $this->macroCall($method, $parameters);
    }

    /**
     * @param array $values
     * @return array
     */
    private function prepareProperties(array $values): array
    {
        $properties = [];
        foreach ($values as $key => $value) {
            $binding = 'x' . bin2hex(random_bytes(32)) . $key;

            $properties[$key] = Query::parameter($binding);
            $this->bindings[$binding] = $value;
        }
        return $properties;
    }

    private function getLabel(): ?string
    {
        if ($this->currentNode === null) {
            return null;
        }
        return $this->currentNode->label;
    }

    /**
     * @param array $values
     * @return array
     */
    private function prepareAssignments(array $values): array
    {
        $assignments = [];
        foreach ($values as $key => $value) {
            $binding = 'x' . bin2hex(random_bytes(32)) . $key;

            $assignments[] = $this->current->property($key)->assign(Query::parameter($binding));
            $this->bindings[$binding] = $key;
        }

        return $assignments;
    }

    private function addBindings(array $bindings): void
    {
        foreach ($bindings as $key => $value) {
            $this->bindings[$key] = $value;
        }
    }


    /**
     * Explains the query.
     *
     * @return \Illuminate\Support\Collection
     */
    public function explain()
    {
        $sql = $this->toSql();

        $bindings = $this->getBindings();

        $explanation = $this->getConnection()->select('EXPLAIN ' . $sql, $bindings);

        return new \Illuminate\Support\Collection($explanation);
    }

    private function returning(): ReturnClause
    {
        if ($this->return === null) {
            $this->return = $this->dsl->returning([]);
        }

        return $this->return;
    }

    /**
     * @return $this
     */
    protected function forSubQuery(): self
    {
        // TODO
        return new self($this->connection);
    }
}
