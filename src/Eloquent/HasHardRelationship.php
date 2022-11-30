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
    protected bool $enableHardRelationships = false;
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
        $this->enableHardRelationships();

        $this->relationshipName = $name;

        return $this;
    }

    public function enableHardRelationships(): self
    {
        $this->enableHardRelationships = true;

        return $this;
    }

    public function disableHardRelationships(): self
    {
        $this->enableHardRelationships = false;

        return $this;
    }

    public function hasHardRelationshipsEnabled(): bool
    {
        return $this->enableHardRelationships;
    }
}