<?php namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\Eloquent\Collection;
use Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo as IlluminateBelongsTo;

class BelongsTo extends IlluminateBelongsTo {

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
             * For belongs to relationships, which are essentially the inverse of has one
             * or has many relationships, we need to actually query on the primary key
             * of the parent model matching on the INCOMING relationship by name.
             *
             * We are trying to achieve a Cypher that goes something like:
             *
             * MATCH (phone:`Phone`), (phone)<-[:PHONE]-(owner:`User`)
             * WHERE id(phone) = 1006
             * RETURN owner;
             *
             * (phone:`Phone`) represents a matching statement where
             * 'phone' is the parent Node's placeholder and '`Phone`' is the parentLabel.
             * All node placeholders must be lowercased letters and will be used
             * throught the query to represent the actual Node.
             *
             * Resulting from:
             * class Phone extends NeoEloquent {
             *
             *     public function owner()
             *     {
             *          return $this->belongsTo('User', 'PHONE');
             *     }
             * }
            */

            // Get the parent node's placeholder.
            $parentNode = $this->query->getQuery()->modelAsNode($this->parent->getTable());
            // Tell the query that we only need the related model returned.
            $this->query->select($this->relation);
            // Set the parent node's placeholder as the RETURN key.
            $this->query->getQuery()->from = array($parentNode);
            // Build the MATCH ()<-[]-() Cypher clause.
            $this->query->matchIn($this->parent, $this->related, $this->relation, $this->foreignKey, $this->otherKey, $this->parent->{$this->otherKey});
            // Add WHERE clause over the parent node's matching key = value.
            $this->query->where($this->otherKey, '=', $this->parent->{$this->otherKey});
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
        // Build the MATCH ()<-[]-() Cypher clause.
        $this->query->matchIn($this->parent, $this->related, $this->relation, $this->foreignKey, $this->otherKey, $this->parent->{$this->otherKey});
        // Add WHERE clause over the parent node's matching keys [values...].
        $this->query->whereIn($this->otherKey, $this->getEagerModelKeys($models));
    }

    /**
     * Gather the keys from an array of related models.
     *
     * @param  array  $models
     * @return array
     */
    protected function getEagerModelKeys(array $models)
    {
        $keys = array();

        /**
         * First we need to gather all of the keys from the parent models so we know what
         * to query for via the eager loading query. We will add them to an array then
         * execute a "where in" statement to gather up all of those related records.
         */
        foreach ($models as $model)
        {
            if ( ! is_null($value = $model->{$this->otherKey}))
            {
                $keys[] = $value;
            }
        }

        /**
         * If there are no keys that were not null we will just return an array with 0 in
         * it so the query doesn't fail, but will not return any results, which should
         * be what this developer is expecting in a case where this happens to them.
         */
        if (count($keys) == 0)
        {
            return array(0);
        }

        return array_values(array_unique($keys));
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
        // We will need the parent node placeholder so that we use it to extract related results.
        $parent = $this->query->getQuery()->modelAsNode($this->parent->getTable());

        /**
         * Looping into all the parents to match back onto their children using
         * the primary key to map them onto the correct instances, every single
         * result will be having both instances at each Collection item, held by their
         * node placeholder.
         */
        foreach ($models as $model)
        {
            $matched = $results->filter(function($result) use($parent, $relation, $model)
            {
                if ($result[$parent] instanceof Model)
                {
                    return $model->getKey() == $result[$parent]->getKey();
                }
            });

            // Now that we have the matched parents we know where to add the relations.
            // Sometimes we have more than a match so we gotta catch them all!
            foreach ($matched as $match)
            {
                $model->setRelation($relation, $match[$relation]);
            }
        }

        return $models;
    }

    /**
     * Associate the model instance to the given parent.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function associate(EloquentModel $model, $attributes = array())
    {
        /**
         * For associated models we will need to create a unique relationship
         * between the parent and the related model. In Cypher we can use the
         * MERGE clause to make sure that the relationship doesn't happen more than once.
         *
         * An example query would be like:
         *
         * MATCH (account:`Account`), (user:`User`)
         * WHERE id(account) = 10892 AND id(user) = 98522
         * MERGE (account)<-[rel:ACCOUNT]-(user)
         * RETURN rel;
         */

        // Set the relation on the model
        $this->parent->setRelation($this->relation, $model);

        /**
         * Due to the fact that relationships in Graph are entities themselves
         * we will need to treat them as such and in this case what we're looking for is
         * a relationship with an INCOMING direction towards the parent node, in other words
         * it is a relationship with an edge incoming towards the $parent model and we call it
         * an "EdgeIn" relationship.
         */
        return $this->getEdge($model, $attributes);
    }

    public function edge(EloquentModel $model = null)
    {
        return $this->getEdge($model)->current();
    }

    /**
     * Get an instance of the EdgeIn relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array         $attributes
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn
     */
    public function getEdge(EloquentModel $model = null, $attributes = array())
    {
        $model = ( ! is_null($model)) ? $model : $this->parent->{$this->relation};

        return new EdgeIn($this->query, $this->parent, $model, $this->foreignKey, $attributes, $unique = true);
    }
}
