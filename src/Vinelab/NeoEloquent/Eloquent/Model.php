<?php namespace Vinelab\NeoEloquent\Eloquent;

use Vinelab\NeoEloquent\Eloquent\Relations\HasOne;
use Vinelab\NeoEloquent\Eloquent\Relations\HasMany;
use Vinelab\NeoEloquent\Eloquent\Relations\MorphTo;
use Vinelab\NeoEloquent\Eloquent\Relations\BelongsTo;
use Vinelab\NeoEloquent\Eloquent\Relations\HyperMorph;
use Vinelab\NeoEloquent\Query\Builder as QueryBuilder;
use Vinelab\NeoEloquent\Eloquent\Relations\MorphMany;
use Vinelab\NeoEloquent\Eloquent\Relations\MorphedByOne;
use Vinelab\NeoEloquent\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model as IlluminateModel;
use Vinelab\NeoEloquent\Eloquent\Builder as EloquentBuilder;

abstract class Model extends IlluminateModel {

    /**
     * The node label
     *
     * @var string|array
     */
    protected $label = null;

    /**
     * Set the node label for this model
     *
     * @param  string|array  $labels
     */
    public function setLabel($label)
    {
        return $this->label = $label;
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  Vinelab\NeoEloquent\Query\Builder $query
     * @return Vinelab\NeoEloquent\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new EloquentBuilder($query);
    }

    /**
	 * Get a new query builder instance for the connection.
	 *
	 * @return Vinelab\NeoEloquent\Query\Builder
	 */
	protected function newBaseQueryBuilder()
	{
		$conn = $this->getConnection();

        $grammar = $conn->getQueryGrammar();

		return new QueryBuilder($conn, $grammar);
	}

    /**
	 * Get the format for database stored dates.
	 *
	 * @return string
	 */
    protected function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Get the node labels
     *
     * @return array
     */
    public function getDefaultNodeLabel()
    {
        // by default we take the $label, otherwise we consider $table
        // for Eloquent's backward compatibility
        $label = (empty($this->label)) ? $this->table : $this->label;

        // The label is accepted as an array for a convenience so we need to
        // convert it to a string separated by ':' following Neo4j's labels
        if (is_array($label) and ! empty($label)) return $label;

        // since this is not an array, it is assumed to be a string
        // we check to see if it follows neo4j's labels naming (User:Fan)
        // and return an array exploded from the ':'
        if ( ! empty($label))
        {
            $label = array_filter(explode(':', $label));

            // This trick re-indexes the array
            array_splice($label, 0, 0);

            return $label;
        }

        // Since there was no label for this model
        // we take the fully qualified (namespaced) class name and
        // pluck out backslashes to get a clean 'WordsUp' class name and use it as default
        return array(str_replace('\\', '', get_class($this)));
    }

