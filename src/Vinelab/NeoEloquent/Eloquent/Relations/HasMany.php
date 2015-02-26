<?php namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class HasMany extends HasOneOrMany {

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->query->get();
    }

    /**
     * Get an instance of the Edge relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array         $attributes
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut
     */
    public function getEdge(EloquentModel $model = null, $attributes = array())
    {
        $model = ( ! is_null($model)) ? $model : $this->parent->{$this->relation};

        return new EdgeOut($this->query, $this->parent, $model, $this->type, $attributes);
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
        $this->query->matchOut($this->parent, $this->related, $this->relation, $this->foreignKey, $this->localKey, $this->parent->{$this->localKey});
        // Add WHERE clause over the parent node's matching keys [values...].
        $this->query->whereIn($this->localKey, $this->getKeys($models, $this->localKey));
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
        return $this->matchMany($models, $results, $relation);
    }

    /**
     * Get the fully qualified parent key name.
     *
     * @return string
     */
    public function getQualifiedParentKeyName()
    {
        return $this->relation;
    }
}
