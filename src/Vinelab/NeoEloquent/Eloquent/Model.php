<?php namespace Vinelab\NeoEloquent\Eloquent;

use Vinelab\NeoEloquent\Query\Builder as QueryBuilder;
use Vinelab\NeoEloquent\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model as IlluminateModel;

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



}
