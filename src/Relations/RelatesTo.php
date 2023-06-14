<?php

namespace Vinelab\NeoEloquent\Relations;

use Arr;
use function array_map;
use Closure;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Illuminate\Database\Eloquent\Relations\Concerns\InteractsWithDictionary;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Vinelab\NeoEloquent\Concerns\AsRelationship;
use Vinelab\NeoEloquent\Query\Builder;

/**
 * @mixin Builder
 * @mixin
 */
class RelatesTo extends Relation
{
    use SupportsDefaultModels, InteractsWithDictionary;

    protected array $relationshipColumns = [];

    protected array $relationWheres = [];

    protected array $relationWhereIns = [];

    protected array $relationWhereNulls = [];

    protected array $defaultRelationValues = [];

    public bool $withTimestamps = true;

    protected string|null $pivotCreatedAtName = null;

    protected string|null $pivotUpdatedAtName = null;

    protected string $using;

    public function __construct(
        \Illuminate\Database\Eloquent\Builder $queryFromLeft,
        Model $right,
        protected string $relationshipType,
        protected string|null $relationName = null,
        protected string $direction = '>'
    ) {
        parent::__construct($queryFromLeft, $right);
    }

    public function fromLeftToRight(): self
    {
        $this->direction = '>';

        return $this;
    }

    public function fromRightToLeft(): self
    {
        $this->direction = '<';

        return $this;
    }

    public function fromAnyDirection(): self
    {
        $this->direction = '';

        return $this;
    }

    public function addConstraints(): void
    {
        $this->whereRelationship($this->relationshipType, $this->getRightLabel(), $this->direction);
    }

