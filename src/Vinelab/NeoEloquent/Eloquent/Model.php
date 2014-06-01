<?php namespace Vinelab\NeoEloquent\Eloquent;

use Vinelab\NeoEloquent\Eloquent\Relations\HasOne;
use Vinelab\NeoEloquent\Eloquent\Relations\HasMany;
use Vinelab\NeoEloquent\Eloquent\Relations\BelongsTo;
use Vinelab\NeoEloquent\Eloquent\Relations\BelongsToMany;
use Vinelab\NeoEloquent\Eloquent\Relations\HyperMorph;
use Vinelab\NeoEloquent\Query\Builder as QueryBuilder;
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
        $type = $type ?: $this->getForeignKey();

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

    public function belongsToMany($related, $type = null, $key = null, $relation = null)
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
            $type = strtoupper($relation);
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

}
