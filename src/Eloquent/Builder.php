<?php namespace Vinelab\NeoEloquent\Eloquent;

use Closure;
use Everyman\Neo4j\Node;
use Everyman\Neo4j\Query\Row;
use Vinelab\NeoEloquent\Helpers;
use Everyman\Neo4j\Query\ResultSet;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\QueryException;
use Vinelab\NeoEloquent\Eloquent\Relations\HasOne;
use Vinelab\NeoEloquent\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Collection;
use Vinelab\NeoEloquent\Eloquent\Relations\OneRelation;
use Illuminate\Database\Eloquent\Builder as IlluminateBuilder;
use Illuminate\Pagination\Paginator;


class Builder extends IlluminateBuilder {

    use Concerns\QueriesRelationships;

    /**
     * The loaded models that should be transformed back
     * to Models. Sometimes we might ask for more than
     * a model in a query, a quick example is eager loading. We request
     * the relationship and return both models so that when the Node placeholder
     * is detected and found within the mutations we will try to build
     * a new instance of that model with the builder attributes.
     *
     * @var array
     */
    protected $mutations = array();

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $properties
     * @return \Illuminate\Database\Eloquent\Model|static|null|\Illuminate\Database\Eloquent\Collection
     */
    public function find($id, $properties = array('*'))
    {
      //If the ID is numeric we cast it to int.
      //otherwise we leave it as it is.
        if (is_array($id))
        {
            return $this->findMany(array_map(function($id){return is_numeric($id) ? (int) $id : $id;},$id), $properties);
        }

        if ($this->model->getKeyName() === 'id') {
            // ids are treated differently in neo4j so we have to adapt the query to them.
            $this->query->where($this->model->getKeyName() . '('. $this->query->modelAsNode() .')', '=', $id);
        } else {
            $this->query->where($this->model->getKeyName(), '=', $id);
        }

        return $this->first($properties);
    }

    /**
     * Declare identifiers to carry over to the next part of the query.
     *
     * @param  array  $parts Should be associative of the form ['value' => 'identifier']
     *                       and will be mapped to 'WITH value as identifier'
     * @return \Vinelab\NeoEloquent\Eloquent\Builder|static
     */
    public function carry(array $parts)
    {
        $this->query->with($parts);

        return $this;
    }

    /**
     * Get the hydrated models without eager loading.
     *
     * @param  array  $properties
     * @return array|static[]
     */
    public function getModels($properties = array('*'))
    {
        // First, we will simply get the raw results from the query builders which we
        // can use to populate an array with Eloquent models. We will pass columns
        // that should be selected as well, which are typically just everything.
        $results = $this->query->get($properties);

        // Once we have the results, we can spin through them and instantiate a fresh
        // model instance for each records we retrieved from the database. We will
        // also set the proper connection name for the model after we create it.
        return $this->resultsToModels($this->model->getConnectionName(), $results, $properties);
    }

    /**
     * Turn Neo4j result set into the corresponding model
     * @param  string $connection
     * @param  \Everyman\Neo4j\Query\ResultSet $results
     * @return array
     */
    protected function resultsToModels($connection, ResultSet $results, array $columns = [])
    {
        $models = [];

        if ($results->valid())
        {
            $resultColumns = $results->getColumns();

            foreach ($results as $result)
            {
                $attributes = $this->getProperties($resultColumns, $result, $columns);

                // Now that we have the attributes, we first check for mutations
                // and if exists, we will need to mutate the attributes accordingly.
                if ($this->shouldMutate($attributes))
                {
                    $models[] = $this->mutateToOrigin($result, $attributes);
                }
                // This is a regular record that we should deal with the normal way, creating an instance
                // of the model out of the fetched attributes.
                else
                {
                    $model = $this->model->newFromBuilder($attributes);
                    $model->setConnection($connection);
                    $models[] = $model;
                }
            }
        }

        return $models;
    }

