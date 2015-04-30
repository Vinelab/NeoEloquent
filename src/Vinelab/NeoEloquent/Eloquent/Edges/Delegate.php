<?php namespace Vinelab\NeoEloquent\Eloquent\Edges;

use Vinelab\NeoEloquent\Connection;
use Neoxygen\NeoClient\Formatter\Node;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\QueryException;
use Vinelab\NeoEloquent\Eloquent\Builder;
use Vinelab\NeoEloquent\UnknownDirectionException;

abstract class Delegate {

     /**
     * The Eloquent builder instance.
     *
     * @var \Vinelab\NeoEloquent\Eloquent\Builder
     */
    protected $query;

    /**
     * The database connection.
     *
     * @var \Vinelab\NeoEloquent\Connection
     */
    protected $connection;

    /**
     * The database client.
     *
     * @var \Neoxygen\NeoClient\Client
     */
    protected $client;

    /**
     * Create a new delegate instance.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Builder $query
     * @param \Vinelab\NeoEloquent\Eloquent\Model   $parent
     */
    public function __construct(Builder $query)
    {
        $this->query  = $query;
        $model = $query->getModel();

        // Setup the database connection and client.
        $this->connection = $model->getConnection();
        $this->client = $this->connection->getClient();
    }

    /**
     * Get a new Finder instance.
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Finder
     */
    public function newFinder()
    {
        return new Finder($this->query);
    }

    /**
     * Make a new Relationship instance.
     *
     * @param  string $type
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $startModel
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $endModel
     * @param  array  $properties
     * @return \Everyman\Neo4j\Relationship
     */
    protected function makeRelationship($type, $startModel, $endModel, $properties = array())
    {
        $grammar = $this->query->getQuery()->getGrammar();

        $query = $grammar->compileRelationship(
            $this->query->getQuery(),
            [
                'label' => $this->type,
                'direction' => $this->direction,
                'start' => [
                    'id' => [
                        'key' => $startModel->getKeyName(),
                        'value' => $this->start->getId(),
                    ],
                    'label' => $this->start->getLabels(),
                    'properties' => $this->start->getProperties(),
                ],
                'end' => [
                    'id' => [
                        'key' => $endModel->getKeyName(),
                        'value' => $this->end->getId(),
                    ],
                    'label' => $this->end->getLabels(),
                    'properties' => $this->end->getProperties(),
                ],
            ]
        );

        $result = $this->connection->statement($query, [], true);

        return current($result->getRelationships());
    }

    /**
     * Start a batch operation with the database.
     *
     * @return \Everyman\Neo4j\Batch
     */
    public function prepareBatch()
    {
        return $this->client->startBatch();
    }

    /**
     * Commit the started batch operation.
     *
     * @return boolean
     *
     * @throws  \Vinelab\NeoEloquent\QueryException If no open batch to commit.
     */
    public function commitBatch()
    {
        try {

            return $this->client->commitBatch();

        } catch (\Exception $e)
        {
            throw new QueryException('Error committing batch operation.', array(), $e);
        }
    }

    /**
     * Get the direction value from the Neo4j
     * client according to the direction set on
     * the inheriting class,
     *
     * @param  string $direction
     * @return string
     *
     * @throws UnknownDirectionException If the specified $direction is not one of in, out or inout
     */
    public function getRealDirection($direction)
    {
        if ($direction == 'in' or $direction == 'out')
        {
            $direction = ucfirst($direction);

        } elseif ($direction == 'any')
        {
            $direction = 'All';

        } else
        {
            throw new UnknownDirectionException($direction);
        }

        $direction = "Direction". $direction;

        return constant("Everyman\Neo4j\Relationship::". $direction);
    }

    /**
     * Convert a model to a Node object.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $model
     * @return \Neoxygen\NeoClient\Formatter\Node
     */
    public function asNode(Model $model)
    {
        $id = $model->getKey();
        $properties = $model->toArray();
        $label = $model->getDefaultNodeLabel();

        // The id should not be part of the properties since it is treated differently
        if (isset($properties['id']))
        {
            unset($properties['id']);
        }

        return new Node($id, $label, $properties);
    }

    /**
     * Get the NeoEloquent connection for this relation.
     *
     * @return \Vinelab\NeoEloquent\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set the database connection.
     *
     * @param  \Vinelab\NeoEloquent\Connection  $name
     * @return void
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the current connection name.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->query->getModel()->getConnectionName();
    }

}
