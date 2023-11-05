<?php

namespace Vinelab\NeoEloquent\Eloquent\Edges;

use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\CypherMap;
use Laudis\Neo4j\Types\Node;
use Laudis\Neo4j\Types\Relationship;
use Vinelab\NeoEloquent\Connection;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Exceptions\QueryException;
use Vinelab\NeoEloquent\Eloquent\Builder;

abstract class Delegate
{
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
        $this->query = $query;
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

    protected function getRelationshipAttributes(
        $startModel,
        $endModel = null,
        array $properties = [],
        $type = null,
        $direction = null
    ) {
        $attributes = [
            'label' => isset($this->type) ? $this->type : $type,
            'direction' => isset($this->direction) ? $this->direction : $direction,
            'properties' => $properties,
            'start' => [
                'id' => [
                    'key' => $startModel->getKeyName(),
                    'value' => $startModel->getKey(),
                ],
                'label' => $startModel->getDefaultNodeLabel(),
                'properties' => $this->getModelProperties($startModel),
            ],
        ];

        if ($endModel) {
            $attributes['end'] = [
                'id' => [
                    'key' => $endModel->getKeyName(),
                    'value' => $endModel->getKey(),
                ],
                'label' => $endModel->getDefaultNodeLabel(),
                'properties' => $this->getModelProperties($endModel),
            ];
        }

        return $attributes;
    }

    /**
     * Get the model's attributes as query-able properties.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Model $model
     *
     * @return array
     */
    protected function getModelProperties(Model $model)
    {
        $properties = $model->toArray();
        // there shouldn't be an 'id' within the attributes.
        unset($properties['id']);
        // node primary keys should not be passed in as properties.
        unset($properties[$model->getKeyName()]);

        return $properties;
    }

    /**
     * Make a new Relationship instance.
     *
     * @param string                              $type
     * @param \Vinelab\NeoEloquent\Eloquent\Model $startModel
     * @param \Vinelab\NeoEloquent\Eloquent\Model $endModel
     * @param array                               $properties
     *
     * @return Relationship
     */
    protected function makeRelationship($type, $startModel, $endModel, $properties = array())
    {
        $grammar = $this->query->getQuery()->getGrammar();
        $attributes = $this->getRelationshipAttributes($startModel, $endModel, $properties);

        $id = null;
        if (isset($properties['id'])) {
            // when there's an ID within the properties
            // we will remove that so that it doesn't get
            // mixed up with the properties.
            $id = $properties['id'];
            unset($properties['id']);
        }

        return new Relationship($id, $this->asNode($startModel)->getId(), $this->asNode($endModel)->getId(), $type, new CypherMap($properties));
    }

    /**
     * Get the direct relation between two models.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Model $parentModel
     * @param \Vinelab\NeoEloquent\Eloquent\Model $relatedModel
     * @param string                              $direction
     *
     * @return \Everyman\Neo4j\Relationship
     */
    public function firstRelation(Model $parentModel, Model $relatedModel, $type, $direction = 'any')
    {
        $result = $this->firstRelationWithNodes($parentModel, $relatedModel, $type, $direction);

        if (count($result->getRecords()) > 0) {
            return $result->firstRecord()->valueByIndex(0);
        }
    }

    /**
     * @param Model $parentModel
     * @param Model $relatedModel
     * @param $type
     * @param string $direction
     * @return CypherList
     */
    public function firstRelationWithNodes(Model $parentModel, Model $relatedModel, $type, $direction = 'any'): CypherList
    {
        $this->type = $type;
        $this->start = $this->asNode($parentModel);
//        $this->end = $this->asNode($relatedModel);
        $this->direction = $direction;
        // To get a relationship between two models we will have
        // to find the Path between them so first let's transform
        // them to nodes.
        $grammar = $this->query->getQuery()->getGrammar();

        // remove the ID for the related node so that we match
        // the label regardless of the which node it is, matching
        // any relationship of the type.
        // $relatedInstance = $relatedModel->newInstance();

        $attributes = $this->getRelationshipAttributes($parentModel, $relatedModel);
        $query = $grammar->compileGetRelationship($this->query->getQuery(), $attributes);

        return $this->connection->select($query);
    }

    /**
     * Start a batch operation with the database.
     *
     * @return \Everyman\Neo4j\Batch
     *
     * @deprecated No Batches support in NeoClient at 1.3 release
     */
    public function prepareBatch()
    {
        return $this->client->startBatch();
    }

    /**
     * Commit the started batch operation.
     *
     * @return bool
     *
     * @throws \Vinelab\NeoEloquent\QueryException If no open batch to commit.
     */
    public function commitBatch()
    {
        try {
            return $this->client->commitBatch();
        } catch (\Exception $e) {
            throw new QueryException('Error committing batch operation.', array(), $e);
        }
    }

    /**
     * Get the direction value from the Neo4j
     * client according to the direction set on
     * the inheriting class,.
     *
     * @param string $direction
     *
     * @return string
     *
     * @deprecated 2.0 No longer using Everyman's Relationship to get the value
     *                   of the direction constant
     *
     * @throws \Vinelab\NeoEloquent\Exceptions\UnknownDirectionException If the specified $direction is not one of in, out or inout
     */
    public function getRealDirection($direction)
    {
        if (in_array($direction, ['in', 'out'])) {
            $direction = strtoupper($direction);
        }

        return $direction;
    }

    /**
     * Convert a model to a Node object.
     *
     * @param Model $model
     *
     * @return Node
     */
    public function asNode(Model $model): ?Node
    {
        $id = $model->getKey();
        $properties = $model->toArray();
        $label = $model->getDefaultNodeLabel();

        // The id should not be part of the properties since it is treated differently
        if (isset($properties['id'])) {
            unset($properties['id']);
        }

        return new Node($id, new CypherList([$label]), new CypherMap($properties), null);
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
     * @param \Vinelab\NeoEloquent\Connection $name
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
