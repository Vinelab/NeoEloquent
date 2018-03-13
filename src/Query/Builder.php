<?php namespace Vinelab\NeoEloquent\Query;

use Closure;
use DateTime;
use Carbon\Carbon;
use Vinelab\NeoEloquent\Connection;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Collection;
use Vinelab\NeoEloquent\Query\Grammars\Grammar;
use Vinelab\NeoEloquent\Query\Processors\Processor;
use Illuminate\Database\Query\Processors\Processor as IlluminateProcessor;
use Illuminate\Database\Query\Builder as IlluminateQueryBuilder;

class Builder extends IlluminateQueryBuilder {

    /**
     * The database connection instance
     *
     * @var Vinelab\NeoEloquent\Connection
     */
    public $connection;

    /**
     * The database active client handler
     *
     * @var Everyman\Neo4j\Client
     */
    protected $client;

    /**
     * The matches constraints for the query.
     *
     * @var array
     */
    public $matches = array();

    /**
     * The WITH parts of the query.
     *
     * @var array
     */
    public $with = array();

    /**
     * The current query value bindings.
     *
     * @var array
     */
    public $bindings = array(
        'matches'=> [],
        'select' => [],
        'join'   => [],
        'where'  => [],
        'having' => [],
        'order'  => []
    );

    /**
	 * All of the available clause operators.
	 *
	 * @var array
	 */
    public $operators = array(
        '+', '-', '*', '/', '%', '^',          // Mathematical
        '=', '<>', '<', '>', '<=', '>=',       // Comparison
        'is null', 'is not null',
        'and', 'or', 'xor', 'not',             // Boolean
        'in', '[x]', '[x .. y]',               // Collection
        '=~',                                  // Regular Expression
        'starts with', 'ends with', 'contains' // String matching
    );

    /**
     * Create a new query builder instance.
     *
     * @param Vinelab\NeoEloquent\Connection $connection
     * @param  \Illuminate\Database\Query\Grammars\Grammar  $grammar
     * @param  \Illuminate\Database\Query\Processors\Processor  $processor
     * @return void
     */
    public function __construct(Connection $connection, Grammar $grammar, IlluminateProcessor $processor)
    {
        $this->grammar = $grammar;
        $this->grammar->setQuery($this);
        $this->processor = $processor;

        $this->connection = $connection;

        $this->client = $connection->getClient();
    }

    /**
	 * Set the node's label which the query is targeting.
	 *
	 * @param  string  $label
	 * @return \Vinelab\NeoEloquent\Query\Builder|static
	 */
    public function from($label)
    {
        $this->from = $label;

        return $this;
    }

    /**
	 * Insert a new record and get the value of the primary key.
	 *
	 * @param  array   $values
	 * @param  string  $sequence
	 * @return int
	 */
    public function insertGetId(array $values, $sequence = null)
    {
        // create a neo4j Node
        $node = $this->client->makeNode();

        // set its properties
        foreach ($values as $key => $value)
        {
            $value = $this->formatValue($value);

            $node->setProperty($key, $value);
        }

        // save the node
        $node->save();

        // get the saved node id
        $id = $node->getId();

        // set the labels
        $node->addLabels(array_map(array($this, 'makeLabel'), $this->from));

        return $id;
    }

    /**
     * Update a record in the database.
     *
     * @param  array  $values
     * @return int
     */
    public function update(array $values)
    {
        $cypher = $this->grammar->compileUpdate($this, $values);

        $bindings = $this->getBindingsMergedWithValues($values);

        $updated = $this->connection->update($cypher, $bindings);

        return (isset($updated[0]) && isset($updated[0][0])) ? $updated[0][0] : 0;
    }

    /**
     *  Bindings should have the keys postfixed with _update as used
     *  in the CypherGrammar so that we differentiate them from
     *  query bindings avoiding clashing values.
     *
     * @param  array $values
     * @return array
     */
    protected function getBindingsMergedWithValues(array $values)
    {
        $bindings = [];

        foreach ($values as $key => $value)
        {
            $bindings[$key .'_update'] = $value;
        }

        return array_merge($this->getBindings(), $bindings);
    }

