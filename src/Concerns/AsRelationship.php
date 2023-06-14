<?php

namespace Vinelab\NeoEloquent\Concerns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Vinelab\NeoEloquent\Query\Concerns\ControlsDirectionWithEncoding;

/**
 * @mixin Model
 */
trait AsRelationship
{
    use ControlsDirectionWithEncoding;

    protected string $leftKeyPropertyName;

    protected string $leftLabel;

    protected mixed $leftKeyValue;

    protected string $rightKeyPropertyName;

    protected string $rightLabel;

    protected mixed $rightKeyValue;

    /**
     * Create a new relationship model instance.
     */
    public static function fromAttributes(
        array $attributes,
        string $tableWithRelationshipEncoded,
        string $leftKeyPropertyName,
        string $leftLabel,
        string $rightKeyPropertyName,
        string $rightLabel,
        bool $exists = false
    ): static {
        $instance = new static;

        $instance->timestamps = $instance->hasTimestampAttributes($attributes);

        $instance->setTable($tableWithRelationshipEncoded)
            ->forceFill($attributes)
            ->syncOriginal();

        $instance->leftKeyPropertyName = $leftKeyPropertyName;
        $instance->leftLabel = $leftLabel;
        $instance->rightKeyPropertyName = $rightKeyPropertyName;
        $instance->rightLabel = $rightLabel;

        $instance->exists = $exists;

        return $instance;
    }

    /**
     * Create a new relationship model from raw values returned from a query.
     */
    public static function fromRawAttributes(
        array $attributes,
        string $tableWithRelationshipEncoded,
        string $leftKeyPropertyName,
        string $leftLabel,
        string $rightKeyPropertyName,
        string $rightLabel,
        bool $exists = false
    ): static {
        $instance = static::fromAttributes(
            [],
            $tableWithRelationshipEncoded,
            $leftKeyPropertyName,
            $leftLabel,
            $rightKeyPropertyName,
            $rightLabel,
            $exists
        );

        $instance->timestamps = $instance->hasTimestampAttributes($attributes);

        $instance->setRawAttributes(array_merge($instance->getRawOriginal(), $attributes), $exists);

        return $instance;
    }

    public function getLeftKeyValue(): mixed
    {
        return $this->leftKeyValue;
    }

    public function getLeftKeyPropertyName(): string
    {
        return $this->leftKeyPropertyName;
    }

    public function setLeftKeyPropertyName(string $leftKeyPropertyName): void
    {
        $this->leftKeyPropertyName = $leftKeyPropertyName;
    }

    public function getLeftLabel(): string
    {
        return $this->leftLabel;
    }

    public function setLeftLabel(string $leftLabel): void
    {
        $this->leftLabel = $leftLabel;
    }

    public function getRightKeyPropertyName(): string
    {
        return $this->rightKeyPropertyName;
    }

    public function setRightKeyPropertyName(string $rightKeyPropertyName): void
    {
        $this->rightKeyPropertyName = $rightKeyPropertyName;
    }

    public function getRightLabel(): string
    {
        return $this->rightLabel;
    }

    public function setRightLabel(string $rightLabel): void
    {
        $this->rightLabel = $rightLabel;
    }

    public function getRightKeyValue(): mixed
    {
        return $this->rightKeyValue;
    }

    public function setRightKeyValue(mixed $rightKeyValue): void
    {
        $this->rightKeyValue = $rightKeyValue;
    }

    public function setLeftKeyValue(mixed $leftKeyValue): void
    {
        $this->leftKeyValue = $leftKeyValue;
    }

    /**
     * Set the keys for a select query.
     *
     * @param  EloquentBuilder  $query
     */
    protected function setKeysForSelectQuery($query): EloquentBuilder
    {
        if (isset($this->attributes[$this->getKeyName()])) {
            return parent::setKeysForSelectQuery($query);
        }

        $query->where($this->rightKeyPropertyName, $this->getOriginal(
            $this->foreignKey, $this->getAttribute($this->foreignKey)
        ));

        return $query->where($this->leftKeyPropertyName, $this->getOriginal(
            $this->leftKeyPropertyName, $this->getAttribute($this->leftKeyPropertyName)
        ));
    }

