<?php

namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Illuminate\Support\Str;
use Vinelab\NeoEloquent\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Collection;
use Vinelab\NeoEloquent\Eloquent\Edges\Edge;
use Vinelab\NeoEloquent\Eloquent\Edges\Finder;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Exceptions\ModelNotFoundException;

abstract class HasOneOrMany extends Relation implements RelationInterface
{
    /**
     * The name of the relationship.
     *
     * @var string
     */
    protected $relation;

    /**
     * The relationships finder instance.
     *
     * @var \Vinelab\NeoEloquent\Eloquent\Edges\Finder
     */
    protected $finder;

    /**
     * The edge direction for this relationship.
     *
     * @var string
     */
    protected $edgeDirection = 'out';

    /**
     * Create a new has many relationship instance.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Builder $query
     * @param \Vinelab\NeoEloquent\Eloquent\Model   $parent
     * @param string                                $type
     */
    public function __construct(Builder $query, Model $parent, $type, $key, $relation)
    {
        $this->localKey = $key;
        $this->relation = $relation;
        $this->type = $type;

        parent::__construct($query, $parent, $type, $key);

        $this->finder = $this->newFinder();
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param array  $models
     * @param string $relation
     *
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            // In the case of fetching nested relations, we will get an array
            // with the first key being the model we need, and the other being
            // the related model so we'll just take the first model out of the array.
            if (is_array($model)) {
                $model = reset($model);
            }
            // } else if ($model instanceof Relationship) {
            //     $model = $model->getEndModel();
            // }

            $model->setRelation($relation, $this->related->newCollection());
        }

        return $models;
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param array $models
     */
    public function addEagerConstraints(array $models)
    {
        $this->query->startModel = $this->parent;
        $this->query->endModel = $this->related;
        $this->query->relationshipName = $this->relation;
    }

    /**
     * Get all of the primary keys for an array of models.
     *
     * @param array  $models
     * @param string $key
     *
     * @return array
     */
    protected function getKeys(array $models, $key = null)
    {
        return array_unique(array_values(array_map(function ($value) use ($key, $models) {
            if (is_array($value)) {
                // $models is a collection of associative arrays with the keys being a model and its relation,
                // our job is to know which one to use since if we use the first element
                // it might not be what we need, in some cases it's the lat element.
                // To do that we're going to reversely detect the correct identifier (key)
                // to use and it's sufficient to detect it from one of the records.
                $identifier = $this->determineValueIdentifier(reset($models));
                $value = $value[$identifier];
                // $value = reset($value);
                // $value = end($value);
            }
            // } else if($value instanceof Relationship) {
            //     $value = $value->getEndModel();
            // }

            return $key ? $value->getAttribute($key) : $value->getKey();

        }, $models)));
    }

    /**
     * Get an instance of the Edge[In, Out, etc.] relationship.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array                               $attributes
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In,Out, etc.]
     */
    abstract public function getEdge(Model $model = null, $attributes = array());

    /**
     * Get the edge between the parent model and the given model or
     * the related model determined by the relation function name.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In,Out, etc.]
     */
    public function edge(Model $model = null)
    {
        return $this->finder->first($this->parent, $model, $this->type, $this->edgeDirection);
    }

    /**
     * Get all the edges of the given type and direction.
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In|Out]
     */
    public function edges()
    {
        return $this->finder->get($this->parent, $this->related, $this->type, $this->edgeDirection);
    }

    /**
     * Match the eagerly loaded results to their single parents.
     *
     * @param array                                    $models
     * @param \Illuminate\Database\Eloquent\Collection $results
     * @param string                                   $relation
     *
     * @return array
     */
    public function matchOne(array $models, Collection $results, $relation)
    {
        return $this->matchOneOrMany($models, $results, $relation, 'one');
    }