    /**
     * Get the current query value bindings in a flattened array
     * of $key => $value.
     *
     * @return array
     */
    public function getBindings()
    {
        $bindings = [];

        // We will run through all the bindings and pluck out
        // the component (select, where, etc.)
        foreach($this->bindings as $component => $binding)
        {
            if ( ! empty($binding))
            {
                // For every binding there could be multiple
                // values set so we need to add all of them as
                // flat $key => $value item in our $bindings.
                foreach ($binding as $key => $value)
                {
                    $bindings[$key] = $value;
                }
            }
        }

        return $bindings;
    }

    /**
     * Removes the order by clause when counting for the paginator.
     * @author Gaba93
     */
    private function backupFieldsForCount() {
        $this->orders_backup = $this->orders;
        $this->orders = null;
    }

    /**
     * Readds the order clause.
     * @author Gaba93
     */
    private function restoreFieldsForCount() {
        $this->orders = $this->orders_backup;
        $this->orders_backup = null;
    }

    /**
    * Get the count of the total records for the paginator.
    *
    * @param  array  $columns
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

        $row = null;
        if ($results->offsetExists(0)) {
                $row = $results->offsetGet(0);
                $count = $row->offsetGet(0);
                return $count;
        } else {
                return 0;
        }
    }

    /**
	 * Add a basic where clause to the query.
	 *
	 * @param  string  $column
	 * @param  string  $operator
	 * @param  mixed   $value
	 * @param  string  $boolean
	 * @return \Illuminate\Database\Query\Builder|static
	 *
	 * @throws \InvalidArgumentException
	 */
	public function where($column, $operator = null, $value = null, $boolean = 'and')
	{
        // First we check whether the operator is 'IN' so that we call whereIn() on it
        // as a helping hand and centralization strategy, whereIn knows what to do with the IN operator.
        if (mb_strtolower($operator) == 'in')
        {
            return $this->whereIn($column, $value, $boolean);
        }

        // If the column is an array, we will assume it is an array of key-value pairs
		// and can add them each as a where clause. We will maintain the boolean we
		// received when the method was called and pass it into the nested where.
		if (is_array($column))
		{
			return $this->whereNested(function(IlluminateQueryBuilder $query) use ($column)
			{
				foreach ($column as $key => $value)
				{
					$query->where($key, '=', $value);
				}
			}, $boolean);
		}

		if (func_num_args() == 2)
		{
			list($value, $operator) = array($operator, '=');
		}
		elseif ($this->invalidOperatorAndValue($operator, $value))
		{
			throw new \InvalidArgumentException("Value must be provided.");
		}

		// If the columns is actually a Closure instance, we will assume the developer
		// wants to begin a nested where statement which is wrapped in parenthesis.
		// We'll add that Closure to the query then return back out immediately.
		if ($column instanceof Closure)
		{
			return $this->whereNested($column, $boolean);
		}

		// If the given operator is not found in the list of valid operators we will
		// assume that the developer is just short-cutting the '=' operators and
		// we will set the operators to '=' and set the values appropriately.
		if ( ! in_array(mb_strtolower($operator), $this->operators, true))
		{
			list($value, $operator) = array($operator, '=');
		}

		// If the value is a Closure, it means the developer is performing an entire
		// sub-select within the query and we will need to compile the sub-select
		// within the where clause to get the appropriate query record results.
		if ($value instanceof Closure)
		{
			return $this->whereSub($column, $operator, $value, $boolean);
		}

		// If the value is "null", we will just assume the developer wants to add a
		// where null clause to the query. So, we will allow a short-cut here to
		// that method for convenience so the developer doesn't have to check.
		if (is_null($value))
		{
			return $this->whereNull($column, $boolean, $operator != '=');
		}

		// Now that we are working with just a simple query we can put the elements
		// in our array and add the query binding to our array of bindings that
		// will be bound to each SQL statements when it is finally executed.
		$type = 'Basic';

        $property = $column;

        // When the column is an id we need to treat it as a graph db id and transform it
        // into the form of id(n) and the typecast the value into int.
        if ($column == 'id')
        {
            $column = 'id('. $this->modelAsNode() .')';
            $value = intval($value);
        }
        // When it's been already passed in the form of NodeLabel.id we'll have to
        // re-format it into id(NodeLabel)
        elseif (preg_match('/^.*\.id$/', $column))
        {
            $parts = explode('.', $column);
            $column = sprintf('%s(%s)', $parts[1], $parts[0]);
            $value = intval($value);
        }
        // Also if the $column is already a form of id(n) we'd have to type-cast the value into int.
        elseif (preg_match('/^id\(.*\)$/', $column)) $value = intval($value);

        $binding = $this->prepareBindingColumn($column);

        $this->wheres[] = compact('type', 'binding', 'column', 'operator', 'value', 'boolean');

        $property = $this->wrap($binding);

        if ( ! $value instanceof Expression)
        {
			$this->addBinding([$property => $value], 'where');
		}

		return $this;
	}

