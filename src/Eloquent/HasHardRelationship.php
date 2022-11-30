<?php

namespace Vinelab\NeoEloquent\Eloquent;

use Illuminate\Database\Eloquent\Relations\Relation;

use Illuminate\Support\Str;

use function class_basename;

/**
 * @mixin Relation
 */
trait HasHardRelationship
{
    protected ?string $relationshipName = null;

    public function getRelationshipName(): string
    {
        return $this->relationshipName ?? $this->getDefaultRelationshipName();
    }

    protected function getDefaultRelationshipName(): string
    {
        return 'HAS_'.Str::snake(class_basename($this->getRelated()));
    }

    public function withRelationshipName(string $name): self
    {
        $this->relationshipName = $name;

        return $this;
    }
}