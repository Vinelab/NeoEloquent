<?php namespace Vinelab\NeoEloquent\Eloquent\Edges;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\Builder;

abstract class Relation {

    /**
     * The database connection.
     *
     * @var \Vinelab\NeoEloquent\Connection
     */
    protected $connection;

    /**
     * The database client.
     *
     * @var \Everyman\Neo4j\Client
     */
    protected $client;

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
     * The start Node of the relationship,
     * represented by the $parent Model.
     *
     * @var \Everyman\Neo4j\Node
     */
    protected $start;

    /**
     * The end Node of the relationship,
     * represented by the $related Model.
     *
     * @var \Everyman\Neo4j\Node
     */
    protected $end;

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
     * @var boolean
     */
    protected $unique = false;

    /**
     * The relationship instance.
     *
     * @var \Everyman\Neo4j\
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
     * Create a new Relation instance.
     *
     * @param Builder $query
     * @param Model   $parent
     * @param Model   $related
     * @param string  $type
     */
    public function __construct(Builder $query, Model $parent, Model $related, $type, $attributes = array(), $unique = false)
    {
        $this->type       = $type;
        $this->parent     = $parent;
        $this->related    = $related;
        $this->unique     = $unique;
        $this->attributes = $attributes;

        $this->connection = $parent->getConnection();
        $this->client = $this->connection->getClient();

        $this->initRelation();
    }

    /**
     * Initialize the relationship setting the start node,
     * end node and relation type.
     *
     * @return void
     */
    abstract public function initRelation();

    /**
     * Save the relationship to the database.
     *
     * @return boolean
     */
    public function save()
    {
        $this->updateTimestamps();

        // Go through the properties and assign them
        // to the relation.
        foreach ($this->toArray() as $key => $value)
        {
            $this->relation->setProperty($key, $value);
        }

        $saved = $this->relation->save();

        return  $saved ? true : false;
    }

    /**
     * Remove the relationship from the database.
     *
     * @return  boolean
     */
    public function delete()
    {
        if ( ! is_null($this->relation))
        {
            $deleted = $this->relation->delete();

            return $deleted ? true : false;
        }

        return false;
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
     * Get the NeoEloquent connection for this relation.
     *
     * @return \Vinelab\NeoEloquent\Connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set a given attribute on the relation.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->getDates()))
        {
            if ($value)
            {
                $value = $this->fromDateTime($value);
            }
        }

        $this->attributes[$key] = $value;
    }

    /**
     * Get an attribute from the relation.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes))
        {
            $value = $this->attributes[$key];

            if (in_array($key, $this->getDates()))
            {
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
     * Get all the attributes of this relation
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
     * @return boolean
     */
    public function isUnique()
    {
        return $this->unique;
    }

    /**
     * Determine whether this relation exists.
     *
     * @return boolean
     */
    public function exists()
    {
        if ($this->relation and ! is_null($this->relation->getId()))
        {
            return true;
        }

        return false;
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
     * @param  \DateTime|int  $value
     * @return string
     */
    public function fromDateTime($value)
    {
        $format = $this->getDateFormat();

        // If the value is already a DateTime instance, we will just skip the rest of
        // these checks since they will be a waste of time, and hinder performance
        // when checking the field. We will just return the DateTime right away.
        if ($value instanceof DateTime)
        {
            //
        }

        // If the value is totally numeric, we will assume it is a UNIX timestamp and
        // format the date as such. Once we have the date in DateTime form we will
        // format it according to the proper format for the database connection.
        elseif (is_numeric($value))
        {
            $value = Carbon::createFromTimestamp($value);
        }

        // If the value is in simple year, month, day format, we will format it using
        // that setup. This is for simple "date" fields which do not have hours on
        // the field. This conveniently picks up those dates and format correct.
        elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value))
        {
            $value = Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        // If this value is some other type of string, we'll create the DateTime with
        // the format used by the database connection. Once we get the instance we
        // can return back the finally formatted DateTime instances to the devs.
        elseif ( ! $value instanceof DateTime)
        {
            $value = Carbon::createFromFormat($format, $value);
        }

        return $value->format($format);
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed  $value
     * @return \Carbon\Carbon
     */
    protected function asDateTime($value)
    {
        // If this value is an integer, we will assume it is a UNIX timestamp's value
        // and format a Carbon object from this timestamp. This allows flexibility
        // when defining your date fields as they might be UNIX timestamps here.
        if (is_numeric($value))
        {
            return Carbon::createFromTimestamp($value);
        }

        // If the value is in simply year, month, day format, we will instantiate the
        // Carbon instances from that format. Again, this provides for simple date
        // fields on the database, while still supporting Carbonized conversion.
        elseif (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value))
        {
            return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        // Finally, we will just assume this date is in the format used by default on
        // the database connection and use that format to create the Carbon object
        // that is returned back out to the developers after we convert it here.
        elseif ( ! $value instanceof DateTime)
        {
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
     * Convert a model to a Node object.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $model
     * @return \Everyman\Neo4j\Node
     */
    public function asNode(Model $model)
    {
        $node = $this->client->makeNode();

        // If the key name of the model is 'id' we will need to set it properly with setId()
        // since setting it as a regular property with setProperty() won't cut it.
        if ($model->getKeyName() == 'id')
        {
            $node->setId($model->getKey());
        }

        // In this case the dev has chosen a different primary key
        // so we use it insetead.
        else
        {
            $node->setProperty($model->getKeyName(), $model->getKey());
        }

        return $node;
    }

    /**
     * Get the current connection name for the model.
     *
     * @return string
     */
    public function getConnectionName()
    {
        return $this->connection;
    }

    /**
     * Set the connection associated with the model.
     *
     * @param  string  $name
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function setConnection($name)
    {
        $this->connection = $name;

        return $this;
    }

    /**
     * Update the creation and update timestamps.
     *
     * @return void
     */
    protected function updateTimestamps()
    {
        if ($this->parent->timestamps)
        {
            $time = $this->freshTimestamp();

            $this->setUpdatedAt($time);

            if ( ! $this->exists())
            {
                $this->setCreatedAt($time);
            }
        }
    }

    /**
     * Set the value of the "created at" attribute.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setCreatedAt($value)
    {
        $this->{static::CREATED_AT} = $value;
    }

    /**
     * Set the value of the "updated at" attribute.
     *
     * @param  mixed  $value
     * @return void
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
        return new Carbon;
    }

    /**
     * Dynamically set attributes on the relation.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Dynamically retrieve attributes on the relation.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

}
