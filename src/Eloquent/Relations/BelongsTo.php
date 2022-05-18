<?php

namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BelongsTo extends \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    public function __construct(Builder $query, Model $child, string $relationName)
    {
        parent::__construct($query, $child, '', '', $relationName);
    }

    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints(): void
    {
        if (static::$constraints) {
            $table = $this->related->getTable();

            $this->query->whereRelationship($this->relationName.'>', $table);
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     */
    public function addEagerConstraints(array $models): void
    {
        $table = $this->related->getTable();

        $this->whereRelation('<'.$this->relationName, $table);

        parent::addEagerConstraints($models);
    }

    public function associate($model): Model
    {
        if ($model instanceof Model) {
            $this->related->setRelation($this->relationName.'>', $model);
        } else {
            $this->related->unsetRelation($this->relationName.'>');
        }

        return $this->child;
    }


    /**=
     * @return Model
     */
    public function dissociate(): Model
    {
        return $this->child->setRelation($this->relationName.'>', null);
    }
}
