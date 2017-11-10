<?php namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class MorphMany extends BelongsToMany {

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
            $parentNode = $this->query->getQuery()->modelAsNode($this->parent->getTable());
            // Tell the query that we only need the related model returned.
            $this->query->select($this->relation);
            // Set the parent node's placeholder as the RETURN key.
            $this->query->getQuery()->from = array($parentNode);
            // Build the MATCH ()-[]->() Cypher clause.
            $this->query->matchIn($this->parent, $this->related, $this->relation, $this->foreignKey, $this->localKey, $this->parent->{$this->localKey});
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
        $this->query->addManyMutation($this->relation, $this->related, 'many');
        $this->query->addManyMutation($parentNode, $this->parent, 'many');

        // Set the parent node's placeholder as the RETURN key.
        $this->query->getQuery()->from = array($parentNode);
        // Build the MATCH ()-[]->() Cypher clause.
        $this->query->matchIn($this->parent, $this->related, $this->relation, $this->foreignKey, $this->localKey, $this->parent->{$this->localKey});
        // Add WHERE clause over the parent node's matching keys [values...].
        $this->query->whereIn($this->localKey, $this->getKeys($models));
    }

    /**
     * Get an instance of the Edge[In|Out] relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array         $attributes
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In|Out]
     */
    public function getEdge(EloquentModel $model = null, $attributes = array())
    {
        $model = ( ! is_null($model)) ? $model : $this->related;

        return new EdgeOut($this->query, $this->parent, $model, $this->type, $attributes);
    }
}
