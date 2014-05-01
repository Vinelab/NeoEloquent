<?php namespace Vinelab\NeoEloquent\Query;

use Everyman\Neo4j\Node;
use Everyman\Neo4j\Batch;
use Vinelab\NeoEloquent\Connection;
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
	 * All of the available clause operators.
	 *
	 * @var array
	 */
    protected $operators = array(
        '+', '-', '*', '/', '%', '^',    // Mathematical
        '=', '<>', '<', '>', '<=', '>=', // Comparison
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
    public function __construct(Connection $connection)
    {
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
     * Convert a string into a neo4j Label
     *
     * @param   string  $label
     * @return Everyman\Neo4j\Label
     */
    public function makeLabel($label)
    {
        return $this->client->makeLabel($label);
    }

}