    /**
     * Increment the value of an existing column on a where clause.
     * Used to allow querying on the same attribute with different values.
     *
     * @param  string $column
     * @return string
     */
    protected function prepareBindingColumn($column)
    {
        $count = $this->columnCountForWhereClause($column);
        return ($count > 0) ? $column .'_'. ($count + 1) : $column;
    }

    /**
     * Get the number of occurrences of a column in where clauses.
     *
     * @param  string $column
     * @return int
     */
    protected function columnCountForWhereClause($column)
    {
        if (is_array($this->wheres))
            return count(array_filter($this->wheres, function($where) use($column) {
                return $where['column'] == $column;
            }));
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param  string  $column
     * @param  mixed   $values
     * @param  string  $boolean
     * @param  bool    $not
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotIn' : 'In';

        // If the value of the where in clause is actually a Closure, we will assume that
        // the developer is using a full sub-select for this "in" statement, and will
        // execute those Closures, then we can re-construct the entire sub-selects.
        if ($values instanceof Closure)
        {
            return $this->whereInSub($column, $values, $boolean, $not);
        }

        $property = $column;

        if ($column == 'id') $column = 'id('. $this->modelAsNode() .')';

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        $property = $this->wrap($property);

        $this->addBinding([$property => $values], 'where');

        return $this;
    }

    /**
     * Add a where between statement to the query.
     *
     * @param  string  $column
     * @param  array   $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function whereBetween($column, array $values, $boolean = 'and', $not = false)
    {
        $type = 'between';

        $property = $column;

        if ($column == 'id') $column = 'id('. $this->modelAsNode() .')';

        $this->wheres[] = compact('column', 'type', 'boolean', 'not');

        $this->addBinding([$property => $values], 'where');

        return $this;
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param  string  $column
     * @param  string  $boolean
     * @param  bool    $not
     * @return \Illuminate\Database\Query\Builder|static
     */
    public function whereNull($column, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';

        if ($column == 'id') $column = 'id('. $this->modelAsNode() .')';

        $binding = $this->prepareBindingColumn($column);

        $this->wheres[] = compact('type', 'column', 'boolean', 'binding');

        return $this;
    }

    /**
     * Add a WHERE statement with carried identifier to the query.
     *
     * @param  string $column
     * @param  string $operator
     * @param  string $value
     * @param  string $boolean
     * @return \Illuminate\Database\Query\Builder|static
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
     * @param  array  $parts
     * @return \Vinelab\NeoEloquent\Query\Builder|static
     */
    public function with(array $parts)
    {
        foreach ($parts as $key => $part)
        {
            $this->with[$key] = $part;
        }

        return $this;
    }

    /**
     * Insert a new record into the database.
     *
     * @param  array  $values
     * @return bool
     */
    public function insert(array $values)
    {
        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient for building these
        // inserts statements by verifying the elements are actually an array.
        if ( ! is_array(reset($values)))
        {
            $values = array($values);
        }

        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient for building these
        // inserts statements by verifying the elements are actually an array.
        else
        {
            foreach ($values as $key => $value)
            {
                $value = $this->formatValue($value);
                ksort($value); $values[$key] = $value;
            }
        }

        // We'll treat every insert like a batch insert so we can easily insert each
        // of the records into the database consistently. This will make it much
        // easier on the grammars to just handle one type of record insertion.
        $bindings = array();

        foreach ($values as $record)
        {
            $bindings[] = $record;
        }

        $cypher = $this->grammar->compileInsert($this, $values);

        // Once we have compiled the insert statement's Cypher we can execute it on the
        // connection and return a result as a boolean success indicator as that
        // is the same type of result returned by the raw connection instance.
        $bindings = $this->cleanBindings($bindings);

        return $this->connection->insert($cypher, $bindings);
    }