    /**
     * Match the eagerly loaded results to their many parents.
     *
     * @param array                                    $models
     * @param \Illuminate\Database\Eloquent\Collection $results
     * @param string                                   $relation
     *
     * @return array
     */
    public function matchMany(array $models, Collection $results, $relation)
    {
        return $this->matchOneOrMany($models, $results, $relation, 'many');
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param array                                    $models
     * @param \Illuminate\Database\Eloquent\Collection $results
     * @param string                                   $relation
     *
     * @return array
     */
    public function matchOneOrMany(array $models, Collection $results, $relation, $type)
    {
        // $map = [];

        // foreach ($models as $model) {
        //     if (is_array($model)) {
        //         unset($model[$relation]);
        //         $model = array_values($model)[0];
        //     }

        //     $index = 'i-'.$model->getKey();
        //     $map[$index] = $model;
        // }

        //  // We will need the parent node placeholder so that we use it to extract related results.
        // $startNodeIdentifier = $this->query->getQuery()->modelAsNode($this->parent->nodeLabel());

        // foreach ($results as $result) {
        //     if (is_array($result)) {
        //         $model = $result[$startNodeIdentifier];
        //     }

        //     $index = 'i-'.$model->getKey();
        //     $model = $map[$index];

        //     switch ($type) {
        //         case 'one':
        //         default:
        //             $model->setRelation($relation, $result[$relation]);
        //             break;
        //         case 'many':
        //             $collection = $model->$relation;
        //             $collection->push($result[$relation]);
        //             $model->setRelation($relation, $collection);
        //             break;
        //     }
        // }

        // foreach ($results as $result) {
        //     $startModel = $result->getStartModel();
        //     $endModel = $result->getEndModel();

        //     $index = 'i-'.$startModel->getKey();
        //     $model = $map[$index];

        //     switch ($type) {
        //         case 'one':
        //         default:
        //             $model->setRelation($relation, $endModel);
        //             break;
        //         case 'many':
        //             $collection = $model->$relation;
        //             $collection->push($endModel);
        //             $model->setRelation($relation, $collection);
        //             break;
        //     }
        // }

        // return array_values($map);

        /// ---- OLD IMPLEMENTATION ------ //


        // We will need the parent node placeholder so that we use it to extract related results.
        $parent = $this->query->getQuery()->modelAsNode($this->parent->nodeLabel());

        /*
         * Looping into all the parents to match back onto their children using
         * the primary key to map them onto the correct instances, every single
         * result will be having both instances at each Collection item, held by their
         * node placeholder.
         */
        foreach ($models as $model) {
            $matched = $results->filter(function ($result) use ($parent, $model, $models) {
                if ($result[$parent] instanceof Model) {
                    // In the case of fetching nested relations, we will get an array
                    // with the first key being the model we need, and the other being
                    // the related model so we'll just take the first model out of the array.
                    if (is_array($model)) {
                        $identifier = $this->determineValueIdentifier($model);
                        $model = $model[$identifier];
                    }

                    return $model->getKey() == $result[$parent]->getKey();
                }
            });

            // Now that we have the matched parents we know where to add the relations.
            // Sometimes we have more than a match so we gotta catch them all!
            foreach ($matched as $match) {
                // In the case of fetching nested relations, we will get an array
                // with the first key being the model we need, and the other being
                // the related model so we'll just take the first model out of the array.
                if (is_array($model)) {
                    $identifier = $this->determineValueIdentifier($model);
                    $model = $model[$identifier];
                }

                if ($type == 'many') {
                    $collection = $this->related->newCollection();

                    if ($model->hasRelation($relation)) {
                        $collection = $model->getRelation($relation);
                    }

                    $collection->push($match[$relation]);
                    $model->setRelation($relation, $collection);
                } else {
                    $model->setRelation($relation, $match[$relation]);
                }
            }
        }

        return $models;
    }

    /**
     * Get the value of a relationship by one or many type.
     *
     * @param array  $dictionary
     * @param string $key
     * @param string $type
     *
     * @return mixed
     */
    protected function getRelationValue(array $dictionary, $key, $type)
    {
        $value = $dictionary[$key];

        return $type == 'one' ? reset($value) : $this->related->newCollection($value);
    }

    /**
     * Build model dictionary keyed by the relation's foreign key.
     *
     * @param \Illuminate\Database\Eloquent\Collection $results
     *
     * @return array
     */
    protected function buildDictionary(Collection $results)
    {
        $dictionary = [];

        $foreign = $this->getPlainForeignKey();

        // First we will create a dictionary of models keyed by the foreign key of the
        // relationship as this will allow us to quickly access all of the related
        // models without having to do nested looping which will be quite slow.
        foreach ($results as $result) {
            $dictionary[$result->{$foreign}][] = $result;
        }

        return $dictionary;
    }

    /**
     * Attach a model instance to the parent model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array                               $properties The relationship properites
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In, Out, etc.]
     */
    public function save(Model $model, array $properties = array())
    {
        $model->save() ? $model : false;
        // Create a new edge relationship for both models
        $edge = $this->getEdge($model, $properties);
        // Save the edge
        $edge->save();

        return $edge;
    }

    /**
     * Attach an array of models to the parent instance.
     *
     * @param array $models
     * @param arra  $properties The relationship properties
     *
     * @return array
     */
    public function saveMany($models, array $properties = array())
    {
        // We will collect the edges returned by save() in an Eloquent Database Collection
        // and return them when done.
        $edges = new Collection();

        foreach ($models as $model) {
            $edges->push($this->save($model, $properties));
        }

        return $edges;
    }

    /**
     * Create a new instance of the related model.
     *
     * @param array $attributes
     * @param array $properties The relationship properites
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Model
     */
    public function create(array $attributes = [], array $properties = array())
    {
        // Here we will set the raw attributes to avoid hitting the "fill" method so
        // that we do not have to worry about a mass accessor rules blocking sets
        // on the models. Otherwise, some of these attributes will not get set.
        $instance = $this->related->newInstance($attributes);

        return $this->save($instance, $properties);
    }

    /**
     * Create an array of new instances of the related model.
     *
     * @param array $records
     * @param array $properties The relationship properites
     *
     * @return array
     */
    public function createMany(array $records, array $properties = array())
    {
        $instances = new Collection();

        foreach ($records as $record) {
            $instances->push($this->create($record, $properties));
        }

        return $instances;
    }

    /**
     * Set the base constraints on the relation query.
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            /*
             * For has one relationships we need to actually query on the primary key
             * of the parent model matching on the OUTGOING relationship by name.
             *
             * We are trying to achieve a Cypher that goes something like:
             *
             * MATCH (user:`User`), (user)-[:PHONE]->(phone:`Phone`)
             * WHERE id(user) = 86234
             * RETURN phone;
             *
             * (user:`User`) represents a matching statement where
             * 'user' is the parent Node's placeholder and '`User`' is the parentLabel.
             * All node placeholders must be lowercased letters and will be used
             * throught the query to represent the actual Node.
             *
             * Resulting from:
             * class User extends NeoEloquent {
             *
             *     public function phone()
             *     {
             *          return $this->hasOne('Phone', 'PHONE');
             *     }
             * }
            */

            // Get the parent node's placeholder.
            $parentNode = $this->getParentNode();
            // Tell the query that we only need the related model returned.
            $this->query->select($this->relation);
            // Set the parent node's placeholder as the RETURN key.
            $this->query->getQuery()->from = array($this->relation);
            // Build the MATCH ()-[]->() Cypher clause.
            $this->query->matchOut($this->parent, $this->related, $this->relation, $this->type, $this->localKey, $this->parent->{$this->localKey});
            // Add WHERE clause over the parent node's matching key = value.
            $this->query->where($parentNode.'.'.$this->localKey, '=', $this->parent->{$this->localKey});
        }
    }

    /**
     * Attach a model to the parent.
     *
     * @param mixed $id
     * @param array $attributes
     * @param bool  $touch
     */
    public function attach($id, array $attributes = array(), $touch = true)
    {
        $models = $id;

        if ($id instanceof Model) {
            $models = [$id];
        } elseif ($id instanceof Collection) {
            $models = $id->all();
        } elseif (!$this->isArrayOfModels($id)) {
            $models = $this->modelsFromIds($id);
            // In case someone is messing with us and passed a bunch of ids (or single id)
            // that do not exist we slap them in the face with a ModelNotFoundException.
            // There must be at least a record found as for the records that do not match
            // they will be ignored and forever forgotten, poor thing.
            if (count($models) < 1) {
                throw (new ModelNotFoundException())->setModel(get_class($this->related));
            }

            $models = $models->all();
        }

        $saved = $this->saveMany($models, $attributes);

        if ($touch) {
            $this->touchIfTouching();
        }

        return (!is_array($id)) ? $saved->first() : $saved;
    }

    /**
     * Detach models from the relationship.
     *
     * @param int|array $ids
     * @param bool      $touch
     *
     * @return int
     */
    public function detach($id = array(), $touch = true)
    {
        if (!$id instanceof Model and !$id instanceof Collection) {
            $id = $this->modelsFromIds($id);
        } elseif (!is_array($id)) {
            $id = [$id];
        }

        /*
         * @todo enhance this by creating a WHERE IN query
         */
        // Prepare for a batch operation to take place so that we don't
        // overwhelm the database with many delete hits.
        $results = [];
        foreach ($id as $model) {
            $edge = $this->edge($model);
            $results[] = $edge->delete();
        }

        if ($touch) {
            $this->touchIfTouching();
        }

        return !in_array(false, $results);
    }

    public function delete($shouldKeepEndNode = false)
    {
        return $this->finder->delete($shouldKeepEndNode);
    }

    /**
     * Sync the intermediate tables with a list of IDs or collection of models.
     *
     * @param  $ids
     * @param bool $detaching
     *
     * @return array
     */
    public function sync($ids, $detaching = true)
    {
        $changes = array(
            'attached' => array(), 'detached' => array(), 'updated' => array(),
        );

        // get them as collection
        if ($ids instanceof Collection) {
            $ids = $ids->modelKeys();
        } elseif (!is_array($ids)) {
            $ids = [$ids];
        }

        // First we need to attach the relationships that do not exist
        // for this model so we'll spin throuhg the edges of this model
        // for the specified type regardless of the direction and create
        // those that do not exist.

        // Let's fetch the existing edges first.
        $edges = $this->edges();
        // Collect the current related models IDs out of related models.
        $current = array_map(function (Edge $edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        $records = $this->formatSyncList($ids);

        $detach = array_diff($current, array_keys($records));

        // Next, we will take the differences of the currents and given IDs and detach
        // all of the entities that exist in the "current" array but are not in the
        // the array of the IDs given to the method which will complete the sync.
        if ($detaching && count($detach) > 0) {
            $this->detach($detach);

            $changes['detached'] = (array) array_map('intval', $detach);
        }

        // Now we are finally ready to attach the new records. Note that we'll disable
        // touching until after the entire operation is complete so we don't fire a
        // ton of touch operations until we are totally done syncing the records.
        $changes['attached'] = $records;
        $changes['updated'] = $current;

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

        foreach ($records as $id => $attributes) {
            // If the ID is not in the list of existing pivot IDs, we will insert a new pivot
            // record, otherwise, we will just update this existing record on this joining
            // table, so that the developers will easily update these records pain free.
            if (!in_array($id, $current)) {
                $this->attach($id, $attributes, $touch);

                $changes['attached'][] = (int) $id;
            } elseif (count($attributes) > 0) {
                $this->updateEdge($id, $attributes);

                $changes['updated'][] = (int) $id;
            }
        }

        return $changes;
    }

    /**
     * Perform an update on all the related models.
     *
     * @param array $attributes
     *
     * @return int
     */
    public function update(array $attributes)
    {
        if ($this->related->usesTimestamps()) {
            $attributes[$this->relatedUpdatedAt()] = $this->related->freshTimestampString();
        }

        return $this->query->update($attributes);
    }

    /**
     * Update an edge's properties.
     *
     * @param int   $id
     * @param array $properties
     *
     * @return bool
     */
    public function updateEdge($id, array $properties)
    {
        $edge = $this->finder->first($this->parent, $this->related->findOrFail($id), $this->type, $this->edgeDirection);
        $edge->fill($properties);

        return $edge->save();
    }

    /**
     * Format the sync list so that it is keyed by ID.
     *
     * @param array $records
     *
     * @return array
     */
    protected function formatSyncList(array $records)
    {
        $results = array();

        foreach ($records as $id => $attributes) {
            if (!is_array($attributes)) {
                list($id, $attributes) = array($attributes, array());
            }

            $results[$id] = $attributes;
        }

        return $results;
    }

    /**
     * If we're touching the parent model, touch.
     */
    public function touchIfTouching()
    {
        if ($this->touchingParent()) {
            $this->getParent()->touch();
        }

        if ($this->getParent()->touches($this->relation)) {
            $this->touch();
        }
    }

    /**
     * Find a model by its primary key or return new instance of the related model.
     *
     * @param mixed $id
     * @param array $columns
     *
     * @return \Illuminate\Support\Collection|\Illuminate\Database\Eloquent\Model
     */
    public function findOrNew($id, $columns = ['*'])
    {
        if (is_null($instance = $this->find($id, $columns))) {
            $instance = $this->related->newInstance();

            $instance->setAttribute($this->getPlainForeignKey(), $this->getParentKey());
        }

        return $instance;
    }

    /**
     * Get the first related model record matching the attributes or instantiate it.
     *
     * @param array $attributes
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrNew(array $attributes)
    {
        if (is_null($instance = $this->where($attributes)->first())) {
            $instance = $this->related->newInstance($attributes);

            $instance->setAttribute($this->getPlainForeignKey(), $this->getParentKey());
        }

        return $instance;
    }

    /**
     * Get the first related record matching the attributes or create it.
     *
     * @param array $attributes
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function firstOrCreate(array $attributes)
    {
        if (is_null($instance = $this->where($attributes)->first())) {
            $instance = $this->create($attributes);
        }

        return $instance;
    }

    /**
     * Create or update a related record matching the attributes, and fill it with values.
     *
     * @param array $attributes
     * @param array $values
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $instance = $this->firstOrNew($attributes);

        $instance->fill($values);

        $instance->save();

        return $instance;
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
        return Str::camel(Str::plural(class_basename($this->getParent())));
    }

    /**
     * Get the related models out of their Ids.
     *
     * @param array $ids
     *
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
     * @param array $models
     *
     * @return bool
     */
    public function isArrayOfModels($models)
    {
        if (!is_array($models)) {
            return false;
        }

        $notModels = array_filter($models, function ($model) {
            return !$model instanceof Model;
        });

        return empty($notModels);
    }

    /**
     * Get the plain foreign key.
     *
     * @return string
     */
    public function getPlainForeignKey()
    {
        return $this->relation;
    }

    /**
     * Get the foreign key for the relationship.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return $this->getForeignKey;
    }

    /**
     * Get the key value of the parent's local key.
     *
     * @return mixed
     */
    public function getParentKey()
    {
        return $this->parent->getAttribute($this->localKey);
    }

    /**
     * Get the fully qualified parent key name.
     *
     * @return string
     */
    public function getQualifiedParentKeyName()
    {
        return $this->parent->getTable().'.'.$this->localKey;
    }
    /**
     * Get a new Finder instance.
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Finder
     */
    public function newFinder()
    {
        return new Finder($this->query);
    }

    /**
     * Get the key for comparing against the parent key in "has" query.
     *
     * @return string
     */
    public function getHasCompareKey()
    {
        return $this->related->getKeyName();
    }

    /**
     * Get the relation name.
     *
     * @return string
     */
    public function getRelationName()
    {
        return $this->relation;
    }

    /**
     * Get the relationship type (label in other words),
     * [:FOLLOWS] etc.
     *
     * @return string
     */
    public function getRelationType()
    {
        return $this->type;
    }

    /**
     * Get the localKey.
     *
     * @return string
     */
    public function getLocalKey()
    {
        return $this->localKey;
    }

    /**
     * Get the parent model's value according to $localKey.
     *
     * @return mixed
     */
    public function getParentLocalKeyValue()
    {
        return $this->parent->{$this->localKey};
    }

    /**
     * Get the parent model's Node placeholder.
     *
     * @return string
     */
    public function getParentNode()
    {
        return $this->query->getQuery()->modelAsNode($this->parent->nodeLabel());
    }

    /**
     * Get the related model's Node placeholder.
     *
     * @return string
     */
    public function getRelatedNode()
    {
        return $this->query->getQuery()->modelAsNode($this->related->nodeLabel());
    }

    /**
     * Get the edge direction for this relationship.
     *
     * @return string
     */
    public function getEdgeDirection()
    {
        return $this->edgeDirection;
    }
}