    /**
	 * Get the table associated with the model.
	 *
	 * @return string
	 */
	public function getTable()
	{
		return $this->getDefaultNodeLabel();
	}

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $otherKey
     * @param  string  $relation
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $otherKey = null, $relation = null)
    {
        // If no relation name was given, we will use this debug backtrace to extract
        // the calling method's name and use that as the relationship name as most
        // of the time this will be what we desire to use for the relationships.
        if (is_null($relation))
        {
            list(, $caller) = debug_backtrace(false);

            $relation = $caller['function'];
        }

        // If no foreign key was supplied, we can use a backtrace to guess the proper
        // foreign key name by using the name of the calling class, which
        // will be uppercased and used as a relationship label
        if (is_null($foreignKey))
        {
            $foreignKey = strtoupper($caller['class']);
        }

        $instance = new $related;

        // Once we have the foreign key names, we'll just create a new Eloquent query
        // for the related models and returns the relationship instance which will
        // actually be responsible for retrieving and hydrating every relations.
        $query = $instance->newQuery();

        $otherKey = $otherKey ?: $instance->getKeyName();

        return new BelongsTo($query, $this, $foreignKey, $otherKey, $relation);
    }

    /**
     * Define a one-to-one relationship.
     *
     * @param  string  $related
     * @param  string  $foreignKey
     * @param  string  $localKey
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function hasOne($related, $foreignKey = null, $otherKey = null, $relation = null)
    {
        // If no relation name was given, we will use this debug backtrace to extract
        // the calling method's name and use that as the relationship name as most
        // of the time this will be what we desire to use for the relationships.
        if (is_null($relation))
        {
            list(, $caller) = debug_backtrace(false);

            $relation = $caller['function'];
        }

        // If no foreign key was supplied, we can use a backtrace to guess the proper
        // foreign key name by using the name of the calling class, which
        // will be uppercased and used as a relationship label
        if (is_null($foreignKey))
        {
            $foreignKey = strtoupper($caller['class']);
        }

        $instance = new $related;

        // Once we have the foreign key names, we'll just create a new Eloquent query
        // for the related models and returns the relationship instance which will
        // actually be responsible for retrieving and hydrating every relations.
        $query = $instance->newQuery();

        $otherKey = $otherKey ?: $instance->getKeyName();

        return new HasOne($query, $this, $foreignKey, $otherKey, $relation);
    }

    /**
     * Define a one-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $type
     * @param  string  $key
     * @return \Vinelab\NeoEloquent\Eloquent\Relations\HasMany
     */
    public function hasMany($related, $type = null, $key = null, $relation = null)
    {
        // If no relation name was given, we will use this debug backtrace to extract
        // the calling method's name and use that as the relationship name as most
        // of the time this will be what we desire to use for the relationships.
        if (is_null($relation))
        {
            list(, $caller) = debug_backtrace(false);

            $relation = $caller['function'];
        }

        // FIXME: the $type should be the UPPERCASE of the relation not the foreign key.
        $type = $type ?: mb_strtoupper($relation);

        $instance = new $related;

        $key = $key ?: $this->getKeyName();

        return new HasMany($instance->newQuery(), $this, $type, $key, $relation);
    }

    /**
     * Define a many-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $type
     * @param  string  $key
     * @param  string  $relation
     * @return \Vinelab\NeoEloquent\Eloquent\Relations\BelongsToMany
     */
    public function belongsToMany($related, $table = null, $foreignKey = null, $otherKey = null, $relation = null)
    {
        // To escape the error:
        // PHP Strict standards:  Declaration of Vinelab\NeoEloquent\Eloquent\Model::belongsToMany() should be
        //      compatible with Illuminate\Database\Eloquent\Model::belongsToMany()
        // We'll just map them in with the variables we want.
        $type     = $table;
        $key      = $foreignKey;
        $relation = $otherKey;
        // If no relation name was given, we will use this debug backtrace to extract
        // the calling method's name and use that as the relationship name as most
        // of the time this will be what we desire to use for the relationships.
        if (is_null($relation))
        {
            list(, $caller) = debug_backtrace(false);

            $relation = $caller['function'];
        }

        // If no $key was provided we will consider it the key name of this model.
        $key = $key ?: $this->getKeyName();

        // If no relationship type was provided, we can use the previously traced back
        // $relation being the function name that called this method and using it in its
        // all uppercase form.
        if (is_null($type))
        {
            $type = mb_strtoupper($relation);
        }

        $instance = new $related;

        // Now we're ready to create a new query builder for the related model and
        // the relationship instances for the relation. The relations will set
        // appropriate query constraint and entirely manages the hydrations.
        $query = $instance->newQuery();

        return new BelongsToMany($query, $this, $type, $key, $relation);
    }

    /**
     * Create a new HyperMorph relationship.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Model  $model
     * @param  string $related
     * @param  string $type
     * @param  string $morphType
     * @param  string $relation
     * @param  string $key
     * @return \Vinelab\NeoEloquent\Eloquent\Relations\HyperMorph
     */
    public function hyperMorph($model, $related, $type = null, $morphType = null, $relation = null, $key = null)
    {
        // If no relation name was given, we will use this debug backtrace to extract
        // the calling method's name and use that as the relationship name as most
        // of the time this will be what we desire to use for the relationships.
        if (is_null($relation))
        {
            list(, $caller) = debug_backtrace(false);

            $relation = $caller['function'];
        }

        // If no $key was provided we will consider it the key name of this model.
        $key = $key ?: $this->getKeyName();

        // If no relationship type was provided, we can use the previously traced back
        // $relation being the function name that called this method and using it in its
        // all uppercase form.
        if (is_null($type))
        {
            $type = mb_strtoupper($relation);
        }

        $instance = new $related;

        // Now we're ready to create a new query builder for the related model and
        // the relationship instances for the relation. The relations will set
        // appropriate query constraint and entirely manages the hydrations.
        $query = $instance->newQuery();

        return new HyperMorph($query, $this, $model, $type, $morphType, $key, $relation);
    }

    /**
     * Define a many-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $type
     * @param  string  $key
     * @param  string  $relation
     * @return \Vinelab\NeoEloquent\Eloquent\Relations\MorphMany
     */
    public function morphMany($related, $name, $type = null, $id = null, $localKey = null)
    {
        // To escape the error:
        // Strict standards: Declaration of Vinelab\NeoEloquent\Eloquent\Model::morphMany() should be
        //          compatible with Illuminate\Database\Eloquent\Model::morphMany()
        // We'll just map them in with the variables we want.
        $relationType = $name;
        $key          = $type;
        $relation     = $id;

        // If no relation name was given, we will use this debug backtrace to extract
        // the calling method's name and use that as the relationship name as most
        // of the time this will be what we desire to use for the relationships.
        if (is_null($relation))
        {
            list(, $caller) = debug_backtrace(false);

            $relation = $caller['function'];
        }

        // If no $key was provided we will consider it the key name of this model.
        $key = $key ?: $this->getKeyName();

        // If no relationship type was provided, we can use the previously traced back
        // $relation being the function name that called this method and using it in its
        // all uppercase form.
        if (is_null($relationType))
        {
            $relationType = mb_strtoupper($relation);
        }

        $instance = new $related;

        // Now we're ready to create a new query builder for the related model and
        // the relationship instances for the relation. The relations will set
        // appropriate query constraint and entirely manages the hydrations.
        $query = $instance->newQuery();

        return new MorphMany($query, $this, $relationType, $key, $relation);
    }

    /**
     * Create an inverse one-to-one polymorphic relationship with specified model and relation.
     *
     * @param  \Vinelab\NeoEloquent\Eloquent\Model $related
     * @param  string $type
     * @param  string $key
     * @param  string $relation
     * @return \Vinelab\NeoEloquent\Eloquent\Relations\MorphedByOne
     */
    public function morphedByOne($related, $type, $key = null, $relation = null)
    {
        // If no relation name was given, we will use this debug backtrace to extract
        // the calling method's name and use that as the relationship name as most
        // of the time this will be what we desire to use for the relationships.
        if (is_null($relation))
        {
            list(, $caller) = debug_backtrace(false);

            $relation = $caller['function'];
        }

        // If no $key was provided we will consider it the key name of this model.
        $key = $key ?: $this->getKeyName();

        // If no relationship type was provided, we can use the previously traced back
        // $relation being the function name that called this method and using it in its
        // all uppercase form.
        if (is_null($type))
        {
            $type = mb_strtoupper($relation);
        }

        $instance = new $related;

        // Now we're ready to create a new query builder for the related model and
        // the relationship instances for the relation. The relations will set
        // appropriate query constraint and entirely manages the hydrations.
        $query = $instance->newQuery();

        return new MorphedByOne($query, $this, $type, $key, $relation);
    }

    /**
     * Define a polymorphic, inverse one-to-one or many relationship.
     *
     * @param  string  $name
     * @param  string  $type
     * @param  string  $id
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function morphTo($name = null, $type = null, $id = null)
    {

        // When the name and the type are specified we'll return a MorphedByOne
        // relationship with the given arguments since we know the kind of Model
        // and relationship type we're looking for.
        if ($name and $type)
        {
            // Determine the relation function name out of the back trace
            list(, $caller) = debug_backtrace(false);
            $relation = $caller['function'];
            return $this->morphedByOne($name, $type, $id, $relation);
        }

        // If no name is provided, we will use the backtrace to get the function name
        // since that is most likely the name of the polymorphic interface. We can
        // use that to get both the class and foreign key that will be utilized.
        if (is_null($name))
        {
            list(, $caller) = debug_backtrace(false);

            $name = snake_case($caller['function']);
        }

        list($type, $id) = $this->getMorphs($name, $type, $id);

        // If the type value is null it is probably safe to assume we're eager loading
        // the relationship. When that is the case we will pass in a dummy query as
        // there are multiple types in the morph and we can't use single queries.
        if (is_null($class = $this->$type))
        {
            return new MorphTo(
                $this->newQuery(), $this, $id, null, $type, $name
            );
        }

        // If we are not eager loading the relationship we will essentially treat this
        // as a belongs-to style relationship since morph-to extends that class and
        // we will pass in the appropriate values so that it behaves as expected.
        else
        {
            $instance = new $class;

            return new MorphTo(
                with($instance)->newQuery(), $this, $id, $instance->getKeyName(), $type, $name
            );
        }
    }

    public static function createWith(array $attributes, array $relations)
    {
        $query = static::query();

        $instance = new static($attributes);

        return $query->createWith($attributes, $relations);
    }
    /**
     * Get the polymorphic relationship columns.
     *
     * @param  string  $name
     * @param  string  $type
     * @param  string  $id
     * @return array
     */
    protected function getMorphs($name, $type, $id)
    {
        $type = $type ?: $name.'_type';

        $id = $this->getkeyname();

        return array($type, $id);
    }

    /**
     * Get the table qualified key name.
     *
     * @return string
     */
    public function getQualifiedKeyName()
    {
        return $this->getKeyName();
    }

    public function addTimestamps()
    {
        $this->updateTimestamps();
    }
}