    /**
     * Create a new node with related nodes with one database hit.
     *
     * @param  array  $model
     * @param  array  $related
     * @return \Vinelab\NeoEloquent\Eloquent\Model
     */
    public function createWith(array $model, array $related)
    {
        $cypher = $this->grammar->compileCreateWith($this, compact('model', 'related'));

        // Indicate that we need the result returned as is.
        return $this->connection->statement($cypher, [], true);
    }

    /**
	 * Execute the query as a fresh "select" statement.
	 *
	 * @param  array  $columns
	 * @return array|static[]
	 */
	public function getFresh($columns = array('*'))
	{
		if (is_null($this->columns)) $this->columns = $columns;

        return $this->runSelect();
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
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $parent       The parent model of the relationship
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $related      The related model
     * @param  string $relatedNode  The related node' placeholder
     * @param  string $relationship The relationship title
     * @param  string $property     The parent's property we are matching against
     * @param  string $value
     * @param  string $direction Possible values are in, out and in-out
     * @return \Vinelab\NeoEloquent\Query\Builder|static
     */
    public function matchRelation($parent, $related, $relatedNode, $relationship, $property, $value = null, $direction = 'out')
    {
        $parentLabels  = $parent->getTable();
        $relatedLabels = $related->getTable();
        $parentNode    = $this->modelAsNode($parentLabels);

        $this->matches[] = array(
            'type'         => 'Relation',
            'property'     => $property,
            'direction'    => $direction,
            'relationship' => $relationship,
            'parent' => array(
                'node'   => $parentNode,
                'labels' => $parentLabels
            ),
            'related' => array(
                'node'   => $relatedNode,
                'labels' => $relatedLabels
            )
        );

        $this->addBinding(array($this->wrap($property) => $value), 'matches');

        return $this;
    }

    public function matchMorphRelation($parent, $relatedNode, $property, $value = null, $direction = 'out')
    {
        $parentLabels = $parent->getTable();
        $parentNode = $this->modelAsNode($parentLabels);

        $this->matches[] = array(
            'type'      => 'MorphTo',
            'property'  => $property,
            'direction' => $direction,
            'related'   => array('node' => $relatedNode),
            'parent'    => array(
                'node'   => $parentNode,
                'labels' => $parentLabels
            )
        );

        $this->addBinding(array($property => $value), 'matches');

        return $this;
    }

    /**
     * the percentile of a given value over a group,
     * with a percentile from 0.0 to 1.0.
     * It uses a rounding method, returning the nearest value to the percentile.
     *
     * @param  string $column
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
     * @param  string $column
     * @return mixed
     */
    public function percentileCont($column, $percentile = 0.0)
    {
        return $this->aggregate(__FUNCTION__, array($column), $percentile);
    }

    /**
     * Retrieve the standard deviation for a given column.
     *
     * @param  string $column
     * @return mixed
     */
    public function stdev($column)
    {
        return $this->aggregate(__FUNCTION__, array($column));
    }

    /**
     * Retrieve the standard deviation of an entire group for a given column.
     *
     * @param  string $column
     * @return mixed
     */
    public function stdevp($column)
    {
        return $this->aggregate(__FUNCTION__, array($column));
    }

    /**
     * Get the collected values of the give column.
     *
     * @param  string $column
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function collect($column)
    {
        $row = $this->aggregate(__FUNCTION__, array($column));

        $collected = [];

        foreach ($row as $value)
        {
            $collected[] = $value;
        }

        return new Collection($collected);
    }

    /**
     * Get the count of the disctinct values of a given column.
     *
     * @param  string $column
     * @return int
     */
    public function countDistinct($column)
    {
        return (int) $this->aggregate(__FUNCTION__, array($column));
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  array   $columns
     * @return mixed|static
     */
    public function first($columns = array('*'))
    {
        $results = $this->take(1)->get($columns)->current();

        return (isset($results[0]) && count($results[0]) > 0) ? $results[0]->getProperties() : null;
    }

    /**
     * Execute an aggregate function on the database.
     *
     * @param  string  $function
     * @param  array   $columns
     * @return mixed
     */
    public function aggregate($function, $columns = array('*'), $percentile = null)
    {
        $this->aggregate = array_merge([
            'label' => $this->from
        ], compact('function', 'columns', 'percentile'));

        $previousColumns = $this->columns;

        $results = $this->get($columns);

        // Once we have executed the query, we will reset the aggregate property so
        // that more select queries can be executed against the database without
        // the aggregate value getting in the way when the grammar builds it.
        $this->aggregate = null;

        $this->columns = $previousColumns;

        if ($results->valid())
        {
            return $results->current()[0];
        }
    }

    /**
     * Add a binding to the query.
     *
     * @param  mixed   $value
     * @param  string  $type
     * @return \Illuminate\Database\Query\Builder
     */
    public function addBinding($value, $type = 'where')
    {
        if (is_array($value))
        {
            $key = array_keys($value)[0];

            if (strpos($key, '.') !== false)
            {
                $binding = $value[$key];
                unset($value[$key]);
                $key = explode('.', $key)[1];
                $value[$key] = $binding;
            }
        }

        if ( ! array_key_exists($type, $this->bindings))
        {
            throw new \InvalidArgumentException("Invalid binding type: {$type}.");
        }

        if (is_array($value))
        {
            $this->bindings[$type] = array_merge($this->bindings[$type], $value);
        }
        else
        {
            $this->bindings[$type][] = $value;
        }

        return $this;
    }

    /**
     * Convert a string into a Neo4j Label.
     *
     * @param   string  $label
     * @return Everyman\Neo4j\Label
     */
    public function makeLabel($label)
    {
        return $this->client->makeLabel($label);
    }

    /**
     * Tranfrom a model's name into a placeholder
     * for fetched properties. i.e.:
     *
     * MATCH (user:`User`)... "user" is what this method returns
     * out of User (and other labels).
     * PS: It consideres the first value in $labels
     *
     * @param  array $labels
     * @return string
     */
    public function modelAsNode(array $labels = null)
    {
        $labels = ( ! is_null($labels)) ? $labels : $this->from;

        return $this->grammar->modelAsNode($labels);
    }

    /**
     * Merge an array of where clauses and bindings.
     *
     * @param  array  $wheres
     * @param  array  $bindings
     * @return void
     */
    public function mergeWheres($wheres, $bindings)
    {
        $this->wheres = array_merge((array) $this->wheres, (array) $wheres);

        $this->bindings['where'] = array_merge_recursive($this->bindings['where'], (array) $bindings);
    }

    public function wrap($property)
    {
        return $this->grammar->getIdReplacement($property);
    }

	/**
	 * Get a new instance of the query builder.
	 *
	 * @return \Illuminate\Database\Query\Builder
	 */
	public function newQuery()
	{
		return new Builder($this->connection, $this->grammar, $this->getProcessor());
	}

    /**
     * Fromat the value into its string representation.
     *
     * @param  mixed $value
     *
     * @return string
     */
    protected function formatValue($value)
    {
        // If the value is a date we'll format it according to the specified
        // date format.
        if ($value instanceof DateTime || $value instanceof Carbon)
        {
            $value = $value->format($this->grammar->getDateFormat());
        }

        return $value;
    }

    /*
     * Add/Drop labels
     * @param $labels array array of strings(labels)
     * @param $operation string 'add' or 'drop'
     * @return bool true if success, otherwise false
     */
    public function updateLabels($labels, $operation = 'add')
    {

        $cypher = $this->grammar->compileUpdateLabels($this, $labels, $operation);

        $updated = $this->connection->update($cypher, $this->getBindings());

        return (isset($updated[0]) && isset($updated[0][0])) ? $updated[0][0] : 0;
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return \Illuminate\Support\Collection
     */
    public function get($columns = ['*'])
    {
        $original = $this->columns;

        if (is_null($original)) {
            $this->columns = $columns;
        }

        $results = $this->processor->processSelect($this, $this->runSelect());

        $this->columns = $original;

        return $results;
    }
}
