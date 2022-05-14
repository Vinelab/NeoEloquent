<?php

namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Model;

abstract class Relation
{
    /**
     * The Eloquent query builder instance.
     */
    protected Builder $query;

    /**
     * The parent model instance.
     */
    protected $parent;

    /**
     * The related model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $related;

    /**
     * Indicates if the relation is adding constraints.
     *
     * @var bool
     */
    protected static $constraints = true;

    /**
     * An array to map class names to their morph names in database.
     *
     * @var array
     */
    protected static $morphMap = [];

    /**
     * Create a new relation instance.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Model   $parent
     */
    public function __construct(Builder $query, Model $parent)
    {
        $this->query = $query;
        $this->parent = $parent;
        $this->related = $query->getModel();

        $this->addConstraints();
    }

    /**
     * Set the base constraints on the relation query.
     */
    abstract public function addConstraints();

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     */
    abstract public function addEagerConstraints(array $models);

    /**
     * Initialize the relation on a set of models.
     *
     * @param array  $models
     * @param string $relation
     *
     * @return array
     */
    abstract public function initRelation(array $models, $relation);

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param array                                    $models
     * @param \Vinelab\NeoEloquent\Eloquent\Collection $results
     * @param string                                   $relation
     *
     * @return array
     */
    abstract public function match(array $models, Collection $results, $relation);

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    abstract public function getResults();

    /**
     * Get the relationship for eager loading.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getEager()
    {
        return $this->get();
    }

    /**
     * Touch all of the related models for the relationship.
     */
    public function touch()
    {
        $column = $this->getRelated()->getUpdatedAtColumn();

        $this->rawUpdate([$column => $this->getRelated()->freshTimestampString()]);
    }

    /**
     * Run a raw update against the base query.
     *
     * @param array $attributes
     *
     * @return int
     */
    public function rawUpdate(array $attributes = [])
    {
        return $this->query->update($attributes);
    }

    /**
     * Add the constraints for a relationship count query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param \Illuminate\Database\Eloquent\Builder $parent
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationCountQuery(Builder $query, Builder $parent)
    {
        $query->select(new Expression('count(*)'));

        $key = $this->wrap($this->getQualifiedParentKeyName());

        return $query->where($this->getHasCompareKey(), '=', new Expression($key));
    }

    /**
     * Run a callback with constraints disabled on the relation.
     *
     * @param \Closure $callback
     *
     * @return mixed
     */
    public static function noConstraints(Closure $callback)
    {
        $previous = static::$constraints;

        static::$constraints = false;

        // When resetting the relation where clause, we want to shift the first element
        // off of the bindings, leaving only the constraints that the developers put
        // as "extra" on the relationships, and not original relation constraints.
        $results = call_user_func($callback);

        static::$constraints = $previous;

        return $results;
    }

    /**
     * When matching eager loaded data, we need to determine
     * which identifier should be used to set the related models to.
     * This is done by iterating the given models and checking for
     * the matching class between the result and this relation's
     * parent model. When there's a match, the identifier at which
     * the match occurred is returned.
     *
     * @param  array  $models
     *
     * @return string
     */
    protected function determineValueIdentifier(array $models)
    {
        foreach ($models as $resultIdentifier => $model) {
            if (get_class($this->parent) === get_class($model)) {
                return $resultIdentifier;
            }
        }
    }

    /**
     * Get all of the primary keys for an array of models.
     *
     * @param array  $models
     * @param string $key
     *
     * @return array
     */
    protected function getKeys(array $models, $key = null)
    {
        return array_unique(array_values(array_map(function ($value) use ($key) {
            return $key ? $value->getAttribute($key) : $value->getKey();

        }, $models)));
    }

    /**
     * Get the underlying query for the relation.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Get the base query builder driving the Eloquent builder.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function getBaseQuery()
    {
        return $this->query->getQuery();
    }

    /**
     * Get the parent model of the relation.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Get the fully qualified parent key name.
     *
     * @return string
     */
    public function getQualifiedParentKeyName()
    {
        return $this->parent->getQualifiedKeyName();
    }

    /**
     * Get the related model of the relation.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * Get the name of the "created at" column.
     *
     * @return string
     */
    public function createdAt()
    {
        return $this->parent->getCreatedAtColumn();
    }

    /**
     * Get the name of the "updated at" column.
     *
     * @return string
     */
    public function updatedAt()
    {
        return $this->parent->getUpdatedAtColumn();
    }

    /**
     * Get the name of the related model's "updated at" column.
     *
     * @return string
     */
    public function relatedUpdatedAt()
    {
        return $this->related->getUpdatedAtColumn();
    }

    /**
     * Wrap the given value with the parent query's grammar.
     *
     * @param string $value
     *
     * @return string
     */
    public function wrap($value)
    {
        return $this->parent->newQueryWithoutScopes()->getQuery()->getGrammar()->wrap($value);
    }

    /**
     * Set the morph map for polymorphic relations.
     *
     * @param array|null $map
     * @param bool       $merge
     *
     * @return array
     */
    public static function morphMap(array $map = null, $merge = true)
    {
        if (is_array($map)) {
            static::$morphMap = $merge ? array_merge(static::$morphMap, $map) : $map;
        }

        return static::$morphMap;
    }

    /**
     * Handle dynamic method calls to the relationship.
     *
     * @param string $method
     * @param array  $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $result = call_user_func_array([$this->query, $method], $parameters);

        if ($result === $this->query) {
            return $this;
        }

        return $result;
    }
}
