<?php namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn;
use Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Model as EloquentModel;

class BelongsToMany extends HasOneOrMany {

    /**
     * The relationship name.
     *
     * @var string
     */
    protected $relation;

    /**
     * The relationship type, which is the label to be used
     * in the relationship between models.
     *
     * @var string
     */
    protected $type;

    /**
     * The key that we should query with.
     *
     * @var string
     */
    protected $key;

    public function __construct(Builder $query, Model $parent, $type, $key, $relation)
    {
        parent::__construct($query, $parent, $type, $key, $relation);

        $this->finder = $this->newFinder();
    }

    public function addConstraints()
    {

    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array   $models
     * @param  string  $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model)
        {
            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->get();
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        $this->matchOneOrMany($models, $results, $relation, 'many');
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $this->query->whereIn($this->getForeignKey(), $this->getKeys($models));
    }

    /**
     * Get the edge between the parent model and the given model or
     * the related model determined by the relation function name.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In|Out]
     */
    public function edge(Model $model = null)
    {
        return $this->finder->first($this->parent, $model, $this->type);
    }

    /**
     * Get an instance of the Edge[In|Out] relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array         $attributes
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In|Out]
     */
    public function getEdge(EloquentModel $model = null, $attributes = array())
    {
        $model = ( ! is_null($model)) ? $model : $this->related;

        return new EdgeOut($this->query, $this->parent, $model, $this->type, $attributes);
    }

    /**
     * Attach a model to the parent.
     *
     * @param  mixed  $id
     * @param  array  $attributes
     * @param  bool   $touch
     * @return void
     */
    public function attach($id, array $attributes = array(), $touch = true)
    {
        if ($id instanceof Model)
        {
            $id = [$id];
        } elseif ($id instanceof Collection)
        {
            $id = $id->all();
        } elseif ( ! $this->isArrayOfModels($id))
        {
            $id = $this->modelsFromIds($id);
            // In case someone is messing with us and passed a bunch of ids (or single id)
            // that do not exist we slap them in the face with a ModelNotFoundException.
            // There must be at least a record found as for the records that do not match
            // they will be ignored and forever forgotten, poor thing.
            if (count($id) < 1) throw new ModelNotFoundException;

            $id = $id->all();
        }

        $saved = $this->saveMany($id, $attributes);

        if ($touch) $this->touchIfTouching();

        return (count($id) > 1) ? $saved : $saved->first();
    }

    /**
     * Detach models from the relationship.
     *
     * @param  int|array  $ids
     * @param  bool  $touch
     * @return int
     */
    public function detach($id = array(), $touch = true)
    {
        if ( ! $id instanceof Model and ! $id instanceof Collection)
        {
            $id = $this->modelsFromIds($id);
        }

        // Prepare for a batch operation to take place so that we don't
        // overwhelm the database with many delete hits.
        $this->finder->prepareBatch();

        foreach ($id as $model)
        {
            $edge = $this->edge($model);
            $edge->delete();
        }

        $results = $this->finder->commitBatch();

        if ($touch) $this->touchIfTouching();

        return $results;
    }

    /**
     * Sync the intermediate tables with a list of IDs or collection of models.
     *
     * @param  $ids
     * @param  bool   $detaching
     * @return array
     */
    public function sync($ids, $detaching = true)
    {
        $changes = array(
            'attached' => array(), 'detached' => array(), 'updated' => array()
        );

        // get them as collection
        if ($ids instanceof Collection) $ids = $ids->modelKeys();

        // $this->attach($ids, [], $touch = false);

        // First we need to attach the relationships that do not exist
        // for this model so we'll spin throuhg the edges of this model
        // for the specified type regardless of the direction and create
        // those that do not exist.

        // Let's fetch the existing edges first.
        $edges = $this->edges();
        // Collect the current related models IDs out of related models.
        $current = array_map(function($edge){ return $edge->getRelated()->getKey(); }, $edges->toArray());

        $records = $this->formatSyncList($ids);

        $detach = array_diff($current, array_keys($records));

        // Next, we will take the differences of the currents and given IDs and detach
        // all of the entities that exist in the "current" array but are not in the
        // the array of the IDs given to the method which will complete the sync.
        if ($detaching && count($detach) > 0)
        {
            $this->detach($detach);

            $changes['detached'] = (array) array_map('intval', $detach);
        }

        // Now we are finally ready to attach the new records. Note that we'll disable
        // touching until after the entire operation is complete so we don't fire a
        // ton of touch operations until we are totally done syncing the records.
        $changes['attached'] = $records;
        $changes['updated']  = $current;

        // Now we are finally ready to attach the new records. Note that we'll disable
        // touching until after the entire operation is complete so we don't fire a
        // ton of touch operations until we are totally done syncing the records.
        $changes = array_merge(
            $changes, $this->attachNew($records, $current, false)
        );

        $this->touchIfTouching();

        return $changes;
    }

    protected function attachNew(array $records, array $current, $touch = true)
    {
        $changes = array('attached' => array(), 'updated' => array());

        foreach ($records as $id => $attributes)
        {
            // If the ID is not in the list of existing pivot IDs, we will insert a new pivot
            // record, otherwise, we will just update this existing record on this joining
            // table, so that the developers will easily update these records pain free.
            if ( ! in_array($id, $current))
            {
                $this->attach($id, $attributes, $touch);

                $changes['attached'][] = (int) $id;
            }
            elseif (count($attributes) > 0)
            {
                $this->updateEdge($id, $attributes);

                $changes['updated'][] = (int) $id;
            }
        }

        return $changes;
    }

    /**
     * Update an edge's properties.
     *
     * @param  int $id
     * @param  array  $properties
     * @return boolean
     */
    public function updateEdge($id, array $properties)
    {
        $related = $this->related->findOrFail($id);
        $edge = $this->finder->first($this->parent, $this->related->findOrFail($id), $this->type);
        $edge->fill($properties);
        return $edge->save();
    }

    /**
     * Format the sync list so that it is keyed by ID.
     *
     * @param  array  $records
     * @return array
     */
    protected function formatSyncList(array $records)
    {
        $results = array();

        foreach ($records as $id => $attributes)
        {
            if ( ! is_array($attributes))
            {
                list($id, $attributes) = array($attributes, array());
            }

            $results[$id] = $attributes;
        }

        return $results;
    }

    /**
     * If we're touching the parent model, touch.
     *
     * @return void
     */
    public function touchIfTouching()
    {
        if ($this->touchingParent()) $this->getParent()->touch();

        if ($this->getParent()->touches($this->relation)) $this->touch();
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
        return camel_case(str_plural(class_basename($this->getParent())));
    }

    /**
     * Get the related models out of their Ids.
     *
     * @param  array  $ids
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function modelsFromIds($ids)
    {
        // We need a Model in order to save this relationship so we try
        // to whereIn the given id(s) through the related model.
        return $this->related->whereIn($this->related->getKeyName(), (array) $ids)->get();
    }

    /**
     * Determine whether the given array of models is actually
     * an array containing model instances. In case at least one
     * of the elements is not a Model this will return false.
     *
     * @param  array   $models
     * @return boolean
     */
    public function isArrayOfModels($models)
    {
        if ( ! is_array($models)) return false;

        $notModels = array_filter($models, function($model)
        {
            return ! $model instanceof Model;
        });

        return empty($notModels);
    }
}
