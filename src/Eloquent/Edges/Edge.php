<?php

namespace Vinelab\NeoEloquent\Eloquent\Edges;

use DateTime;
use Carbon\Carbon;
use GraphAware\Neo4j\Client\Formatter\Result;
use GraphAware\Common\Result\RecordViewInterface;
use GraphAware\Bolt\Result\Type\Relationship;
use Laudis\Neo4j\Types\CypherMap;
use Laudis\Neo4j\Types\Node;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Collection;
use Vinelab\NeoEloquent\Exceptions\NoEdgeDirectionException;
use Vinelab\NeoEloquent\Traits\ResultTrait;

abstract class Edge extends Delegate
{
    use ResultTrait;

    /**
     * The edges finder instance.
     *
     * @var \Vinelab\NeoEloquent\Eloquent\Edges\Finder
     */
    protected $finder;

    /**
     * The start node of the relationship.
     *
     * @var Node
     */
    protected $start;
    /**
     * The end node of the relationship.
     *
     * @var Node
     */
    protected $end;

    /**
     * The left side Model of the relationship.
     *
     * @var \Vinelab\NeoEloquent\Eloquent\Model
     */
    protected $parent;

    /**
     * The right side Model of the relationship.
     *
     * @var \Vinelab\NeoEloquent\Eloquent\Model
     */
    protected $related;

    /**
     * The relationship type.
     *
     * @var string
     */
    protected $type;

    /**
     * Relations can also have attributes.
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = array();

    /**
     * Holds the decision on whether
     * this relation is unique or
     * there can be many of it.
     *
     * @var bool
     */
    protected $unique = false;

    /**
     * The primary key that is used to
     * identify the relationship.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The relationship instance.
     *
     * @var \Laudis\Neo4j\Types\Relationship
     */
    protected $relation;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'updated_at';

    /**
     * The direction of this relation,
     * this flag will be used to determine
     * which model will be the start node
     * and which is the end node.
     *
     * Possible values are: in, out.
     *
     * WARNING: Every inheriting class must set this value
     *     or it will throw a NoEdgeDirectionException
     *
     * @var string
     */
    protected $direction;

    /**
     * Create a new Relation instance.
     *
     * @param \Vinelab\NeoEloquent\Eloquent\Builder $query
     * @param \Vinelab\NeoEloquent\Eloquent\Model   $parent
     * @param \Vinelab\NeoEloquent\Eloquent\Model   $related
     * @param string                                $type
     */
    public function __construct(Builder $query, Model $parent, Model $related, $type, $attributes = array(), $unique = false)
    {
        parent::__construct($query);

        $this->type = $type;
        $this->parent = $parent;
        $this->related = $related;
        $this->unique = $unique;
        $this->attributes = $attributes;
        $this->finder = $this->newFinder();

        $this->initRelation();
    }

    /**
     * Initialize the relationship setting the start node,
     * end node and relation type.
     *
     * @throws \Vinelab\NeoEloquent\Exceptions\NoEdgeDirectionException If $direction is not set on the inheriting relation.
     */
    public function initRelation()
    {
        $this->updateTimestamps();

        switch ($this->direction) {
            case 'in':
                // Make them nodes
                $this->start = $this->asNode($this->related);
                if ($this->parent->getKey()) {
                    $this->end = $this->asNode($this->parent);
                }
                // Setup relationship
//                $this->relation = $this->makeRelationship($this->type, $this->related, $this->parent, $this->attributes);
                break;

            case 'out':
                // Make them nodes
                $this->start = $this->asNode($this->parent);
                if ($this->related->getKey()) {
                    $this->end = $this->asNode($this->related);
                }
                // Setup relationship
//                $this->relation = $this->makeRelationship($this->type, $this->parent, $this->related, $this->attributes);
                break;

            default:
                throw new NoEdgeDirectionException();
            break;
        }
    }

    /**
     * Get the direct relationship between
     * the currently set models ($parent and $related).
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Edge[In|Out]
     */
    public function current()
    {
        $results = $this->finder->firstRelationWithNodes($this->parent, $this->related, $this->type, $this->direction);

        return !$results->isEmpty() ? $this->newFromRelation($results->first()) : null;
    }

    /**
     * Save the relationship to the database.
     *
     * @return bool
     */
    public function save()
    {
        $this->updateTimestamps();

         /*
         * If this is a unique relationship we should check for an existing
         * one of the same type and direction for the $parent node before saving
         * and delete it, unless we are updating an existing relationship.
         */
        if ($this->unique && !$this->exists()) {
            $endModel = $this->related->newInstance();
            $existing = $this->firstRelationWithNodes($this->parent, $endModel, $this->type, $this->direction);

            if(!$existing->isEmpty()) {
                $instance = $this->newFromRelation($existing->first());
                $instance->delete();
            }
        }

        $saved = $this->saveRelationship($this->type, $this->parent, $this->related, $this->attributes);

        if ($saved) {
            // Let's refresh the relation we alreay have set so that
            // we make sure that it is totally in sync with the saved one.
            // at this point $saved is an instance of GraphAware\Common\Result\RecordViewInterface
            // that only contains the relationship as a record.
            // We will pull that out of the Result instance
            $this->setRelation($saved);

            return true;
        }

        return  false;
    }

