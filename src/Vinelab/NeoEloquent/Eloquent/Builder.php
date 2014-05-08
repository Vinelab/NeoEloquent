<?php namespace Vinelab\NeoEloquent\Eloquent;

use Everyman\Neo4j\Node;
use Everyman\Neo4j\Query\Row;
use Illuminate\Database\Eloquent\Builder as IlluminateBuilder;

class Builder extends IlluminateBuilder {

    /**
	 * Find a model by its primary key.
	 *
	 * @param  mixed  $id
	 * @param  array  $columns
	 * @return \Illuminate\Database\Eloquent\Model|static|null
	 */
	public function find($id, $columns = array('*'))
	{
		if (is_array($id))
		{
		    return $this->findMany($id, $columns);
		}

		$this->query->where($this->model->getKeyName() . '(n)', '=', $id);

		return $this->first($columns);
	}

    	/**
	 * Get the hydrated models without eager loading.
	 *
	 * @param  array  $columns
	 * @return array|static[]
	 */
	public function getModels($columns = array('*'))
	{
		// First, we will simply get the raw results from the query builders which we
		// can use to populate an array with Eloquent models. We will pass columns
		// that should be selected as well, which are typically just everything.
		$results = $this->query->get($columns);

		$connection = $this->model->getConnectionName();

		$models = array();

		// Once we have the results, we can spin through them and instantiate a fresh
		// model instance for each records we retrieved from the database. We will
		// also set the proper connection name for the model after we create it.
        if ($results->valid())
        {
    		foreach ($results as $result)
    		{
                $attributes = $this->getProperties($result);

    			$models[] = $model = $this->model->newFromBuilder($attributes);

    			$model->setConnection($connection);
    		}
        }

		return $models;
	}

    /**
     * Get the properties (attribtues in Eloquent terms)
     * out of a result row.
     *
     * @param \Everyman\Neo4j\Query\Row $row
     * @param  array $columns
     * @return array
     */
    public function getProperties(Row $row)
    {
        $attributes = array();

        $columns = $this->query->columns;

        // What we get returned from the client is a result set
        // and each result is either a Node or a single column value
        // so we first extract the returned value and retrieve
        // the attributes according to the result type.
        $result = $row->current();

        if ($result instanceOf Node)
        {
            // Extract the properties of the node
            $attributes = $result->getProperties();

            // Add the node id to the attributes since \Everyman\Neo4j\Node
            // does not consider it to be a property, it is treated differently
            // and available through the getId() method.
            $attributes[$this->model->getKeyName()] = $result->getId();

        } else {

            // You must have chosen certain properties (columns) to be returned
            // which means that we should map the values to their corresponding keys.
            foreach ($columns as $property)
            {
                // as already assigned, RETURNed props will be preceded by an 'n.'
                // representing the node we're targeting.
                $returned = "n.{$property}";

                $attributes[$property] = $row[$returned];
            }

            // If the node id is in the columns we need to treat it differently
            // since Neo4j's convenience with node ids will be retrieved as id(n)
            // instead of n.id.

            // WARNING: Do this after setting all the attributes to avoid overriding it
            // with a null value or colliding it with something else, some Daenerys dragons maybe ?!
            if (in_array('id', $columns))
            {
                $attributes['id'] = $row['id(n)'];
            }

        }

        return $attributes;
    }
}