    /**
     * Turn Neo4j result set into the corresponding model with its relations
     *
     * @param  string $connection
     * @param  \Everyman\Neo4j\Query\ResultSet $results
     * @return array
     */
    protected function resultsToModelsWithRelations($connection, ResultSet $results)
    {
        $models = [];

        if ($results->valid())
        {
            $grammar = $this->getQuery()->getGrammar();
            $columns = $results->getColumns();

            foreach ($results as $result)
            {
                $attributes = $this->getProperties($columns, $result);
                // Now that we have the attributes, we first check for mutations
                // and if exists, we will need to mutate the attributes accordingly.
                if ($this->shouldMutate($attributes))
                {
                    foreach ($attributes as $identifier => $values)
                    {
                        $cropped = $grammar->cropLabelIdentifier($identifier);

                        if (! isset($models[$cropped])) $models[$cropped] = [];

                        if(isset($this->mutations[$cropped]))
                        {
                            $mutationModel = $this->getMutationModel($cropped);
                            $model = $mutationModel->newFromBuilder($values);
                            $model->setConnection($mutationModel->getConnectionName());

                            $models[$cropped][] = $model;
                        }
                    }
                }
            }
        }

        return $models;
    }

    /**
     * Mutate a result back into its original Model.
     *
     * @param  mixed $result
     * @param  array $attributes
     * @return array
     */
    public function mutateToOrigin($result, $attributes)
    {
        $mutations = [];

        // Transform mutations back to their origin
        foreach ($attributes as $mutation => $values)
        {
            // First we should see whether this mutation can be resolved so that
            // we take it into consideration otherwise we skip to the next iteration.
            if ( ! $this->resolvableMutation($mutation)) continue;
            // Since this mutation should be resolved by us then we check whether it is
            // a Many or One mutation.
            if ($this->isManyMutation($mutation))
            {
                return $this->mutateManyToOrigin($attributes);
            }
            // Dealing with Morphing relations requires that we determine the morph_type out of the relationship
            // and mutating back to that class.
            elseif ($this->isMorphMutation($mutation))
            {
                $mutant = $this->mutateMorphToOrigin($result, $attributes);

                if ($this->getMutation($mutation)['type'] == 'morphEager')
                {
                    $mutations[$mutation] = $mutant;
                } else {
                    $mutations = reset($mutant);
                }
            }
            // Dealing with One mutations is simply returning an associative array with the mutation
            // label being the $key and the related model is $value.
            else
            {
                $model = $this->getMutationModel($mutation)->newFromBuilder($values);
                $model->setConnection($model->getConnectionName());
                $mutations[$mutation] = $model;
            }
        }

        return $mutations;
    }

    /**
     * In the case of Many mutations we need to return an associative array having both
     * relations as a single record so that when we match them back we know which result
     * belongs to which parent node.
     *
     * @param  array $attributes
     * @return void
     */
    public function mutateManyToOrigin($attributes)
    {
        $mutations = [];

        foreach ($this->getMutations() as $label => $info)
        {
            $mutationModel = $this->getMutationModel($label);
            $model = $mutationModel->newFromBuilder($attributes[$label]);
            $model->setConnection($mutationModel->getConnectionName());
            $mutations[$label] = $model;
        }

        return $mutations;
    }

    protected function mutateMorphToOrigin($result, $attributes)
    {
        $mutations = [];

        foreach ($this->getMorphMutations() as $label => $info)
        {
            // Let's see where we should be getting the morph Class name from.
            $mutationModelProperty = $this->getMutationModel($label);
            // We need the relationship from the result since it has the mutation model property's
            // value being the model that we should mutate to as set earlier by a HyperEdge.
            // NOTE: 'r' is statically set in CypherGrammer to represent the relationship.
            // Now we have an \Everyman\Neo4j\Relationship instance that has our morph class name.
            $relationship = $result['r'];

            // Get the morph class name.
            $class = $relationship->getProperty($mutationModelProperty);
            // Create a new instance of it from builder.
            $model = (new $class)->newFromBuilder($attributes[$label]);
            // And that my friend, is our mutations model =)
            $mutations[] = $model;
        }

        return $mutations;
    }