    /**
     * @param string $type
     * @param Model $start
     * @param Model $end
     * @param array $properties
     */
    public function saveRelationship($type, $start, $end, $properties): CypherMap
    {
        $grammar = $this->query->getQuery()->getGrammar();
        $attributes = $this->getRelationshipAttributes($start, $end, $properties);
        $query = $grammar->compileCreateRelationship($this->query->getQuery(), $attributes);

        return $this->connection->statement($query, [], true)->first();
    }

    /**
     * Remove the relationship from the database.
     *
     * @return bool
     */
    public function delete()
    {
        if ($this->relation) {
            $grammar = $this->query->getQuery()->getGrammar();

            // based on the direction, the matching between the parent model and the relation's start node
            // are the inverse, same goes for the end node and the related model.
            $startNode = $this->start;
            $endNode = $this->end;
            // this case applies only when it's an inbound relationship.
            if ($this->direction === 'in') {
                $startNode = $this->end;
                $endNode = $this->start;
            }

            $startModel = $this->query->newModelFromNode($startNode, $this->parent);
            $endModel = $this->query->newModelFromNode($endNode, $this->related);

            // we need to delete any relationship b/w the start and end models
            // so we only need the label out of the end model and not the ID.
            $attributes = $this->getRelationshipAttributes($startModel, $endModel);
            $query = $grammar->compileDeleteRelationship($this->query->getQuery(), $attributes);

            $deleted = $this->connection->affectingStatement($query, []);
        }

        return (bool) (isset($deleted)) ? true : false;
    }

    /**
     * Create a new Relation of the current instance
     * from an existing database relation.
     *
     * @param \GraphAware\Neo4j\Client\Formatter\Result $results
     *
     * @return static
     */
    public function newFromRelation(CypherMap $record)
    {
        $instance = new static($this->query, $this->parent, $this->related, $this->type, $this->attributes, $this->unique);

        $instance->setRelation($record);

        return $instance;
    }

    /**
     * Get the Neo4j relationship object.
     *
     * @return \Everyman\Neo4j\Relationship
     */
    public function getReal()
    {
        return $this->relation;
    }

    /**
     * Get the value of the relation's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Set a given relationship on this relation.
     */
    public function setRelation(CypherMap $record)
    {
        $nodes = $this->getRecordNodes($record);
        $relationships = $this->getRecordRelationships($record);
        $relation = reset($relationships);

        // Set the relation object.
        $this->relation = $relation;

        // Replace the attributes with those brought from the given relation.
        $this->attributes = $relation->getProperties()->toArray();
        $this->setAttribute($this->primaryKey, $relation->getId());

        // Set the start and end nodes.
        // FIXME: See if we will need $this->start and $this->end for they've been removed.
        $this->start = $this->getNodeByType($relation, $nodes, 'start');
        $this->end = $this->getNodeByType($relation, $nodes, 'end');

        $relatedNode = ($this->isDirectionOut()) ? $this->end : $this->start;
        $attributes = array_merge(['id' => $relatedNode->getId()], $relatedNode->getProperties()->toArray());

        $this->related = $this->related->newFromBuilder($attributes);
        $this->related->setConnection($this->related->getConnectionName());

//        $this->start = $relation->getStartNode();
//        $this->end = $relation->getEndNode();
//
//        // Instantiate and fill out the related model.
//        $relatedNode = ($this->isDirectionOut()) ? $this->end : $this->start;
//        $attributes = array_merge(['id' => $relatedNode->getId()], $relatedNode->getProperties());
//
//        // This is an existing relationship.
//        $this->related = $this->related->newFromBuilder($attributes);
//        $this->related->setConnection($this->related->getConnectionName());
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param array $attributes
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Edges\Edge[In|Out]|static
     */
    public function fill(array $properties)
    {
        foreach ($properties as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Set a given attribute on the relation.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->getDates())) {
            if ($value) {
                $value = $this->fromDateTime($value);
            }
        }

        $this->attributes[$key] = $value;
    }

    /**
     * Get an attribute from the relation.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            $value = $this->attributes[$key];

            if (in_array($key, $this->getDates())) {
                return $this->asDateTime($value);
            }

            return $value;
        }
    }

    /**
     * Get the attributes that should be converted to dates.
     *
     * @return array
     */
    public function getDates()
    {
        $defaults = array(static::CREATED_AT, static::UPDATED_AT);

        return array_merge($this->dates, $defaults);
    }

