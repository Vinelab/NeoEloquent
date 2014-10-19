<?php namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Vinelab\NeoEloquent\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class HasOne extends HasOneOrMany {

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array   $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model)
        {
            // In the case of fetching nested relations, we will get an array
            // with the first key being the model we need, and the other being
            // the related model so we'll just take the first model out of the array.
            if (is_array($model)) $model = reset($model);

            $model->setRelation($relation, null);
        }

        return $models;
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
            $parentNode = $this->query->getQuery()->modelAsNode($this->parent->getTable());
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
        $this->query->addMutation($this->relation, $this->related);
        $this->query->addMutation($parentNode, $this->parent);

        // Set the parent node's placeholder as the RETURN key.
        $this->query->getQuery()->from = array($parentNode);
        // Build the MATCH ()-[]->() Cypher clause.
        $this->query->matchOut($this->parent, $this->related, $this->relation, $this->foreignKey, $this->localKey, $this->parent->{$this->localKey});
        // Add WHERE clause over the parent node's matching keys [values...].
        $this->query->whereIn($this->localKey, $this->getKeys($models));
    }

    /**
     * Get an instance of the EdgeIn relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array         $attributes
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut
     */
    public function getEdge(EloquentModel $model = null, $attributes = array())
    {
        $model = ( ! is_null($model)) ? $model : $this->parent->{$this->relation};

       // Indicate a unique relation since this only involves one other model.
        $unique = true;
        return new EdgeOut($this->query, $this->parent, $model, $this->foreignKey, $attributes, $unique);
    }

    /**
     * Get the edge between the parent model and the given model or
     * the related model determined by the relation function name.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In,Out, etc.]
     */
    public function edge(Model $model = null)
    {
        return $this->getEdge($model)->current();
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        return $this->matchOne($models, $results, $relation);
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->query->first();
    }
}