    /**
     * Determine whether attributes are mutations
     * and should be transformed back. It is considered
     * a mutation only when the attributes' keys
     * and mutations keys match.
     *
     * @param  array  $attributes
     * @return boolean
     *
     */
    public function shouldMutate(array $attributes)
    {
        $grammar = $this->getQuery()->getGrammar();
        $attributes = array_map([$grammar, 'cropLabelIdentifier'], array_keys($attributes));
        $mutations = array_keys($this->mutations);

        $intersect = array_intersect($attributes, $mutations);

        return ! empty($intersect);
    }

    /**
     * Get the properties (attributes in Eloquent terms)
     * out of a result row.
     *
     * @param  array $columns The columns retrieved by the result
     * @param \Everyman\Neo4j\Query\Row $row
     * @param  array $columns
     * @return array
     */
    public function getProperties(array $resultColumns, Row $row, array $columns = [])
    {
        $attributes = array();

        // when no columns are specified (*) we look for them in the query instead,
        // this is a workaround to be able to override the columns expected.
        if ($columns == ['*']) {
            $columns = $this->query->columns;
        }
        // What we get returned from the client is a result set
        // and each result is either a Node or a single column value
        // so we first extract the returned value and retrieve
        // the attributes according to the result type.

        // Only when requesting a single property
        // will we extract the current() row of result.

        $current = $row->current();

        $result = ($current instanceof Node) ? $current : $row;

        if ($this->isRelationship($resultColumns))
        {
            // You must have chosen certain properties (columns) to be returned
            // which means that we should map the values to their corresponding keys.
            foreach ($resultColumns as $key => $property)
            {
                $value = $row[$property];

                if ($value instanceof Node)
                {
                    $value = $this->getNodeAttributes($value);
                } else
                {
                    // Our property should be extracted from the query columns
                    // instead of the result columns
                    $property = $columns[$key];

                    // as already assigned, RETURNed props will be preceded by an 'n.'
                    // representing the node we're targeting.
                    $returned = $this->query->modelAsNode() . ".{$property}";

                    $value = $row[$returned];
                }

                $attributes[$property] = $value;
            }

            // If the node id is in the columns we need to treat it differently
            // since Neo4j's convenience with node ids will be retrieved as id(n)
            // instead of n.id.

            // WARNING: Do this after setting all the attributes to avoid overriding it
            // with a null value or colliding it with something else, some Daenerys dragons maybe ?!
            if ( ! is_null($columns) && in_array('id', $columns))
            {
                $attributes['id'] = $row['id(' . $this->query->modelAsNode() . ')'];
            }

        } elseif ($result instanceof Node)
        {
            $attributes = $this->getNodeAttributes($result);
        } elseif ($result instanceof Row)
        {
            $attributes = $this->getRowAttributes($result, $columns, $resultColumns);
        }

        return $attributes;
    }

    /**
     * Gather the properties of a Node including its id.
     *
     * @param  \Everyman\Neo4j\Node   $node
     * @return array
     */
    public function getNodeAttributes(Node $node)
    {
        // Extract the properties of the node
        $attributes = $node->getProperties();

        // Add the node id to the attributes since \Everyman\Neo4j\Node
        // does not consider it to be a property, it is treated differently
        // and available through the getId() method.
        $attributes['id'] = $node->getId();

        return $attributes;
    }

    /**
     * Get the attributes of a result Row
     *
     * @param  \Everyman\Neo4j\Query\Row    $row
     * @param  array $columns The query columns
     * @param  array $resultColumns The result columns that can be extracted from a \Everyman\Neo4j\Query\ResultSet
     * @return array
     */
    public function getRowAttributes(Row $row, $columns, $resultColumns)
    {
        $attributes = [];

        foreach ($resultColumns as $key => $column)
        {
            $attributes[$columns[$key]] = $row[$column];
        }

        return $attributes;
    }