    /**
     * Set all the attributes of this relation.
     *
     * @param array $attributes
     */
    public function setRawAttributes(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get all the attributes of this relation.
     *
     * @return mixed
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Get the Models of this relation.
     *
     * @return \Illuminate\Database\Collection
     */
    public function getModels()
    {
        return new Collection(array($this->parent, $this->related));
    }

    /**
     * Just a convenient method to get
     * the parent model of this relation.
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Model
     */
    public function parent()
    {
        return $this->getParent();
    }

    /**
     * Get the parent model of this relation.
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Model
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Just a convenient function to get
     * the related Model of this relation.
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Model
     */
    public function related()
    {
        return $this->getRelated();
    }

    /**
     * Get the parent model of this relation.
     *
     * @return \Vinelab\NeoEloquent\Eloquent\Model
     */
    public function getRelated()
    {
        return $this->related;
    }

    /**
     * Get the Nodes of this relation.
     *
     * @return \Illuminate\Database\Collection
     */
    public function getNodes()
    {
        return new Collection(array($this->start, $this->end));
    }

    /**
     * Determine whether this relationship is unique.
     *
     * @return bool
     */
    public function isUnique()
    {
        return $this->unique;
    }

    /**
     * Determine whether this relation exists.
     *
     * @return bool
     */
    public function exists()
    {
        $exists = false;

        if ($this->relation) {
            $exists = true;
        }

        return $exists;
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    protected function getDateFormat()
    {
        return $this->getConnection()->getQueryGrammar()->getDateFormat();
    }

    /**
     * Convert a DateTime to a storable string.
     *
     * @param \DateTime|int $value
     *
     * @return string
     */
    public function fromDateTime($value)
    {
        $format = $this->getDateFormat();

        // If the value is already a DateTime instance, we will just skip the rest of
        // these checks since they will be a waste of time, and hinder performance
        // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTime) {
            //
        }

        // If the value is totally numeric, we will assume it is a UNIX timestamp and
        // format the date as such. Once we have the date in DateTime form we will
        // format it according to the proper format for the database connection.
        elseif (is_numeric($value)) {
            $value = Carbon::createFromTimestamp($value);
        }

        // If the value is in simple year, month, day format, we will format it using
        // that setup. This is for simple "date" fields which do not have hours on
        // the field. This conveniently picks up those dates and format correct.
        elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            $value = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        // If this value is some other type of string, we'll create the DateTime with
        // the format used by the database connection. Once we get the instance we
        // can return back the finally formatted DateTime instances to the devs.
        elseif (!$value instanceof DateTime) {
            $value = Carbon::createFromFormat($format, $value);
        }

        return $value->format($format);
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param mixed $value
     *
     * @return \Carbon\Carbon
     */
    protected function asDateTime($value)
    {
        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        elseif (!$value instanceof DateTime) {
            $format = $this->getDateFormat();

            return Carbon::createFromFormat($format, $value);
        }

        return Carbon::instance($value);
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return (array) $this->attributes;
    }

    /**
     * Get the left node of the relationship.
     *
     * @return \Everyman\Neo4j\Node
     */
    public function getStartNode()
    {
        return $this->start;
    }

    /**
     * Get the end Node of the relationship.
     *
     * @return \Everyman\Neo4j\Node
     */
    public function getEndNode()
    {
        return $this->end;
    }

    /**
     * Update the creation and update timestamps.
     */
    protected function updateTimestamps()
    {
        if ($this->parent->timestamps) {
            $time = $this->freshTimestamp();

            $this->setUpdatedAt($time);

            if (!$this->exists()) {
                $this->setCreatedAt($time);
            }
        }
    }

    /**
     * Set the value of the "created at" attribute.
     *
     * @param mixed $value
     */
    public function setCreatedAt($value)
    {
        $this->{static::CREATED_AT} = $value;
    }

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param mixed $value
     */
    public function setUpdatedAt($value)
    {
        $this->{static::UPDATED_AT} = $value;
    }

    /**
     * Get a fresh timestamp for the model.
     *
     * @return \Carbon\Carbon
     */
    public function freshTimestamp()
    {
        return new Carbon();
    }

    /**
     * Determine whether the direction of the relationship is 'out'.
     *
     * @return bool
     */
    public function isDirectionOut()
    {
        return $this->direction == 'out';
    }

    /**
     * Determine whether the direction of the relationship is 'in'.
     *
     * @return bool [description]
     */
    public function isDirectionIn()
    {
        return $this->direction == 'in';
    }

    /**
     * Determine whether the direction of the relationship is 'any'.
     *
     * @return bool
     */
    public function isDirectionAny()
    {
        return $this->direction == 'any';
    }

    /**
     * Dynamically set attributes on the relation.
     *
     * @param string $key
     * @param mixed  $value
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Dynamically retrieve attributes on the relation.
     *
     * @param string $key
     *
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }
}
