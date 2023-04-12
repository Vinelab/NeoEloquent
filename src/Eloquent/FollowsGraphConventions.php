<?php

namespace Vinelab\NeoEloquent\Eloquent;

use function class_basename;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use function implode;
use function sort;
use function strtolower;

/**
 * @mixin HasRelationships
 * @mixin Model
 */
trait FollowsGraphConventions
{
    public static bool $snakeAttributes = false;

    public function getForeignKey(): string
    {
        return Str::studly(class_basename($this)).$this->getKeyName();
    }

    /**
     * Get the joining table name for a many-to-many relation.
     *
     * @param  string  $related
     * @param  Model|null  $instance
     */
    public function joiningTable($related, $instance = null): string
    {
        // The joining table name, by convention, is simply the snake cased models
        // sorted alphabetically and concatenated with an underscore, so we can
        // just sort the models and join them together to get the table name.
        $segments = [
            $instance ? $instance->joiningTableSegment() : Str::studly(class_basename($related)),
            $this->joiningTableSegment(),
        ];

        // Now that we have the model names in an array we can just sort them and
        // use the implode function to join them together with an underscores,
        // which is typically used by convention within the database system.
        sort($segments);

        return strtolower(implode('_', $segments));
    }

    /**
     * Get this model's half of the intermediate table name for belongsToMany relationships.
     */
    public function joiningTableSegment(): string
    {
        return Str::studly(class_basename($this));
    }

    /**
     * Get the polymorphic relationship columns.
     *
     * @param  string  $name
     * @param  string  $type
     * @param  string  $id
     */
    protected function getMorphs($name, $type, $id): array
    {
        return [$type ?: $name.'Type', $id ?: $name.'Id'];
    }

    public function getTable(): string
    {
        return $this->table ?? Str::studly(class_basename($this));
    }
}