    /**
     * Add an INCOMING "<-" relationship MATCH to the query.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $parent       The parent model
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $related      The related model
     * @param  string                             $relationship
     * @return \Vinelab\NeoEloquent\Query\Builder|static
     */
    public function matchIn($parent, $related, $relatedNode, $relationship, $property, $value = null)
    {
        // Add a MATCH clause for a relation to the query
        $this->query->matchRelation($parent, $related, $relatedNode, $relationship, $property, $value, 'in');

        return $this;
    }

    /**
     * Add an OUTGOING "->" relationship MATCH to the query.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $parent       The parent model
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $related      The related model
     * @param  string                             $relationship
     * @return \Vinelab\NeoEloquent\Eloquent|static
     */
    public function matchOut($parent, $related, $relatedNode, $relationship, $property, $value = null)
    {
        $this->query->matchRelation($parent, $related, $relatedNode, $relationship, $property, $value, 'out');

        return $this;
    }

    /**
     * Add an outgoing morph relationship to the query,
     * a morph relationship usually ignores the end node type since it doesn't know
     * what it would be so we'll only set the start node and hope to get it right when we match it.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $parent
     * @param  string $relatedNode
     * @param  string $property
     * @param  mixed $value
     * @return \Vinelab\NeoEloquent\Eloquent|static
     */
    public function matchMorphOut($parent, $relatedNode, $property, $value = null)
    {
        $this->query->matchMorphRelation($parent, $relatedNode, $property, $value);

        return $this;
    }


