<?php namespace Vinelab\NeoEloquent\Query;

use Everyman\Neo4j\Node;
use Everyman\Neo4j\Batch;
use Vinelab\NeoEloquent\Connection;
use Vinelab\NeoEloquent\Query\Grammars\CypherGrammar;
use Illuminate\Database\Query\Builder as IlluminateQueryBuilder;

class Builder extends IlluminateQueryBuilder {

    /**
     * The database connection instance
     *
     * @var Vinelab\NeoEloquent\Connection
     */
    protected $connection;

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
     * The current query value bindings.
     *
     * @var array
     */
    protected $bindings = array(
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
    protected $operators = array(
        '+', '-', '*', '/', '%', '^',    // Mathematical
        '=', '<>', '<', '>', '<=', '>=', // Comparison
        'IS NULL', 'IS NOT NULL',
        'AND', 'OR', 'XOR', 'NOT',       // Boolean
        'IN, [x], [x .. y]',             // Collection
        '=~'                             // Regular Expression
    );

    /**
     * Create a new query builder instance.
     *
     * @param Vinelab\NeoEloquent\Connection $connection
     * @return void
     */
    public function __construct(Connection $connection, CypherGrammar $grammar)
    {
        $this->grammar = $grammar;
        $this->grammar->setQuery($this);

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
        $bindings = array_merge($values, $this->getBindings());

        $cypher = $this->grammar->compileUpdate($this, $values);

        return $this->connection->update($cypher, $bindings);
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
		if ( ! in_array(strtolower($operator), $this->operators, true))
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

		$this->wheres[] = compact('type', 'column', 'operator', 'value', 'boolean');

		if ( ! $value instanceof Expression)
		{
            if ($column == 'id('. $this->modelAsNode() .')') $column = 'id';

			$this->addBinding(array($column => $value), 'where');
		}

		return $this;
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

        $this->addBinding(array($property => $value), 'matches');

        return $this;
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

}
