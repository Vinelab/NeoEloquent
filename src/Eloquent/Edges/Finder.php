<?php

namespace Vinelab\NeoEloquent\Eloquent\Edges;

use Laudis\Neo4j\Types\CypherMap;
use Laudis\Neo4j\Types\Relationship;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Collection;
use GraphAware\Neo4j\Client\Formatter\Result;
use Vinelab\NeoEloquent\Traits\ResultTrait;
use GraphAware\Bolt\Result\Result as GraphawareResult;
use GraphAware\Common\Result\RecordViewInterface;

class Finder extends Delegate
{
    use ResultTrait;

    /**
     * Create a new Finder instance.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Builder $query
     * @param \Vinelab\NeoEloquent\Eloquent\Model   $parent
     * @param \Vinelab\NeoEloquent\Eloquent\Model   $related
     * @param string                                $type
     */
    public function __construct(Builder $query)
    {
        parent::__construct($query);
    }

    /**
     * Get the first edge relationship between two models.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Model $parentModel
     * @param \Vinelab\NeoEloquent\Eloquent\Model $relatedModel
     * @param string                              $direction
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In|Out]|null
     */
    public function first(Model $parentModel, Model $relatedModel, $type, $direction)
    {
        // First we get the first relationship instance between the two models
        // based on the given direction.
        $results = $this->firstRelationWithNodes($parentModel, $relatedModel, $type, $direction);

        // Let's stop here if there is no relationship between them.
        if ($results->isEmpty()) {
            return null;
        }

        $record = $results->first();

        // Now we can return the determined edge out of the relation and direction.
        return $this->edgeFromRelationWithDirection($record, $parentModel, $relatedModel, $direction);
    }

    /**
     * Get the edges between two models.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Model $parent
     * @param \Vinelab\NeoEloquent\Eloquent\Model $related
     * @param string|array                        $type
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get(Model $parent, Model $related, $type, $direction)
    {
        // Get the relationships for the parent node of the given type.
        $records = $this->firstRelationWithNodes($parent, $related, $type, $direction);

        $edges = [];
        // Collect the edges out of the found relationships.
        foreach ($records as $record) {
            // Now that we have the direction and the relationship all we need to do is generate the edge
            // and add it to our collection of edges.
            $edges[] = $this->edgeFromRelationWithDirection($record, $parent, $related, $direction);
        }

        return new Collection($edges);
    }

    /**
     * Delete the current relation in the query.
     *
     * @return bool
     */
    public function delete($shouldKeepEndNode)
    {
        $builder = $this->query->getQuery();
        $grammar = $builder->getGrammar();

        $cypher = $grammar->compileDelete($builder, true, $shouldKeepEndNode);

        $result = $this->connection->delete($cypher, $builder->getBindings());

        if ($result instanceof GraphawareResult) {
            $result = true;
        }

        return $result;
    }

    /**
     * Get the first HyperEdge between three models.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Model $parent
     * @param \Vinelab\NeoEloquent\Eloquent\Model $related
     * @param \Vinelab\NeoEloquent\Eloquent\Model $morph
     * @param string                              $type
     * @param string                              $morphType
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge
     */
    public function hyperFirst($parent, $related, $morph, $type, $morphType)
    {
        $left = $this->first($parent, $related, $type, 'out');
        $right = $this->first($related, $morph, $morphType, 'out');

        $edge = new HyperEdge($this->query, $parent, $type, $related, $morphType, $morph);
        if ($left) {
            $edge->setLeft($left);
        }
        if ($right) {
            $edge->setRight($right);
        }

        return $edge;
    }

    /**
     * Get the direction of a relationship out of a Relation instance.
     *
     * @param \GraphAware\Neo4j\Client\Formatter\Result $results
     * @param \Vinelab\NeoEloquent\Eloquent\Model $parent
     * @param \Vinelab\NeoEloquent\Eloquent\Model $related
     *
     * @return string Either 'in' or 'out'
     */
    public function directionFromRelation(Result $results, Model $parent, Model $related)
    {
        // We will match the ids of the parent model and the start node of the relationship
        // and if they match we know that the direction is outgoing, incoming otherwise.
        $nodes = $this->getNodeRecords($results);
        $relations = $this->getRelationshipRecords($results);
        $relation = reset($relations);

        $startNode = $this->getNodeByType($relation, $nodes);

        // We will start by considering the relationship direction to be 'incoming' until
        // we match and find otherwise.
        $direction = 'in';

        $id = ($parent->getKeyName() === 'id') ? $id = $relation->startNodeIdentity() : $startNode->value($parent->getKeyName());

        if ($id === $parent->getKey()) {
            $direction = 'out';
        }

        return $direction;
    }

    /**
     * Get the Edge instance out of a Relationship based on a direction.
     *
     * @param CypherMap $record
     * @param Model $parent
     * @param Model $related
     * @param string $direction can be 'in' or 'out'
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In|Out]
     */
    public function edgeFromRelationWithDirection(CypherMap $record, Model $parent, Model $related, $direction)
    {
        $relationships = $this->getRecordRelationships($record);
        /** @var Relationship $relation */
        $relation = reset($relationships);

        if ($relation) {
            // Based on the direction we are now able to construct the edge class name and call for
            // an instance of it then pass it the actual relationship that was previously found.
            $class = $this->getEdgeClass($direction);
            /** @var Edge $edge */
            $edge = new $class($this->query, $parent, $related, $relation->getType());
            $edge->setRelation($record);

            return $edge;
        }
    }

    public function getModelRelationsForType(Model $startModel, Model $endModel, $type = null, $direction = null)
    {
        // Determine the direction, the real one!
        $direction = $this->getRealDirection($direction);

        $grammar = $this->query->getQuery()->getGrammar();

        $query = $grammar->compileGetRelationship(
            $this->query->getQuery(),
            $this->getRelationshipAttributes($startModel, $endModel, [], $type, $direction)
        );

        $result = $this->connection->statement($query, [], true);

        return $this->getRelationshipRecords($result);
    }

    /**
     * Get the edge class name for a direction.
     *
     * @param string $direction
     *
     * @return string
     */
    public function getEdgeClass($direction)
    {
        return __NAMESPACE__.'\Edge'.ucfirst(mb_strtolower($direction));
    }
}