    /**
     * Get a paginator only supporting simple next and previous links.
     *
     * This is more efficient on larger data-sets, etc.
     *
     * @param  int $perPage
     * @param  array $columns
     * @param string $pageName
     * @param null $page
     * @return \Illuminate\Pagination\Paginator
     * @internal param \Illuminate\Pagination\Factory $paginator
     */
    public function simplePaginate($perPage = null, $columns = array('*'), $pageName = 'page', $page = null)
    {
        $paginator = $this->query->getConnection()->getPaginator();
        $page = $paginator->getCurrentPage();
        $perPage = $perPage ?: $this->model->getPerPage();
        $this->query->skip(($page - 1) * $perPage)->take($perPage + 1);
        return new Paginator($this->get($columns), $perPage, $page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => $pageName,
        ]);
    }

    /**
     * Add a mutation to the query.
     *
     * @param string $holder
     * @param \Vinelab\NeoEloquent\Eloquent\Model|string  $model String in the case of morphs where we do not know
     *                                                           the morph model class name
     * @return  void
     */
    public function addMutation($holder, $model, $type = 'one')
    {
        $this->mutations[$holder] = [
            'type'  => $type,
            'model' => $model
        ];
    }

    /**
     * Add a mutation of the type 'many' to the query.
     *
     * @param string $holder
     * @param \Vinelab\NeoEloquent\Eloquent\Model  $model
     */
    public function addManyMutation($holder, Model $model)
    {
        $this->addMutation($holder, $model, 'many');
    }

    /**
     * Add a mutation of the type 'morph' to the query.
     *
     * @param string $holder
     * @param string $model
     */
    public function addMorphMutation($holder, $model = 'morph_type')
    {
        return $this->addMutation($holder, $model, 'morph');
    }

    /**
     * Add a mutation of the type 'morph' to the query.
     *
     * @param string $holder
     * @param string $model
     */
    public function addEagerMorphMutation($holder, $model = 'morph_type')
    {
        return $this->addMutation($holder, $model, 'morphEager');
    }

    /**
     * Determine whether a mutation is of the type 'many'.
     *
     * @param  string  $mutation
     * @return boolean
     */
    public function isManyMutation($mutation)
    {
        return isset($this->mutations[$mutation]) && $this->mutations[$mutation]['type'] === 'many';
    }

    /**
     * Determine whether this mutation is of the typ 'morph'.
     *
     * @param  string  $mutation
     * @return boolean
     */
    public function isMorphMutation($mutation)
    {
        if ( ! is_array($mutation) && isset($this->mutations[$mutation]))
        {
            $mutation = $this->getMutation($mutation);
        }

        return $mutation['type'] === 'morph' || $mutation['type'] === 'morphEager';
    }

    /**
     * Get the mutation model.
     *
     * @param  string $mutation
     * @return \Vinelab\NeoEloquent\Eloquent\Model
     */
    public function getMutationModel($mutation)
    {
        return $this->getMutation($mutation)['model'];
    }

    /**
     * Determine whether a mutation can be resolved
     * by simply checking whether it exists in the $mutations.
     *
     * @param  string $mutation
     * @return boolean
     */
    public function resolvableMutation($mutation)
    {
        return isset($this->mutations[$mutation]);
    }

    /**
     * Get the mutations.
     *
     * @return array
     */
    public function getMutations()
    {
        return $this->mutations;
    }

    /**
     * Get a single mutation.
     *
     * @param  string $mutation
     * @return array
     */
    public function getMutation($mutation)
    {
        return $this->mutations[$mutation];
    }

    /**
     * Get the mutations of type 'morph'.
     *
     * @return array
     */
    public function getMorphMutations()
    {
        return array_filter($this->getMutations(), function($mutation){ return $this->isMorphMutation($mutation); });
    }

    /**
     * Determine whether the intended result is a relationship result between nodes,
     * we can tell by the format of the requested properties, in case the requested
     * properties were in the form of 'user.name' we are pretty sure it is an attribute
     * of a node, otherwise if they're plain strings like 'user' and they're more than one then
     * the reference is assumed to be a Node placeholder rather than a property.
     *
     * @param  \Everyman\Neo4j\Query\Row $row
     * @return boolean
     */
    public function isRelationship(array $columns)
    {
        $matched = array_filter($columns, function($column)
        {
            // As soon as we find that a property does not
            // have a dot '.' in it we assume it is a relationship,
            // unless it is the id of a node which is where we look
            // at a pattern that matches id(any character here).
            if (preg_match('/^([a-zA-Z0-9-_]+\.[a-zA-Z0-9-_]+)|(id\(.*\))$/', $column)) return false;

            return true;
        });

        return  count($matched) > 1 ? true : false;
    }



    /**
     * Create a new record from the parent Model and new related records with it.
     *
     * @param  array  $attributes
     * @param  array  $relations
     * @return \Vinelab\NeoEloquent\Eloquent\Model
     */
    public function createWith(array $attributes, array $relations)
    {
        // Collect the model attributes and label in the form of ['label' => $label, 'attributes' => $attributes]
        // as expected by the Query Builder.
        $attributes = $this->prepareForCreation($this->model, $attributes);
        $model = ['label' => $this->model->getTable(), 'attributes' => $attributes];

        /**
         * Collect the related models in the following for as expected by the Query Builder:
         *
         *  [
         *       'label' => ['Permission'],
         *       'relation' => [
         *           'name' => 'photos',
         *           'type' => 'PHOTO',
         *           'direction' => 'out',
         *       ],
         *       'values' => [
         *           // A mix of models and attributes, doesn't matter really..
         *           ['url' => '', 'caption' => ''],
         *           ['url' => '', 'caption' => '']
         *       ]
         *  ]
         */
        $related = [];
        foreach ($relations as $relation => $values)
        {
            $name = $relation;
            // Get the relation by calling the model's relationship function.
            if ( ! method_exists($this->model, $relation))
                throw new QueryException("The relation method $relation() does not exist on ". get_class($this->model));

            $relationship = $this->model->$relation();
            // Bring the model from the relationship.
            $relatedModel = $relationship->getRelated();

            // We will first check to see what the dev have passed as values
            // so that we make sure that we have an array moving forward
            // In the case of a model Id or an associative array or a Model instance it means that
            // this is probably a One-To-One relationship or the dev decided not to add
            // multiple records as relations so we'll wrap it up in an array.
            if ( ! is_array($values) || Helpers::isAssocArray($values) || $values instanceof Model) $values = [$values];

            $label     = $relationship->getRelated()->getTable();
            $direction = $relationship->getEdgeDirection();
            $type      = $relationship->getRelationType();

            // Hold the models that we need to attach
            $attach = [];
            // Hold the models that we need to create
            $create = [];
            // Separate the models that needs to be attached from the ones that needs
            // to be created.
            foreach ($values as $value)
            {
                // If this is a Model then the $exists property will indicate what we need
                // so we'll add its id to be attached.
                if ($value instanceof Model && $value->exists === true)
                {
                    $attach[] = $value->getKey();
                }
                // Next we will check whether we got a Collection in so that we deal with it
                // accordingly, which guarantees sending an Eloquent result straight in would work.
                elseif ($value instanceof Collection)
                {
                    $attach = array_merge($attach, $value->pluck('id')->toArray());
                }
                // Or in the case where the attributes are neither an array nor a model instance
                // then this is assumed to be the model Id that the dev means to attach and since
                // Neo4j node Ids are always an int then we take that as a value.
                elseif ( ! is_array($value) && ! $value instanceof Model)
                {
                    $attach[] = $value;
                }
                // In this case the record is considered to be new to the market so let's create it.
                else $create[] = $this->prepareForCreation($relatedModel, $value);
            }

            $relation  = compact('name', 'type', 'direction');
            $related[] = compact('relation', 'label', 'create', 'attach');
        }

        $result = $this->query->createWith($model, $related);
        $models = $this->resultsToModelsWithRelations($this->model->getConnectionName(), $result);

        return ( ! empty($models)) ? $models : null;
    }

    /**
     * Prepare model's attributes or instance for creation in a query.
     *
     * @param  string $class
     * @param  mixed $attributes
     * @return array
     */
    protected function prepareForCreation($class, $attributes)
    {
        // We need to get the attributes of each $value from $values into
        // an instance of the related model so that we make sure that it goes
        // through the $fillable filter pipeline.

        // This adds support for having model instances mixed with values, so whenever
        // we encounter a Model we take it as our instance
        if ($attributes instanceof Model)
        {
            $instance = $attributes;
        }
        // Reaching here means the dev entered raw attributes (similar to insert())
        // so we'll need to pass the attributes through the model to make sure
        // the fillables are respected as expected by the dev.
        else
        {
            $instance = new $class($attributes);
        }
        // Update timestamps on the instance, this will only affect newly
        // created models by adding timestamps to them, otherwise it has no effect
        // on existing models.
        if ($instance->usesTimestamps()) $instance->addTimestamps();
        return $instance->toArray();
    }

    /**
     * Prefix query bindings and wheres with the relation's model Node placeholder.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Builder $query
     * @param  string  $prefix
     * @return void
     */
    protected function prefixAndMerge(Builder $query, $prefix)
    {
        $this->prefixWheres($query, $prefix);
        $this->query->mergeWheres($query->getQuery()->wheres, $query->getQuery()->getBindings());
    }

    /**
     * Prefix where clauses' columns.
     * @param  \Vinelab\NeoEloquent\Eloquent\Builder $query
     * @param  string  $prefix
     * @return void
     */
    protected function prefixWheres(Builder $query, $prefix)
    {
        if (is_array($query->getQuery()->wheres))
        {
            $query->getQuery()->wheres = array_map(function($where) use($prefix)
            {
                $column = $where['column'];
                $where['column'] = ($this->isId($column)) ? $column : $prefix .'.'. $column;
                return $where;
            }, $query->getQuery()->wheres);
        }
    }

    /**
     * Determine whether a value is an Id attribute according to Neo4j.
     *
     * @param  string  $value
     * @return boolean
     */
    public function isId($value)
    {
        return preg_match('/^id(\(.*\))?$/', $value);
    }

    /**
     * Get the match[In|Out] method name out of a relation.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Relations\* $relation
     * @return [type]
     */
    protected function getMatchMethodName($relation)
    {
        return 'match'. ucfirst(mb_strtolower($relation->getEdgeDirection()));
    }
}
