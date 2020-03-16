<?php

namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Collection;
use Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut;

class MorphTo extends OneRelation
{
    /**
     * The edge direction of this relatioship.
     *
     * @var string
     */
    protected $edgeDirection = 'in';

    /**
     * The type of the polymorphic relation (in graph this is the relationship label).
     *
     * @var string
     */
    protected $morphType;

    public function __construct(Builder $query, Model $parent, $relationType, $otherKey, $type, $relation)
    {
        $this->morphType = $type;

        parent::__construct($query, $parent, $relationType, $otherKey, $relation);
    }

    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            // Get the parent node's placeholder.
            $parentNode = $this->query->getQuery()->modelAsNode($this->parent->nodeLabel());
            // Tell the query that we need the morph model and the relationship represented by CypherGrammar
            // statically with 'r'.
            $this->query->select($this->relation, 'r');
            // Add morph mutation that will tell the parser about the property name on the Relationship that is holding
            // the class name of our morph model so that they can instantiate the correct one, and pass the relation
            // name as an indicator of the Node that has our morph attributes in the query.
            $this->query->addMorphMutation($this->relation);
            // Set the parent node's placeholder as the RETURN key.
            $this->query->getQuery()->from = array($parentNode);
            // Build the MATCH ()<-[]-() Cypher clause.
            $this->query->matchMorphOut($this->parent, $this->relation, $this->relationType, $this->parent->{$this->relationType});
            // Add WHERE clause over the parent node's matching key = value.
            $this->query->where($this->relationType, '=', $this->parent->{$this->relationType});
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     */
    public function addEagerConstraints(array $models)
    {
        // Get the parent node's placeholder.
        $parentNode = $this->query->getQuery()->modelAsNode($this->parent->nodeLabel());
        // Tell the query that we need the morph model and the relationship represented by CypherGrammar
        // statically with 'r'.
        $this->query->select('r', $parentNode, $this->relation);
        // Add morph mutation that will tell the parser about the property name on the Relationship that is holding
        // the class name of our morph model so that they can instantiate the correct one, and pass the relation
        // name as an indicator of the Node that has our morph attributes in the query.
        $this->query->addMutation($parentNode, $this->parent);
        $this->query->addEagerMorphMutation($this->relation);
        // Set the parent node's placeholder as the RETURN key.
        $this->query->getQuery()->from = array($parentNode);
        // Build the MATCH ()<-[]-() Cypher clause.
        $this->query->matchMorphOut($this->parent, $this->relation, $this->relationType, $this->parent->{$this->relationType});
        // Add WHERE clause over the parent node's matching keys [values...].
        $this->query->whereIn($this->relationType, $this->getKeys($models));
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param array                                    $models
     * @param \Illuminate\Database\Eloquent\Collection $results
     * @param string                                   $relation
     *
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        // This relationship deals with a One-To-One morph type so we'll just extract
        // the first model out of the results and return it.
        $matched = parent::match($models, $results, $relation);

        return array_map(function ($match) use ($relation) {
            if (isset($match[$relation]) && isset($match[$relation][0])) {
                $match->setRelation($relation, $match[$relation][0]);
            }

            return $match;

        }, $matched);
    }

    /**
     * Get an instance of the EdgeIn relationship.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Model $model
     * @param array                               $attributes
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut
     */
    public function getEdge(Model $model = null, $attributes = array())
    {
        $model = (!is_null($model)) ? $model : $this->parent->{$this->relation};

        // Indicate a unique relationship since this involves one other model.
        $unique = true;

        return new EdgeOut($this->query, $this->parent, $model, $this->relationType, $attributes, $unique);
    }
}
