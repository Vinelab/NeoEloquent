<?php namespace Vinelab\NeoEloquent\Query\Grammars;

use Illuminate\Database\Query\Builder;

class CypherGrammar extends Grammar {

    protected $selectComponents = array(
        'from',
        'wheres',
		'unions',
        'orders',
        'columns',
		'offset',
        'limit',
    );

    /**
	 * Get the Cypher representation of the query.
	 *
	 * @return string
	 */
    public function compileSelect(Builder $query)
    {
        if (is_null($query->columns)) $query->columns = array('*');

        return trim($this->concatenate($this->compileComponents($query)));
    }


    /**
	 * Compile the components necessary for a select clause.
	 *
	 * @param  \Vinelab\NeoEloquent\Query\Builder
	 * @return array
	 */
	protected function compileComponents(Builder $query)
	{
		$cypher = array();

		foreach ($this->selectComponents as $component)
		{
            if ($component == 'return') $component = 'columns';

			// To compile the query, we'll spin through each component of the query and
			// see if that component exists. If it does we'll just call the compiler
			// function for the component which is responsible for making the SQL.
			if ( ! is_null($query->$component))
			{
				$method = 'compile'.ucfirst($component);

				$cypher[$component] = $this->$method($query, $query->$component);
			}
		}

		return $cypher;
	}

    /**
	 * Compile the "from" portion of the query
     * which in cypher represents the nodes we're MATCHing
	 *
	 * @param  \Vinelab\NeoEloquent\Query\Builder  $query
	 * @param  string  $labels
	 * @return string
	 */
    public function compileFrom(Builder $query, $labels)
    {
        // first we will check whether we need
        // to reformat the labels from an array
        if (is_array($labels))
        {
            $labels = implode(':', array_map(array($this, 'prepareLabel'), $labels));
        }

        // every label must begin with a ':' so we need to check
        // and reformat if need be.
        $labels = ':' . preg_replace('/^:/', '', $labels);

        // now we add the default placeholder for this node
        $labels = 'n' . $labels;

        return sprintf("MATCH (%s)", $labels);
    }

    	/**
	 * Compile the "where" portions of the query.
	 *
	 * @param  \Vinelab\NeoEloquent\Query\Builder  $query
	 * @return string
	 */
	protected function compileWheres(Builder $query)
	{
		$cypher = array();

		if (is_null($query->wheres)) return '';

		// Each type of where clauses has its own compiler function which is responsible
		// for actually creating the where clauses SQL. This helps keep the code nice
		// and maintainable since each clause has a very small method that it uses.
		foreach ($query->wheres as $where)
		{
			$method = "WHERE{$where['type']}";

			$cypher[] = $where['boolean'].' '.$this->$method($query, $where);
		}

		// If we actually have some where clauses, we will strip off the first boolean
		// operator, which is added by the query builders for convenience so we can
		// avoid checking for the first clauses in each of the compilers methods.
		if (count($cypher) > 0)
		{
			$cypher = implode(' ', $cypher);

			return 'WHERE '.preg_replace('/and |or /', '', $cypher, 1);
		}

		return '';
	}

    /**
	 * Compile a basic where clause.
	 *
	 * @param  \Illuminate\Database\Query\Builder  $query
	 * @param  array  $where
	 * @return string
	 */
	protected function whereBasic(Builder $query, $where)
	{
		$value = $this->parameter($where);

		return $this->wrap($where['column']).' '.$where['operator'].' '.$value;
	}

    /**
	 * Compile the "limit" portions of the query.
	 *
	 * @param  \Vinelab\NeoEloquent\Query\Builder  $query
	 * @param  int  $limit
	 * @return string
	 */
	protected function compileLimit(Builder $query, $limit)
	{
		return 'LIMIT '.(int) $limit;
	}

    /**
	 * Compile the "offset" portions of the query.
	 *
	 * @param  \Vinelab\NeoEloquent\Query\Builder  $query
	 * @param  int  $offset
	 * @return string
	 */
	protected function compileOffset(Builder $query, $offset)
	{
		return 'SKIP '.(int) $offset;
	}

    protected function compileColumns(Builder $query, $properties)
    {
        // Columnize method always expects an array so we give it that
        if ( ! is_array($properties)) $properties = array($properties);

        return 'RETURN ' . $this->columnize($properties);
    }
}
