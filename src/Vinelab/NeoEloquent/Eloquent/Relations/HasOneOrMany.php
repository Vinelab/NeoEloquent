<?php namespace Vinelab\NeoEloquent\Eloquent\Relations;

use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany as IlluminateHasOneOrMany;

abstract class HasOneOrMany extends IlluminateHasOneOrMany {

    /**
     * The name of the relationship.
     *
     * @var string
     */
    protected $relation;

    /**
     * Create a new has many relationship instance.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Builder  $query
     * @param  \Vinelab\NeoEloquent\Eloquent\Model  $parent
     * @param  string  $type
     * @return void
     */
    public function __construct(Builder $query, Model $parent, $type, $key, $relation)
    {
        $this->localKey = $key;
        $this->relation = $relation;
        $this->type = $this->foreignKey = $type;

        parent::__construct($query, $parent, $type, $key);
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
     * Get an instance of the Edge[In, Out, etc.] relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @param  array         $attributes
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In,Out, etc.]
     */
    abstract public function getEdge(EloquentModel $model = null, $attributes = array());

    /**
     * Get the edge between the parent model and the given model or
     * the related model determined by the relation function name.
     *
     * @param  \Illuminate\Database\Eloquent\Model $model
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In,Out, etc.]
     */
    public function edge(Model $model = null)
    {
        return $this->getEdge($model)->current();
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array   $models
     * @param  \Illuminate\Database\Eloquent\Collection  $results
     * @param  string  $relation
     * @return array
     */
    public function matchOneOrMany(array $models, Collection $results, $relation, $type)
    {
        // We will need the parent node placeholder so that we use it to extract related results.
        $parent = $this->query->getQuery()->modelAsNode($this->parent->getTable());

        /**
         * Looping into all the parents to match back onto their children using
         * the primary key to map them onto the correct instances, every single
         * result will be having both instances at each Collection item, held by their
         * node placeholder.
         */
        foreach ($models as $model)
        {
            $matched = $results->filter(function($result) use($parent, $relation, $model)
            {
                if ($result[$parent] instanceof Model)
                {
                    return $model->getKey() == $result[$parent]->getKey();
                }
            });

            // Now that we have the matched parents we know where to add the relations.
            // Sometimes we have more than a match so we gotta catch them all!
            foreach ($matched as $match)
            {
                if ($type == 'many')
                {
                    $collection = $model->getRelation($relation);
                    $collection->push($match[$relation]);
                    $model->setRelation($relation, $collection);

                } else
                {
                    $model->setRelation($relation, $match[$relation]);
                }
            }
        }

        return $models;
    }

    /**
     * Attach a model instance to the parent model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array $properties The relationship properites
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In, Out, etc.]
     */
    public function save(EloquentModel $model, array $properties = array())
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
     * @param  array  $models
     * @param  arra   $properties The relationship properties
     * @return array
     */
    public function saveMany(array $models, array $properties = array())
    {
        // We will collect the edges returned by save() in an Eloquent Database Collection
        // and return them when done.
        $edges = new Collection;

        foreach ($models as $model)
        {
            $edges->push($this->save($model, $properties));
        }

        return $edges;
    }

    /**
     * Create a new instance of the related model.
     *
     * @param  array  $attributes
     * @param  array   $properties The relationship properites
     * @return \Vinelab\NeoEloquent\Eloquent\Model
     */
    public function create(array $attributes, array $properties = array())
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
     * @param  array  $records
     * @param  array   $properties The relationship properites
     * @return array
     */
    public function createMany(array $records, array $properties = array())
    {
        $instances = new Collection;

        foreach ($records as $record)
        {
            $instances->push($this->create($record, $properties));
        }

        return $instances;
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints)
        {
            /**
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
            $parentNode = $this->query->getQuery()->modelAsNode($this->parent->getTable());
            // Tell the query that we only need the related model returned.
            $this->query->select($this->relation);
            // Set the parent node's placeholder as the RETURN key.
            $this->query->getQuery()->from = array($parentNode);
            // Build the MATCH ()-[]->() Cypher clause.
            $this->query->matchOut($this->parent, $this->related, $this->relation, $this->foreignKey, $this->localKey, $this->parent->{$this->localKey});
            // Add WHERE clause over the parent node's matching key = value.
            $this->query->where($this->localKey, '=', $this->parent->{$this->localKey});
        }
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
}
