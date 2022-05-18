<?php

namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Illuminate\Database\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Model;

abstract class HasOneOrMany extends \Illuminate\Database\Eloquent\Relations\HasOneOrMany
{
    private string $relation;

    public function __construct(Builder $query, Model $parent, string $relation)
    {
        $this->relation = $relation;
        parent::__construct($query, $parent, '', '');
    }

    public function addConstraints(): void
    {
        if (static::$constraints) {
            // We need to swap around the corresponding nodes, as the processor will otherwise load the wrong node into the models
            $table = $this->parent->getTable();

            $oldFrom = $this->query->from;
            $this->query->from($table)
                ->crossJoin($oldFrom);

            $this->query->whereRelationship('<'.$this->relation, $oldFrom);
        }
    }

    public function addEagerConstraints(array $models): void
    {
        $table = $this->related->getTable();

        $this->query->whereRelationship($this->relation . '>', $table);
    }

    public function save(\Illuminate\Database\Eloquent\Model $model)
    {
        $model->setRelation('<'.$this->relation, $this->related);

        return $model->save() ? $model : false;
    }
}
