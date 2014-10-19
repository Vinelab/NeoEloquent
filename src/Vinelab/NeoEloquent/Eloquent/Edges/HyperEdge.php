<?php namespace Vinelab\NeoEloquent\Eloquent\Edges;

use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Edges\Relation;

class HyperEdge extends Relation {

    protected $direction = 'any';

    /**
     * The morph Model instance
     * representing the 3rd Node of the relationship.
     *
     * @var \Vinelab\NeoEloquent\Eloquent\Model
     */
    protected $morph;

    /**
     * The morph relation type name representing the relationship
     * name b/w the related model and the morph model.
     *
     * @var string
     */
    protected $morphType;

    /**
     * The left side Edge of the relationship.
     *
     * @var \Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut
     */
    protected $left;

    /**
     * The right side Edge of the relationship.
     *
     * @var \Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut
     */
    protected $right;

    /**
     * The Hyper node of the relationship (sits in the middle b/w start and end).
     *
     * @var \Everyman\Neo4j\Node
     */
    protected $hyper;

    /**
     * Create a new HyperEdge instance.
     *
     * @param Vinelab\NeoEloquent\Eloquent\Builder $query
     * @param Vinelab\NeoEloquent\Eloquent\Model   $parent
     * @param string  $type
     * @param Vinelab\NeoEloquent\Eloquent\Model   $related
     * @param string  $morphType
     * @param Vinelab\NeoEloquent\Eloquent\Model   $morph
     */
    public function __construct(Builder $query, Model $parent, $type, Model $related, $morphType, Model $morph, $attributes = array())
    {
        $this->morph = $morph;
        $this->morphType = $morphType;

        // This is not a unique relationship since it involves multiple models.
        $unique = false;

        parent::__construct($query, $parent, $related, $type, $attributes, $unique);
    }

     /**
     * Initialize the relationship by setting up nodes and edges,
     *
     * @return void
     *
     * @throws  \Vinelab\NeoEloquent\NoEdgeDirectionException If $direction is not set on the inheriting relation.
     */
    public function initRelation()
    {
        // Turn models into nodes
        $this->start = $this->asNode($this->parent);
        $this->hyper = $this->asNode($this->related);
        $this->end   = $this->asNode($this->morph);

        // Not a unique relationship since it involves multiple models.
        $unique = false;

        // Setup left and right edges
        $this->left  = new EdgeOut($this->query, $this->parent, $this->related, $this->type, $this->attributes, $unique);
        $this->right = new EdgeOut($this->query, $this->related, $this->morph, $this->morphType, $this->attributes, $unique);
        // Set the morph type to the relationship so that we know who we're talking to.
        $this->right->morph_type = get_class($this->morph);
    }

    /**
     * Get the left side Edge of this relationship.
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut
     */
    public function left()
    {
        return $this->left;
    }

    /**
     * Set the left side Edge of this relation.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Edges\Relation $left
     * @return  void
     */
    public function setLeft($left)
    {
        $this->left = $left;
    }

    /**
     * Get the right side Edge of this relationship.
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut
     */
    public function right()
    {
        return $this->right;
    }

    /**
     * Set the right side Edge of this relationship.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Edges\Relation $right
     * @return void
     */
    public function setRight($right)
    {
        $this->right = $right;
    }

    /**
     * Get the hyper model of the relationship.
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Model
     */
    public function hyper()
    {
        return $this->getRelated();
    }

    /**
     * Save the relationship to the database.
     *
     * @return boolean
     */
    public function save()
    {
        $savedLeft  = $this->left->save();
        $savedRight = $this->right->save();

        return $savedLeft && $savedRight;
    }

    /**
     * Remove the relationship from the database.
     *
     * @return  boolean
     */
    public function delete()
    {
        if ($this->exists())
        {
            $deletedLeft = $this->left->delete();
            $deletedRight = $this->right->delete();

            return $deletedLeft && $deletedRight;
        }

        return false;
    }

    /**
     * Determine whether this relation exists.
     *
     * @return boolean
     */
    public function exists()
    {
        return $this->left->exists() && $this->right->exists();
    }

}
