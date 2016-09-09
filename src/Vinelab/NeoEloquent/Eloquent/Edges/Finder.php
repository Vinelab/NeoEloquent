<?php namespace Vinelab\NeoEloquent\Eloquent\Edges;

use Everyman\Neo4j\Path;
use Everyman\Neo4j\Relationship;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Finder extends Delegate {

    /**
     * Create a new Finder instance.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Builder $query
     * @param \Vinelab\NeoEloquent\Eloquent\Model   $parent
     * @param \Vinelab\NeoEloquent\Eloquent\Model   $related
     * @param string  $type
     */
    public function __construct(Builder $query)
    {
        parent::__construct($query);
    }

    /**
     * Get the direct relation between two models.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Model  $parentModel
     * @param  \Vinelab\NeoEloquent\Eloquent\Model  $relatedModel
     * @param  string $direction
     * @return \Everyman\Neo4j\Relationship
     */
    public function firstRelation(Model $parentModel, Model $relatedModel, $type, $direction = 'any')
    {
        // To get a relationship between two models we will have
        // to find the Path between them so first let's transform
        // them to nodes.
        $parent = $this->asNode($parentModel);
        $related = $this->asNode($relatedModel);

        // Determine the direction, the real one!
        $direction = $this->getRealDirection($direction);

        // Find the path between parent and related nodes in the previously
        // determined direction according to the type and we will get returned
        // an instance of \Everyman\Neo4j\Path which will lead us to the relationship.
        $path = $parent->findPathsTo($related, $type, $direction)->getSinglePath();

        // Since we are sure that the relation between these two nodes is direct
        // with depth of 1 we will get the path and return the first relationship (if any).
        if ( ! is_null($path))
        {
            // Tell the path that we need to work with the relationships now
            // so that it sets the nodes aside.
            $path->setContext(Path::ContextRelationship);

            $relationships = $path->getRelationships();

            return  reset($relationships);
        }
    }

    /**
     * Get the first edge relationship between two models.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Model  $parentModel
     * @param  \Vinelab\NeoEloquent\Eloquent\Model  $relatedModel
     * @param  string $direction
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In|Out]|null
     */
    public function first(Model $parentModel, Model $relatedModel, $type, $direction = 'any')
    {
        // First we get the first relationship instance between the two models
        // based on the given direction.
        $relation = $this->firstRelation($parentModel, $relatedModel, $type, $direction);

        // Let's stop here if there is no relationship between them.
        if (is_null($relation)) return null;

        // Now we can return the determined edge out of the relation and direction.
        return $this->edgeFromRelationWithDirection($relation, $parentModel, $relatedModel, $direction);
    }

    /**
     * Get the edges between two models.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Model  $parent
     * @param  \Vinelab\NeoEloquent\Eloquent\Model  $related
     * @param  string|array $type
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get(Model $parent, Model $related, $type = [])
    {
        // Get the relationships for the parent node of the given type.
        $relationships = $this->getModelRelationsForType($parent, $type);

        $edges = [];
        // Collect the edges out of the found relationships.
        foreach ($relationships as $relationship)
        {
            // We need the direction so that we can generate an Edge[In|Out] instance accordingly.
            $direction = $this->directionFromRelation($relationship, $parent, $related);
            // Now that we have the direction and the relationship all we need to do is generate the edge
            // and add it to our collection of edges.
            $edges[] = $this->edgeFromRelationWithDirection($relationship, $parent, $related, $direction);
        }

        return new Collection($edges);
    }

    /**
     * Get the first HyperEdge between three models.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $parent
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $related
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $morph
     * @param  string $type
     * @param  string $morphType
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge
     */
    public function hyperFirst($parent, $related, $morph, $type, $morphType)
    {
        $left  = $this->first($parent, $related, $type, 'out');
        $right = $this->first($related, $morph, $morphType, 'out');

        $edge = new HyperEdge($this->query, $parent, $type, $related, $morphType, $morph);
        if ($left)  $edge->setLeft($left);
        if ($right) $edge->setRight($right);

        return $edge;
    }

    /**
     * Get the direction of a relationship out of a Relation instance.
     *
     * @param  \Everyman\Neo4j\Relationship $relation
     * @param  \Vinelab\NeoEloquent\Eloquent\Model        $parent
     * @param  \Vinelab\NeoEloquent\Eloquent\Model        $related
     * @return string Either 'in' or 'out'
     */
    public function directionFromRelation(Relationship $relation, Model $parent, Model $related)
    {
        // We will match the ids of the parent model and the start node of the relationship
        // and if they match we know that the direction is outgoing, incoming otherwise.
        $node = $relation->getStartNode();

        // We will start by considering the relationship direction to be 'incoming' until
        // we match and find otherwise.
        $direction = 'in';

        if ($node->getId() === $parent->getKey())
        {
            $direction = 'out';
        }

        return $direction;
    }

    /**
     * Get the Edge instance out of a Relationship based on a direction.
     *
     * @param  \Everyman\Neo4j\Relationship $relation
     * @param  string $direction
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In|Out]
     */
    public function edgeFromRelationWithDirection(Relationship $relation, Model $parent, Model $related, $direction)
    {
        // If the direction is of type 'any' we need to figure out the relationship direction
        // from the determined relation.
        if ($direction == 'any')
        {
            $direction = $this->directionFromRelation($relation, $parent, $related);
        }

        // Based on the direction we are now able to construct the edge class name and call for
        // an instance of it then pass it the actual relationship that was previously found.
        $class = $this->getEdgeClass($direction);
        $edge = new $class($this->query, $parent, $related, $relation->getType());
        $edge->setRelation($relation, true);

        return $edge;
    }

    public function getModelRelationsForType(Model $parentModel, $type = array(), $direction = 'any')
    {
        // Get the Node representation of the parent model so that we can
        // query its relationships.
        $parent = $this->asNode($parentModel);

        // Determine the direction, the real one!
        $direction = $this->getRealDirection($direction);

        return $parent->getRelationships((array) $type, $direction);
    }

    /**
     * Get the edge class name for a direction.
     *
     * @param  string $direction
     * @return string
     */
    public function getEdgeClass($direction)
    {
        return __NAMESPACE__.'\Edge'. ucfirst(mb_strtolower($direction));
    }

}
