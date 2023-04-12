<?php

namespace Vinelab\NeoEloquent\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Relationships\BelongsTo;
use Vinelab\NeoEloquent\Eloquent\Relationships\BelongsToMany;
use Vinelab\NeoEloquent\Eloquent\Relationships\HasMany;
use Vinelab\NeoEloquent\Eloquent\Relationships\HasManyThrough;
use Vinelab\NeoEloquent\Eloquent\Relationships\HasOne;
use Vinelab\NeoEloquent\Eloquent\Relationships\HasOneThrough;
use Vinelab\NeoEloquent\Eloquent\Relationships\MorphMany;
use Vinelab\NeoEloquent\Eloquent\Relationships\MorphOne;
use Vinelab\NeoEloquent\Eloquent\Relationships\MorphTo;
use Vinelab\NeoEloquent\Eloquent\Relationships\MorphToMany;

/**
 * @mixin Model
 *
 * @method BelongsTo belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null)
 * @method BelongsToMany belongsToMany($related, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null)
 * @method HasMany hasMany($related, $foreignKey = null, $localKey = null)
 * @method HasManyThrough hasManyThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null, $secondLocalKey = null)
 * @method HasOne hasOne($related, $foreignKey = null, $localKey = null)
 * @method HasOneThrough hasOneThrough($related, $through, $firstKey = null, $secondKey = null, $localKey = null, $secondLocalKey = null)
 * @method MorphMany morphMany($related, $name, $type = null, $id = null, $localKey = null)
 * @method MorphOne morphOne($related, $name, $type = null, $id = null, $localKey = null)
 * @method MorphTo morphTo($name = null, $type = null, $id = null, $ownerKey = null)
 * @method MorphToMany morphToMany($related, $name, $table = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $inverse = false)
 */
trait IsGraphAware
{
    use HasRelationships;

    public bool $incrementing = false;

    public function setLabel(string $label): self
    {
        return $this->setTable($label);
    }

    public function getLabel(): string
    {
        return $this->getTable();
    }

    public function nodeLabel(): string
    {
        return $this->getTable();
    }

    protected function newHasOne(Builder $query, Model $parent, $foreignKey, $localKey): HasOne
    {
        return new HasOne($query, $parent, $foreignKey, $localKey);
    }

    protected function newHasOneThrough(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey): HasOneThrough
    {
        return new HasOneThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
    }

    protected function newMorphOne(Builder $query, Model $parent, $type, $id, $localKey): MorphOne
    {
        return new MorphOne($query, $parent, $type, $id, $localKey);
    }

    protected function newBelongsTo(Builder $query, Model $child, $foreignKey, $ownerKey, $relation): BelongsTo
    {
        return new BelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
    }

    protected function newMorphTo(Builder $query, Model $parent, $foreignKey, $ownerKey, $type, $relation): MorphTo
    {
        return new MorphTo($query, $parent, $foreignKey, $ownerKey, $type, $relation);
    }

    protected function newHasMany(Builder $query, Model $parent, $foreignKey, $localKey): HasMany
    {
        return new HasMany($query, $parent, $foreignKey, $localKey);
    }

    protected function newHasManyThrough(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey): HasManyThrough
    {
        return new HasManyThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
    }

    protected function newMorphMany(Builder $query, Model $parent, $type, $id, $localKey): MorphMany
    {
        return new MorphMany($query, $parent, $type, $id, $localKey);
    }

    protected function newBelongsToMany(Builder $query, Model $parent, $table, $foreignPivotKey, $relatedPivotKey,
        $parentKey, $relatedKey, $relationName = null): BelongsToMany
    {
        return new BelongsToMany($query, $parent, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relationName);
    }

    protected function newMorphToMany(Builder $query, Model $parent, $name, $table, $foreignPivotKey,
        $relatedPivotKey, $parentKey, $relatedKey,
        $relationName = null, $inverse = false): MorphToMany
    {
        return new MorphToMany($query, $parent, $name, $table, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey,
            $relationName, $inverse);
    }
}
