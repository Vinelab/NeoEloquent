<?php namespace Vinelab\NeoEloquent\Eloquent\Relations\Hybrid;

use Illuminate\Database\Eloquent\Collection;
use Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Vinelab\NeoEloquent\Eloquent\Relations\OneRelation;

class SqlBelongsToGraph extends OneRelation
{

    /**
     * The edge direction for this relationship.
     *
     * @var string
     */
    protected $edgeDirection = 'in';

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $this->getQuery()->where($this->ownerKey, '=', $this->child->{$this->foreignKey});
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->getQuery()->whereIn($this->ownerKey, $this->getKeys($models, $this->foreignKey));
    }


    public function match(array $models, Collection $results, $relation)
    {
        return \Illuminate\Database\Eloquent\Relations\BelongsTo::match($models, $results, $relation);
    }

    /**
     * Get an instance of the EdgeIn relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array $attributes
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn
     */
    public function getEdge(EloquentModel $model = null, $attributes = array())
    {
        $model = (!is_null($model)) ? $model : $this->parent->{$this->relation};

        // Indicate a unique relation since this only involves one other model.
        $unique = true;
        return new EdgeIn($this->getQuery(), $this->parent, $model, $this->foreignKey, $attributes, $unique);
    }
}