    public function addEagerConstraints(array $models): void
    {
        $this->whereIn($this->getQualifiedRightKeyName(), $this->parseIds($models));
    }

    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }

    /**
     * Toggles a model (or models) from the parent.
     *
     * Each existing model is detached, and non existing ones are attached.
     *
     * @return array{attached: list<string>, detached: list<string>}
     */
    public function toggle(mixed $ids, bool $touch = true): array
    {
        $changes = [];

        $toToggle = $this->formatRecordsList($this->parseIds($ids));
        $connectedRhs = $this->fetchKeysOfConnectedNodesAtRightHandSide();

        $changes['detached'] = $this->detachAndReturnTheirIds($connectedRhs, $toToggle);
        $changes['attached'] = $this->attachAndReturnTheirIds($connectedRhs, $toToggle);

        $this->touchIfNeeded($touch, $changes);

        return $changes;
    }

    /**
     * Cast the given key to convert to primary key type.
     */
    protected function castKey(mixed $key): mixed
    {
        return $this->getTypeSwapValue($this->getRightModel()->getKeyType(), $key);
    }

    /**
     * Converts a given value to a given type value.
     */
    protected function getTypeSwapValue(string $type, mixed $value): mixed
    {
        return match (strtolower($type)) {
            'int', 'integer' => (int) $value,
            'real', 'float', 'double' => (float) $value,
            'string' => (string) $value,
            default => $value,
        };
    }

    /**
     * Sync the intermediate tables with a list of IDs without detaching.
     */
    public function syncWithoutDetaching(Model|array|BaseCollection $ids): array
    {
        return $this->sync($ids, false);
    }

    /**
     * Sync the intermediate tables with a list of IDs or collection of models.
     *
     * @return array{attached: array, detached: array, updated: array}
     */
    public function sync(BaseCollection|Model|array $ids, bool $detaching = true): array
    {
        $changes = [];

        $current = $this->fetchKeysOfConnectedNodesAtRightHandSide();
        $toSync = $this->formatRecordsList($this->parseIds($ids));

        if ($detaching) {
            $changes['detached'] = $this->detachAndReturnTheirIds($current, $toSync);
        }

        $changes = array_merge($changes, $this->attachNew($toSync, $current, false));

        if (count(Arr::flatten($changes)) > 0) {
            $this->touchIfTouching();
        }

        return $changes;
    }

    /**
     * Sync the intermediate tables with a list of IDs or collection of models with the given pivot values.
     *
     * @param  BaseCollection|Model|array  $ids
     */
    public function syncWithPivotValues(mixed $ids, array $values, bool $detaching = true): array
    {
        return $this->sync(collect($this->parseIds($ids))->mapWithKeys(function ($id) use ($values) {
            return [$id => $values];
        }), $detaching);
    }

    /**
     * Format the sync / toggle record list so that it is keyed by ID.
     */
    protected function formatRecordsList(array $records): array
    {
        return collect($records)->mapWithKeys(function ($attributes, $id) {
            if (! is_array($attributes)) {
                return [$attributes, []];
            }

            return [$id => $attributes];
        })->all();
    }

    /**
     * Attach all of the records that aren't in the given current records.
     *
     * @return array{attached: array, updated: array}
     */
    protected function attachNew(array $records, array $current, bool $touch = true): array
    {
        $changes = ['attached' => [], 'updated' => []];

        foreach ($records as $id => $attributes) {
            if (! in_array($id, $current)) {
                $this->attach($id, $attributes, $touch);

                $changes['attached'][] = $this->castKey($id);
            } elseif (count($attributes) > 0 &&
                $this->updateExistingRelationship($id, $attributes, $touch)) {
                $changes['updated'][] = $this->castKey($id);
            }
        }

        return $changes;
    }

    /**
     * Update an existing pivot record on the table.
     */
    public function updateExistingRelationship(mixed $id, array $attributes, bool $touch = true): int
    {
        if ($this->using &&
            empty($this->relationWheres) &&
            empty($this->relationWhereIns) &&
            empty($this->relationWhereNulls)) {
            return $this->updateExistingPivotUsingCustomClass($id, $attributes, $touch);
        }

        if ($this->hasRelationshipColumn($this->updatedAt())) {
            $attributes = $this->addTimestampsToAttachment($attributes, true);
        }

        $updated = $this->newRelationshipStatementForId($this->parseId($id))
            ->update($this->castAttributes($attributes));

        if ($touch) {
            $this->touchIfTouching();
        }

        return $updated;
    }

    /**
     * Update an existing pivot record on the table via a custom class.
     *
     * @param  mixed  $id
     * @param  bool  $touch
     * @return int
     */
    protected function updateExistingPivotUsingCustomClass($id, array $attributes, $touch)
    {
        $pivot = $this->getCurrentlyAttachedPivots()
            ->where($this->foreignPivotKey, $this->parent->{$this->parentKey})
            ->where($this->relatedPivotKey, $this->parseId($id))
            ->first();

        $updated = $pivot ? $pivot->fill($attributes)->isDirty() : false;

        if ($updated) {
            $pivot->save();
        }

        if ($touch) {
            $this->touchIfTouching();
        }

        return (int) $updated;
    }

    /**
     * Attach a model to the parent.
     */
    public function attach(mixed $id, array $attributes = [], bool $touch = true): void
    {
        $this->newRelationStatement()->insert($this->formatAttachRecords(
            $this->parseIds($id), $attributes
        ));

        if ($touch) {
            $this->touchIfTouching();
        }
    }

    /**
     * Get all of the IDs from the given mixed value.
     */
    protected function parseIds(mixed $value): array
    {
        if ($value instanceof Model) {
            return [$value->{$this->getParentKeyName()}];
        }

        if ($value instanceof Collection) {
            return $value->pluck($this->relatedKey)->all();
        }

        if ($value instanceof BaseCollection) {
            return $value->toArray();
        }

        return (array) $value;
    }

    /**
     * @return list<mixed>
     */
    public function fetchKeysOfConnectedNodesAtRightHandSide(): array
    {
        return $this->newRelationshipQuery()
            ->pluck($this->getQualifiedRightKeyName())
            ->all();
    }

    /**
     * @param  list<mixed>  $available
     * @param  array<array-key, array>  $toDetach
     */
    protected function detachAndReturnTheirIds(array $available, array $toDetach): array
    {
        $detached = [];
        $toDetach = array_diff($available, array_keys($toDetach));

        if (count($toDetach) > 0) {
            $this->detach($toDetach);

            $detached = array_map($this->castKey(...), $toDetach);
        }

        return $detached;
    }

    /**
     * @param  list<mixed>  $available
     * @param  array<array-key, array>  $toAttach
     */
    protected function attachAndReturnTheirIds(array $available, array $toAttach): array
    {
        $attach = array_diff_key($toAttach, array_flip($available));
        $attached = [];
        if (count($attach) > 0) {
            $this->attach($attach, [], false);

            $attached = array_keys($attach);
        }

        return $attached;
    }

    public function touchIfNeeded(bool $touch, array $changes): void
    {
        if ($touch &&
            count(Arr::flatten($changes)) > 0) {
            $this->touchIfTouching();
        }
    }

    /**
     * Attach a model to the parent using a custom class.
     *
     * @param  mixed  $id
     * @return void
     */
    protected function attachUsingCustomClass($id, array $attributes)
    {
        $records = $this->formatAttachRecords(
            $this->parseIds($id), $attributes
        );

        foreach ($records as $record) {
            $this->newRelation($record, false)->save();
        }
    }

    /**
     * Create an array of records to insert into the pivot table.
     *
     * @return array
     */
    protected function formatAttachRecords(array $ids, array $attributes)
    {
        $records = [];

        $hasTimestamps = ($this->hasRelationshipColumn($this->createdAt()) ||
            $this->hasRelationshipColumn($this->updatedAt()));

        // To create the attachment records, we will simply spin through the IDs given
        // and create a new record to insert for each ID. Each ID may actually be a
        // key in the array, with extra attributes to be placed in other columns.
        foreach ($ids as $key => $value) {
            $records[] = $this->formatAttachRecord(
                $key, $value, $attributes, $hasTimestamps
            );
        }

        return $records;
    }

    /**
     * Create a full attachment record payload.
     *
     * @param  int  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @param  bool  $hasTimestamps
     * @return array
     */
    protected function formatAttachRecord($key, $value, $attributes, $hasTimestamps)
    {
        [$id, $attributes] = $this->extractAttachIdAndAttributes($key, $value, $attributes);

        return array_merge(
            $this->baseAttachRecord($id, $hasTimestamps), $this->castAttributes($attributes)
        );
    }

    /**
     * Get the attach record ID and extra attributes.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return array
     */
    protected function extractAttachIdAndAttributes($key, $value, array $attributes)
    {
        return is_array($value)
            ? [$key, array_merge($value, $attributes)]
            : [$value, $attributes];
    }

    /**
     * Create a new pivot attachment record.
     *
     * @param  int  $id
     * @param  bool  $timed
     * @return array
     */
    protected function baseAttachRecord($id, $timed)
    {
        $record[$this->relatedPivotKey] = $id;

        $record[$this->foreignPivotKey] = $this->parent->{$this->parentKey};

        // If the record needs to have creation and update timestamps, we will make
        // them by calling the parent model's "freshTimestamp" method which will
        // provide us with a fresh timestamp in this model's preferred format.
        if ($timed) {
            $record = $this->addTimestampsToAttachment($record);
        }

        foreach ($this->defaultRelationValues as $value) {
            $record[$value['column']] = $value['value'];
        }

        return $record;
    }

    /**
     * Set the creation and update timestamps on an attach record.
     */
    protected function addTimestampsToAttachment(array $record, bool $exists = false): array
    {
        $fresh = $this->parent->freshTimestamp();

        if ($this->using) {
            $relationshipModel = new $this->using;

            $fresh = $fresh->format($relationshipModel->getDateFormat());
        }

        if (! $exists && $this->hasRelationshipColumn($this->createdAt())) {
            $record[$this->createdAt()] = $fresh;
        }

        if ($this->hasRelationshipColumn($this->updatedAt())) {
            $record[$this->updatedAt()] = $fresh;
        }

        return $record;
    }

    /**
     * Determine whether the given column is defined as a pivot column.
     */
    public function hasRelationshipColumn(string $column): bool
    {
        return in_array($column, $this->relationshipColumns);
    }

    /**
     * Detach models from the relationship.
     *
     * @param  mixed  $ids
     * @param  bool  $touch
     * @return int
     */
    public function detach($ids = null, $touch = true)
    {
        if ($this->using &&
            ! empty($ids) &&
            empty($this->relationWheres) &&
            empty($this->relationWhereIns) &&
            empty($this->relationWhereNulls)) {
            $results = $this->detachUsingCustomClass($ids);
        } else {
            $query = $this->newRelationshipQuery();

            // If associated IDs were passed to the method we will only delete those
            // associations, otherwise all of the association ties will be broken.
            // We'll return the numbers of affected rows when we do the deletes.
            if (! is_null($ids)) {
                $ids = $this->parseIds($ids);

                if (empty($ids)) {
                    return 0;
                }

                $query->whereIn($this->getQualifiedRelatedPivotKeyName(), (array) $ids);
            }

            // Once we have all of the conditions set on the statement, we are ready
            // to run the delete on the pivot table. Then, if the touch parameter
            // is true, we will go ahead and touch all related models to sync.
            $results = $query->delete();
        }

        if ($touch) {
            $this->touchIfTouching();
        }

        return $results;
    }

    /**
     * Detach models from the relationship using a custom class.
     *
     * @param  mixed  $ids
     * @return int
     */
    protected function detachUsingCustomClass($ids)
    {
        $results = 0;

        foreach ($this->parseIds($ids) as $id) {
            $results += $this->newRelation([
                $this->foreignPivotKey => $this->parent->{$this->parentKey},
                $this->relatedPivotKey => $id,
            ], true)->delete();
        }

        return $results;
    }

    /**
     * Get the pivot models that are currently attached.
     *
     * @return BaseCollection
     */
    protected function getCurrentlyAttachedPivots()
    {
        return $this->newRelationshipQuery()->get()->map(function ($record) {
            $class = $this->using ?: Pivot::class;

            $pivot = $class::fromRawAttributes($this->parent, (array) $record, $this->getTable(), true);

            return $pivot->setPivotKeys($this->foreignPivotKey, $this->relatedPivotKey);
        });
    }

    /**
     * Create a new pivot model instance.
     *
     * @return Model&AsRelationship
     */
    public function newRelation(array $attributes = [], bool $exists = false): Model
    {
        $attributes = array_merge(array_column($this->defaultRelationValues, 'value', 'column'), $attributes);

        $pivot = $this->related->newPivot(
            $this->parent, $attributes, $this->table, $exists, $this->using
        );

        return $pivot->setPivotKeys($this->foreignPivotKey, $this->relatedPivotKey);
    }

    /**
     * Create a new existing relation model instance.
     *
     * @return Model&AsRelationship
     */
    public function newExistingRelation(array $attributes = []): Model
    {
        return $this->newRelation($attributes, true);
    }

    /**
     * Get a new plain query builder for the pivot table.
     */
    public function newRelationStatement(): Builder
    {
        $query = $this->query->getQuery()
            ->newQuery();

        if (! $query instanceof Builder) {
            throw new \LogicException(sprintf('Query is an instance of %s, but must be one of %s', $query::class, Builder::class));
        }

        return $query
            ->from($this->getLeftLabel())
            ->whereRelationship($this->relationshipType, $this->getRightLabel());
    }

    /**
     * Get a new pivot statement for a given "other" ID.
     */
    public function newRelationshipStatementForId(mixed $id): Builder
    {
        return $this->newRelationshipQuery()
            ->whereIn($this->getQualifiedRightKeyName(), $this->parseIds($id));
    }

    public function newRelationshipQuery(): Builder
    {
        $query = $this->newRelationStatement();

        foreach ($this->relationWheres as $arguments) {
            $query->where(...$arguments);
        }

        foreach ($this->relationWhereIns as $arguments) {
            $query->whereIn(...$arguments);
        }

        foreach ($this->relationWhereNulls as $arguments) {
            $query->whereNull(...$arguments);
        }

        return $query->where($this->getQualifiedForeignPivotKeyName(), $this->parent->{$this->parentKey});
    }

    /**
     * Set the columns on the pivot table to retrieve.
     *
     * @param  array|mixed  $columns
     * @return $this
     */
    public function withPivot($columns)
    {
        $this->relationshipColumns = array_merge(
            $this->relationshipColumns, is_array($columns) ? $columns : func_get_args()
        );

        return $this;
    }

    /**
     * Attempt to resolve the intermediate table name from the given string.
     *
     * @param  string  $table
     * @return string
     */
    protected function resolveTableName($table)
    {
        if (! str_contains($table, '\\') || ! class_exists($table)) {
            return $table;
        }

        $model = new $table;

        if (! $model instanceof Model) {
            return $table;
        }

        if (in_array(AsPivot::class, class_uses_recursive($model))) {
            $this->using($table);
        }

        return $model->getTable();
    }

    /**
     * Set the where clause for the relation query.
     *
     * @return $this
     */
    protected function addWhereConstraints()
    {
        $this->query->where(
            $this->getQualifiedForeignPivotKeyName(), '=', $this->parent->{$this->parentKey}
        );

        return $this;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have an array dictionary of child objects we can easily match the
        // children back to their parent using the dictionary and the keys on the
        // parent models. Then we should return these hydrated models back out.
        foreach ($models as $model) {
            $key = $this->getDictionaryKey($model->{$this->parentKey});

            if (isset($dictionary[$key])) {
                $model->setRelation(
                    $relation, $this->related->newCollection($dictionary[$key])
                );
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        // First we'll build a dictionary of child models keyed by the foreign key
        // of the relation so that we will easily and quickly match them to the
        // parents without having a possibly slow inner loop for every model.
        $dictionary = [];

        foreach ($results as $result) {
            $value = $this->getDictionaryKey($result->{$this->accessor}->{$this->foreignPivotKey});

            $dictionary[$value][] = $result;
        }

        return $dictionary;
    }

    /**
     * Get the class being used for pivot models.
     *
     * @return string
     */
    public function getPivotClass()
    {
        return $this->using ?? Pivot::class;
    }

    /**
     * Specify the custom pivot model to use for the relationship.
     *
     * @param  string  $class
     * @return $this
     */
    public function using($class)
    {
        $this->using = $class;

        return $this;
    }

    /**
     * Specify the custom pivot accessor to use for the relationship.
     *
     * @param  string  $accessor
     * @return $this
     */
    public function as($accessor)
    {
        $this->accessor = $accessor;

        return $this;
    }

    /**
     * Set a where clause for a pivot table column.
     *
     * @param  string  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function wherePivot($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->relationWheres[] = func_get_args();

        return $this->where($this->qualifyRelationshipColumn($column), $operator, $value, $boolean);
    }

    /**
     * Set a "where between" clause for a pivot table column.
     *
     * @param  string  $column
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function wherePivotBetween($column, array $values, $boolean = 'and', $not = false)
    {
        return $this->whereBetween($this->qualifyRelationshipColumn($column), $values, $boolean, $not);
    }

    /**
     * Set a "or where between" clause for a pivot table column.
     *
     * @param  string  $column
     * @return $this
     */
    public function orWherePivotBetween($column, array $values)
    {
        return $this->wherePivotBetween($column, $values, 'or');
    }

    /**
     * Set a "where pivot not between" clause for a pivot table column.
     *
     * @param  string  $column
     * @param  string  $boolean
     * @return $this
     */
    public function wherePivotNotBetween($column, array $values, $boolean = 'and')
    {
        return $this->wherePivotBetween($column, $values, $boolean, true);
    }

    /**
     * Set a "or where not between" clause for a pivot table column.
     *
     * @param  string  $column
     * @return $this
     */
    public function orWherePivotNotBetween($column, array $values)
    {
        return $this->wherePivotBetween($column, $values, 'or', true);
    }

    /**
     * Set a "where in" clause for a pivot table column.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function wherePivotIn($column, $values, $boolean = 'and', $not = false)
    {
        $this->relationWhereIns[] = func_get_args();

        return $this->whereIn($this->qualifyRelationshipColumn($column), $values, $boolean, $not);
    }

    /**
     * Set an "or where" clause for a pivot table column.
     *
     * @param  string  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    public function orWherePivot($column, $operator = null, $value = null)
    {
        return $this->wherePivot($column, $operator, $value, 'or');
    }

    /**
     * Set a where clause for a pivot table column.
     *
     * In addition, new pivot records will receive this value.
     *
     * @param  string|array  $column
     * @param  mixed  $value
     * @return $this
     *
     * @throws InvalidArgumentException
     */
    public function withPivotValue($column, $value = null)
    {
        if (is_array($column)) {
            foreach ($column as $name => $value) {
                $this->withPivotValue($name, $value);
            }

            return $this;
        }

        if (is_null($value)) {
            throw new InvalidArgumentException('The provided value may not be null.');
        }

        $this->defaultRelationValues[] = compact('column', 'value');

        return $this->wherePivot($column, '=', $value);
    }

    /**
     * Set an "or where in" clause for a pivot table column.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @return $this
     */
    public function orWherePivotIn($column, $values)
    {
        return $this->wherePivotIn($column, $values, 'or');
    }

    /**
     * Set a "where not in" clause for a pivot table column.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @return $this
     */
    public function wherePivotNotIn($column, $values, $boolean = 'and')
    {
        return $this->wherePivotIn($column, $values, $boolean, true);
    }

    /**
     * Set an "or where not in" clause for a pivot table column.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @return $this
     */
    public function orWherePivotNotIn($column, $values)
    {
        return $this->wherePivotNotIn($column, $values, 'or');
    }

    /**
     * Set a "where null" clause for a pivot table column.
     *
     * @param  string  $column
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function wherePivotNull($column, $boolean = 'and', $not = false)
    {
        $this->relationWhereNulls[] = func_get_args();

        return $this->whereNull($this->qualifyRelationshipColumn($column), $boolean, $not);
    }

    /**
     * Set a "where not null" clause for a pivot table column.
     *
     * @param  string  $column
     * @param  string  $boolean
     * @return $this
     */
    public function wherePivotNotNull($column, $boolean = 'and')
    {
        return $this->wherePivotNull($column, $boolean, true);
    }

    /**
     * Set a "or where null" clause for a pivot table column.
     *
     * @param  string  $column
     * @param  bool  $not
     * @return $this
     */
    public function orWherePivotNull($column, $not = false)
    {
        return $this->wherePivotNull($column, 'or', $not);
    }

    /**
     * Set a "or where not null" clause for a pivot table column.
     *
     * @param  string  $column
     * @return $this
     */
    public function orWherePivotNotNull($column)
    {
        return $this->orWherePivotNull($column, true);
    }

    /**
     * Add an "order by" clause for a pivot table column.
     *
     * @param  string  $column
     * @param  string  $direction
     * @return $this
     */
    public function orderByPivot($column, $direction = 'asc')
    {
        return $this->orderBy($this->qualifyRelationshipColumn($column), $direction);
    }

    /**
     * Find a related model by its primary key or return a new instance of the related model.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return BaseCollection|Model
     */
    public function findOrNew($id, $columns = ['*'])
    {
        if (is_null($instance = $this->find($id, $columns))) {
            $instance = $this->related->newInstance();
        }

        return $instance;
    }

    /**
     * Get the first related model record matching the attributes or instantiate it.
     *
     * @return Model
     */
    public function firstOrNew(array $attributes = [], array $values = [])
    {
        if (is_null($instance = $this->related->where($attributes)->first())) {
            $instance = $this->related->newInstance(array_merge($attributes, $values));
        }

        return $instance;
    }

    /**
     * Get the first related record matching the attributes or create it.
     *
     * @param  bool  $touch
     * @return Model
     */
    public function firstOrCreate(array $attributes = [], array $values = [], array $joining = [], $touch = true)
    {
        if (is_null($instance = (clone $this)->where($attributes)->first())) {
            if (is_null($instance = $this->related->where($attributes)->first())) {
                $instance = $this->create(array_merge($attributes, $values), $joining, $touch);
            } else {
                $this->attach($instance, $joining, $touch);
            }
        }

        return $instance;
    }

    /**
     * Create or update a related record matching the attributes, and fill it with values.
     *
     * @param  bool  $touch
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values = [], array $joining = [], $touch = true)
    {
        if (is_null($instance = (clone $this)->where($attributes)->first())) {
            if (is_null($instance = $this->related->where($attributes)->first())) {
                return $this->create(array_merge($attributes, $values), $joining, $touch);
            } else {
                $this->attach($instance, $joining, $touch);
            }
        }

        $instance->fill($values);

        $instance->save(['touch' => false]);

        return $instance;
    }

    /**
     * Find a related model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return Model|Collection|null
     */
    public function find($id, $columns = ['*'])
    {
        if (! $id instanceof Model && (is_array($id) || $id instanceof Arrayable)) {
            return $this->findMany($id, $columns);
        }

        return $this->where(
            $this->getRelated()->getQualifiedKeyName(), '=', $this->parseId($id)
        )->first($columns);
    }

    /**
     * Find multiple related models by their primary keys.
     *
     * @param  Arrayable|array  $ids
     * @param  array  $columns
     * @return Collection
     */
    public function findMany($ids, $columns = ['*'])
    {
        $ids = $ids instanceof Arrayable ? $ids->toArray() : $ids;

        if (empty($ids)) {
            return $this->getRelated()->newCollection();
        }

        return $this->whereKey(
            $this->parseIds($ids)
        )->get($columns);
    }

    /**
     * Find a related model by its primary key or throw an exception.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return Model|Collection
     *
     * @throws ModelNotFoundException<Model>
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->find($id, $columns);

        $id = $id instanceof Arrayable ? $id->toArray() : $id;

        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif (! is_null($result)) {
            return $result;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->related), $id);
    }

    /**
     * Find a related model by its primary key or call a callback.
     *
     * @param  mixed  $id
     * @param  Closure|array  $columns
     * @return Model|Collection|mixed
     */
    public function findOr($id, $columns = ['*'], Closure $callback = null)
    {
        if ($columns instanceof Closure) {
            $callback = $columns;

            $columns = ['*'];
        }

        $result = $this->find($id, $columns);

        $id = $id instanceof Arrayable ? $id->toArray() : $id;

        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif (! is_null($result)) {
            return $result;
        }

        return $callback();
    }

    /**
     * Add a basic where clause to the query, and return the first result.
     *
     * @param  Closure|string|array  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return Model|static
     */
    public function firstWhere($column, $operator = null, $value = null, $boolean = 'and')
    {
        return $this->where($column, $operator, $value, $boolean)->first();
    }

    /**
     * Execute the query and get the first result.
     *
     * @param  array  $columns
     * @return mixed
     */
    public function first($columns = ['*'])
    {
        $results = $this->take(1)->get($columns);

        return count($results) > 0 ? $results->first() : null;
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param  array  $columns
     * @return Model|static
     *
     * @throws ModelNotFoundException<Model>
     */
    public function firstOrFail($columns = ['*'])
    {
        if (! is_null($model = $this->first($columns))) {
            return $model;
        }

        throw (new ModelNotFoundException)->setModel(get_class($this->related));
    }

    /**
     * Execute the query and get the first result or call a callback.
     *
     * @param  Closure|array  $columns
     * @return Model|static|mixed
     */
    public function firstOr($columns = ['*'], Closure $callback = null)
    {
        if ($columns instanceof Closure) {
            $callback = $columns;

            $columns = ['*'];
        }

        if (! is_null($model = $this->first($columns))) {
            return $model;
        }

        return $callback();
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return ! is_null($this->parent->{$this->parentKey})
            ? $this->get()
            : $this->related->newCollection();
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return Collection
     */
    public function get($columns = ['*'])
    {
        // First we'll add the proper select columns onto the query so it is run with
        // the proper columns. Then, we will get the results and hydrate our pivot
        // models with the result of those columns as a separate model relation.
        $builder = $this->query->applyScopes();

        $columns = $builder->getQuery()->columns ? [] : $columns;

        $models = $builder->addSelect(
            $this->shouldSelect($columns)
        )->getModels();

        $this->hydratePivotRelation($models);

        // If we actually found models we will also eager load any relationships that
        // have been specified as needing to be eager loaded. This will solve the
        // n + 1 query problem for the developer and also increase performance.
        if (count($models) > 0) {
            $models = $builder->eagerLoadRelations($models);
        }

        return $this->related->newCollection($models);
    }

    /**
     * Get the select columns for the relation query.
     *
     * @return array
     */
    protected function shouldSelect(array $columns = ['*'])
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable().'.*'];
        }

        return array_merge($columns, $this->aliasedPivotColumns());
    }

    /**
     * Get the pivot columns for the relation.
     *
     * "pivot_" is prefixed at each column for easy removal later.
     *
     * @return array
     */
    protected function aliasedPivotColumns()
    {
        $defaults = [$this->foreignPivotKey, $this->relatedPivotKey];

        return collect(array_merge($defaults, $this->relationshipColumns))->map(function ($column) {
            return $this->qualifyRelationshipColumn($column).' as pivot_'.$column;
        })->unique()->all();
    }

    /**
     * Get a paginator for the "select" statement.
     *
     * @param  int|null  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->shouldSelect($columns));

        return tap($this->query->paginate($perPage, $columns, $pageName, $page), function ($paginator) {
            $this->hydratePivotRelation($paginator->items());
        });
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param  int|null  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return Paginator
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $this->query->addSelect($this->shouldSelect($columns));

        return tap($this->query->simplePaginate($perPage, $columns, $pageName, $page), function ($paginator) {
            $this->hydratePivotRelation($paginator->items());
        });
    }

    /**
     * Paginate the given query into a cursor paginator.
     *
     * @param  int|null  $perPage
     * @param  array  $columns
     * @param  string  $cursorName
     * @param  string|null  $cursor
     * @return CursorPaginator
     */
    public function cursorPaginate($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
    {
        $this->query->addSelect($this->shouldSelect($columns));

        return tap($this->query->cursorPaginate($perPage, $columns, $cursorName, $cursor), function ($paginator) {
            $this->hydratePivotRelation($paginator->items());
        });
    }

    /**
     * Chunk the results of the query.
     *
     * @param  int  $count
     * @return bool
     */
    public function chunk($count, callable $callback)
    {
        return $this->prepareQueryBuilder()->chunk($count, function ($results, $page) use ($callback) {
            $this->hydratePivotRelation($results->all());

            return $callback($results, $page);
        });
    }

    /**
     * Chunk the results of a query by comparing numeric IDs.
     *
     * @param  int  $count
     * @param  string|null  $column
     * @param  string|null  $alias
     * @return bool
     */
    public function chunkById($count, callable $callback, $column = null, $alias = null)
    {
        $this->prepareQueryBuilder();

        $column ??= $this->getRelated()->qualifyColumn(
            $this->getRelatedKeyName()
        );

        $alias ??= $this->getRelatedKeyName();

        return $this->query->chunkById($count, function ($results) use ($callback) {
            $this->hydratePivotRelation($results->all());

            return $callback($results);
        }, $column, $alias);
    }

    /**
     * Execute a callback over each item while chunking.
     *
     * @param  int  $count
     * @return bool
     */
    public function each(callable $callback, $count = 1000)
    {
        return $this->chunk($count, function ($results) use ($callback) {
            foreach ($results as $key => $value) {
                if ($callback($value, $key) === false) {
                    return false;
                }
            }
        });
    }

    /**
     * Query lazily, by chunks of the given size.
     *
     * @param  int  $chunkSize
     * @return LazyCollection
     */
    public function lazy($chunkSize = 1000)
    {
        return $this->prepareQueryBuilder()->lazy($chunkSize)->map(function ($model) {
            $this->hydratePivotRelation([$model]);

            return $model;
        });
    }

    /**
     * Query lazily, by chunking the results of a query by comparing IDs.
     *
     * @param  int  $chunkSize
     * @param  string|null  $column
     * @param  string|null  $alias
     * @return LazyCollection
     */
    public function lazyById($chunkSize = 1000, $column = null, $alias = null)
    {
        $column ??= $this->getRelated()->qualifyColumn(
            $this->getRelatedKeyName()
        );

        $alias ??= $this->getRelatedKeyName();

        return $this->prepareQueryBuilder()->lazyById($chunkSize, $column, $alias)->map(function ($model) {
            $this->hydratePivotRelation([$model]);

            return $model;
        });
    }

    /**
     * Get a lazy collection for the given query.
     *
     * @return LazyCollection
     */
    public function cursor()
    {
        return $this->prepareQueryBuilder()->cursor()->map(function ($model) {
            $this->hydratePivotRelation([$model]);

            return $model;
        });
    }

    /**
     * Prepare the query builder for query execution.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function prepareQueryBuilder()
    {
        return $this->query->addSelect($this->shouldSelect());
    }

    /**
     * Hydrate the pivot table relationship on the models.
     *
     * @return void
     */
    protected function hydratePivotRelation(array $models)
    {
        // To hydrate the pivot relationship, we will just gather the pivot attributes
        // and create a new Pivot model, which is basically a dynamic model that we
        // will set the attributes, table, and connections on it so it will work.
        foreach ($models as $model) {
            $model->setRelation($this->accessor, $this->newExistingRelation(
                $this->migratePivotAttributes($model)
            ));
        }
    }

    /**
     * Get the pivot attributes from a model.
     *
     * @return array
     */
    protected function migratePivotAttributes(Model $model)
    {
        $values = [];

        foreach ($model->getAttributes() as $key => $value) {
            // To get the pivots attributes we will just take any of the attributes which
            // begin with "pivot_" and add those to this arrays, as well as unsetting
            // them from the parent's models since they exist in a different table.
            if (str_starts_with($key, 'pivot_')) {
                $values[substr($key, 6)] = $value;

                unset($model->$key);
            }
        }

        return $values;
    }

    /**
     * If we're touching the parent model, touch.
     *
     * @return void
     */
    public function touchIfTouching()
    {
        if ($this->touchingParent()) {
            $this->getParent()->touch();
        }

        if ($this->getParent()->touches($this->relationName)) {
            $this->touch();
        }
    }

    /**
     * Determine if we should touch the parent on sync.
     *
     * @return bool
     */
    protected function touchingParent()
    {
        return $this->getRelated()->touches($this->guessInverseRelation());
    }

    /**
     * Attempt to guess the name of the inverse of the relation.
     *
     * @return string
     */
    protected function guessInverseRelation()
    {
        return Str::camel(Str::pluralStudly(class_basename($this->getParent())));
    }

    /**
     * Touch all of the related models for the relationship.
     *
     * E.g.: Touch all roles associated with this user.
     *
     * @return void
     */
    public function touch()
    {
        $columns = [
            $this->related->getUpdatedAtColumn() => $this->related->freshTimestampString(),
        ];

        // If we actually have IDs for the relation, we will run the query to update all
        // the related model's timestamps, to make sure these all reflect the changes
        // to the parent models. This will help us keep any caching synced up here.
        if (count($ids = $this->allRelatedIds()) > 0) {
            $this->getRelated()->newQueryWithoutRelationships()->whereKey($ids)->update($columns);
        }
    }

    /**
     * Get all of the IDs for the related models.
     *
     * @return BaseCollection
     */
    public function allRelatedIds()
    {
        return $this->newRelationshipQuery()->pluck($this->relatedPivotKey);
    }

    /**
     * Save a new model and attach it to the parent model.
     *
     * @param  bool  $touch
     * @return Model
     */
    public function save(Model $model, array $pivotAttributes = [], $touch = true)
    {
        $model->save(['touch' => false]);

        $this->attach($model, $pivotAttributes, $touch);

        return $model;
    }

    /**
     * Save a new model without raising any events and attach it to the parent model.
     *
     * @param  bool  $touch
     * @return Model
     */
    public function saveQuietly(Model $model, array $pivotAttributes = [], $touch = true)
    {
        return Model::withoutEvents(function () use ($model, $pivotAttributes, $touch) {
            return $this->save($model, $pivotAttributes, $touch);
        });
    }

    /**
     * Save an array of new models and attach them to the parent model.
     *
     * @param  BaseCollection|array  $models
     * @return array
     */
    public function saveMany($models, array $pivotAttributes = [])
    {
        foreach ($models as $key => $model) {
            $this->save($model, (array) ($pivotAttributes[$key] ?? []), false);
        }

        $this->touchIfTouching();

        return $models;
    }

    /**
     * Save an array of new models without raising any events and attach them to the parent model.
     *
     * @param  BaseCollection|array  $models
     * @return array
     */
    public function saveManyQuietly($models, array $pivotAttributes = [])
    {
        return Model::withoutEvents(function () use ($models, $pivotAttributes) {
            return $this->saveMany($models, $pivotAttributes);
        });
    }

    /**
     * Create a new instance of the related model.
     *
     * @param  bool  $touch
     * @return Model
     */
    public function create(array $attributes = [], array $joining = [], $touch = true)
    {
        $instance = $this->related->newInstance($attributes);

        // Once we save the related model, we need to attach it to the base model via
        // through intermediate table so we'll use the existing "attach" method to
        // accomplish this which will insert the record and any more attributes.
        $instance->save(['touch' => false]);

        $this->attach($instance, $joining, $touch);

        return $instance;
    }

    /**
     * Create an array of new instances of the related models.
     *
     * @return array
     */
    public function createMany(iterable $records, array $joinings = [])
    {
        $instances = [];

        foreach ($records as $key => $record) {
            $instances[] = $this->create($record, (array) ($joinings[$key] ?? []), false);
        }

        $this->touchIfTouching();

        return $instances;
    }

    /**
     * Add the constraints for a relationship query.
     *
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQuery(\Illuminate\Database\Eloquent\Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        if ($parentQuery->getQuery()->from == $query->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfJoin($query, $parentQuery, $columns);
        }

        $this->performJoin($query);

        return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
    }

    /**
     * Add the constraints for a relationship query on the same table.
     *
     * @param  array|mixed  $columns
     */
    public function getRelationExistenceQueryForSelfJoin(\Illuminate\Database\Eloquent\Builder $query, \Illuminate\Database\Eloquent\Builder $parentQuery, mixed $columns = ['*']): \Illuminate\Database\Eloquent\Builder
    {
        $query->select($columns);

        $query->from($this->related->getTable().' as '.$hash = $this->getRelationCountHash());

        $this->related->setTable($hash);

        $query->whereRelationship($query);  // TODO

        return parent::getRelationExistenceQuery($query, $parentQuery, $columns);
    }

    /**
     * Specify that the pivot table has creation and update timestamps.
     *
     * @return $this
     */
    public function withTimestamps(string|null $createdAt = null, string|null $updatedAt = null): self
    {
        $this->withTimestamps = true;

        $this->pivotCreatedAtName = $createdAt;
        $this->pivotUpdatedAtName = $updatedAt;

        return $this->withPivot($this->createdAt(), $this->updatedAt());
    }

    /**
     * Get the name of the "created at" column.
     */
    public function createdAt(): string
    {
        return $this->pivotCreatedAtName ?? $this->parent->getCreatedAtColumn();
    }

    /**
     * Get the name of the "updated at" column.
     */
    public function updatedAt(): string
    {
        return $this->pivotUpdatedAtName ?? $this->parent->getUpdatedAtColumn();
    }

    /**
     * Get the parent key for the relationship.
     */
    public function getParentKeyName(): string
    {
        return $this->parent->getKeyName();
    }

    /**
     * Get the related key for the relationship.
     */
    public function getRelatedKeyName(): string
    {
        return $this->query->getModel()->getKeyName();
    }

    /**
     * Get the intermediate table for the relationship.
     */
    public function getRelationshipType(): string
    {
        return $this->relationshipType;
    }

    /**
     * Get the relationship name for the relationship.
     */
    public function getRelationName(): string
    {
        return $this->relationName;
    }

    /**
     * Get the name of the pivot accessor for this relationship.
     */
    public function getPivotAccessor(): string
    {
        return $this->accessor;
    }

    /**
     * Get the pivot columns for this relationship.
     *
     * @return array
     */
    public function getRelationshipColumns()
    {
        return $this->relationshipColumns;
    }

    /**
     * Qualify the given column name by the pivot table.
     */
    public function qualifyRelationshipColumn(string $column): string
    {
        return str_contains($column, '.')
            ? $column
            : $this->relationshipType.'.'.$column;
    }

    protected function newRelatedInstanceFor(Model $parent): Model
    {
        return $this->getRightModel()->newInstance();
    }

    public function getLeftModel(): Model
    {
        return $this->related;
    }

    public function getLeftLabel(): string
    {
        return $this->getLeftModel()->getTable();
    }

    public function getQualifiedLeftKeyName(): string
    {
        return $this->getLeftLabel().'.'.$this->getLeftModel()->getKeyName();
    }

    public function getRightModel(): Model
    {
        return $this->parent;
    }

    public function getRightLabel(): string
    {
        return $this->getRightModel()->getTable();
    }

    public function getQualifiedRightKeyName(): string
    {
        return $this->getRightLabel().'.'.$this->getRightModel()->getKeyName();
    }
}
