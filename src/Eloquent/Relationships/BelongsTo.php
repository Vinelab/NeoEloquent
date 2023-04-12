<?php

namespace Vinelab\NeoEloquent\Eloquent\Relationships;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\HasHardRelationship;

class BelongsTo extends \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    use HasHardRelationship;

    public function __construct(Builder $query, Model $child, $foreignKey, $ownerKey, $relationName)
    {
        parent::__construct($query, $child, $foreignKey, $ownerKey, $relationName);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            // For belongs to relationships, which are essentially the inverse of has one
            // or has many relationships, we need to actually query on the primary key
            // of the related models matching on the foreign key that's on a parent.
            $table = $this->related->getTable();

            if (! $this->hasHardRelationshipsEnabled()) {
                $this->query->where($table.'.'.$this->ownerKey, '=', $this->child->{$this->foreignKey});
            }
        }
    }
}