    /**
     * Set the keys for a save update query.
     *
     * @param  EloquentBuilder  $query
     */
    protected function setKeysForSaveQuery($query): EloquentBuilder
    {
        return $this->setKeysForSelectQuery($query);
    }

    /**
     * Delete the relationship model record from the database.
     */
    public function delete(): int
    {
        if (isset($this->attributes[$this->getKeyName()])) {
            return (int) parent::delete();
        }

        if ($this->fireModelEvent('deleting') === false) {
            return 0;
        }

        $this->touchOwners();

        return tap($this->getDeleteQuery()->delete(), function () {
            $this->exists = false;

            $this->fireModelEvent('deleted', false);
        });
    }

    /**
     * Get the query builder for a delete operation on the relationship.
     *
     * @return EloquentBuilder
     */
    protected function getDeleteQuery()
    {
        return $this->newQueryWithoutRelationships()->where([
            $this->foreignKey => $this->getOriginal($this->foreignKey, $this->getAttribute($this->foreignKey)),
            $this->leftKeyPropertyName => $this->getOriginal($this->leftKeyPropertyName, $this->getAttribute($this->leftKeyPropertyName)),
        ]);
    }

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        if (! isset($this->table)) {
            $table = '<'.Str::upper(Str::snake(Str::singular(class_basename($this)))).'>';

            $this->setTable($table);
        }

        return $this->table;
    }

    /**
     * @return $this
     */
    public function setRelationData(
        string $leftKeyPropertyName,
        string $leftLabel,
        string $rightKeyPropertyName,
        string $rightLabel
    ): static {
        $this->leftKeyPropertyName = $leftKeyPropertyName;
        $this->leftLabel = $leftLabel;
        $this->rightKeyPropertyName = $rightKeyPropertyName;
        $this->rightLabel = $rightLabel;

        return $this;
    }

    /**
     * Determine if the pivot model or given attributes has timestamp attributes.
     *
     * @param  array|null  $attributes
     */
    public function hasTimestampAttributes(array|null $attributes = null): bool
    {
        return array_key_exists($this->getCreatedAtColumn(), $attributes ?? $this->attributes);
    }

    /**
     * Get the queueable identity for the entity.
     */
    public function getQueueableId(): mixed
    {
        if (isset($this->attributes[$this->getKeyName()])) {
            return $this->getKey();
        }

        return sprintf(
            '%s:%s:%s:%s',
            $this->foreignKey, $this->getAttribute($this->foreignKey),
            $this->leftKeyPropertyName, $this->getAttribute($this->leftKeyPropertyName)
        );
    }

    /**
     * Get a new query to restore one or more models by their queueable IDs.
     *
     * @param  int[]|string[]|string  $ids
     */
    public function newQueryForRestoration($ids): EloquentBuilder
    {
        if (is_array($ids)) {
            return $this->newQueryForCollectionRestoration($ids);
        }

        if (! str_contains($ids, ':')) {
            return parent::newQueryForRestoration($ids);
        }

        [$leftLabel, $leftKeyName, $leftKeyValue, $rightLabel, $rightKeyName, $rightKeyValue] = explode(':', $ids);

        return $this->newQueryWithoutScopes()
            ->leftJoin($leftLabel)
            ->whereRightNode($rightLabel)
            ->where($segments[0], $segments[1])
            ->where($segments[2], $segments[3]);
    }

    /**
     * Get a new query to restore multiple models by their queueable IDs.
     *
     * @param  int[]|string[]  $ids
     */
    protected function newQueryForCollectionRestoration(array $ids): EloquentBuilder
    {
        $ids = array_values($ids);

        if (! str_contains($ids[0], ':')) {
            return parent::newQueryForRestoration($ids);
        }

        $query = $this->newQueryWithoutScopes();

        foreach ($ids as $id) {
            $segments = explode(':', $id);

            $query->orWhere(function ($query) use ($segments) {
                return $query->where($segments[0], $segments[1])
                    ->where($segments[2], $segments[3]);
            });
        }

        return $query;
    }

    protected function getEncodedData(): string
    {
        return $this->getTable();
    }

    protected function setEncodedData(string $data): void
    {
        $this->setTable($data);
    }
}
