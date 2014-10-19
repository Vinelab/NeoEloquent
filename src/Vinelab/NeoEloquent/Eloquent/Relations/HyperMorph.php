<?php namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class HyperMorph extends BelongsToMany {

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
     * The edge direction of this relatioship.
     *
     * @var string
     */
    protected $edgeDirection = 'out';

    /**
     * Create a new HyperMorph relationship.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Builder $query
     * @param Vinelab\NeoEloquent\Eloquent\Model   $parent
     * @param Vinelab\NeoEloquent\Eloquent\Model   $morph
     * @param string  $type
     * @param string  $morphType
     * @param string  $key
     * @param string  $relation
     */
    public function __construct(Builder $query, Model $parent, $morph, $type, $morphType, $key, $relation)
    {
        $this->morph = $morph;
        $this->morphType = $morphType;

        parent::__construct($query, $parent, $type, $key, $relation);
    }

        /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints)
        {
            /**
             * For has one relationships we need to actually query on the primary key
             * of the parent model matching on the OUTGOING relationship by name.
             *
             * We are trying to achieve a Cypher that goes something like:
             *
             * MATCH (user:`User`), (user)-[:PHONE]->(phone:`Phone`)
             * WHERE id(user) = 86234
             * RETURN phone;
             *
             * (user:`User`) represents a matching statement where
             * 'user' is the parent Node's placeholder and '`User`' is the parentLabel.
             * All node placeholders must be lowercased letters and will be used
             * throught the query to represent the actual Node.
             *
             * Resulting from:
             * class User extends NeoEloquent {
             *
             *     public function phone()
             *     {
             *          return $this->hasOne('Phone', 'PHONE');
             *     }
             * }
            */

            // Get the parent node's placeholder.
            $parentNode = $this->getParentNode();
            // Tell the query that we only need the related model returned.
            $this->query->select($this->relation);
            // Set the parent node's placeholder as the RETURN key.
            $this->query->getQuery()->from = array($parentNode);
            // Build the MATCH ()-[]->() Cypher clause.
            $this->query->matchOut($this->parent, $this->related, $this->relation, $this->foreignKey, $this->localKey, $this->parent->{$this->localKey});
            // Add WHERE clause over the parent node's matching key = value.
            $this->query->where($this->localKey, '=', $this->parent->{$this->localKey});
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        /**
         * We'll grab the primary key name of the related models since it could be set to
         * a non-standard name and not "id". We will then construct the constraint for
         * our eagerly loading query so it returns the proper models from execution.
         */

        // Grab the parent node placeholder
        $parentNode = $this->query->getQuery()->modelAsNode($this->parent->getTable());

        // Tell the builder to select both models of the relationship
        $this->query->select($this->relation, $parentNode);

        // Setup for their mutation so they don't breed weird stuff like... humans ?!
        $this->query->addManyMutation($this->relation, $this->related);
        $this->query->addManyMutation($parentNode, $this->parent);

        // Set the parent node's placeholder as the RETURN key.
        $this->query->getQuery()->from = array($parentNode);
        // Build the MATCH ()-[]->() Cypher clause.
        $this->query->matchOut($this->parent, $this->related, $this->relation, $this->foreignKey, $this->localKey, $this->parent->{$this->localKey});
        // Add WHERE clause over the parent node's matching keys [values...].
        $this->query->whereIn($this->localKey, $this->getKeys($models));
    }

    public function edge(Model $model = null)
    {
        return $this->finder->hyperFirst($this->parent, $model, $this->morph, $this->type, $this->morphType);
    }

    public function getEdge(EloquentModel $model = null, $properties = array())
    {
        $model = ( ! is_null($model)) ? $model : $this->related;

        return new HyperEdge($this->query, $this->parent, $this->type, $model, $this->morphType, $this->morph, $properties);
    }

}
