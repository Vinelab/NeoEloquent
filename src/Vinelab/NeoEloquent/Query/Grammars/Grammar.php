<?php namespace Vinelab\NeoEloquent\Query\Grammars;

use Illuminate\Database\Query\Builder as Builder;
use Illuminate\Database\Query\Grammars\Grammar as IlluminateGrammar;

class Grammar extends IlluminateGrammar {

    /**
     * The Query builder instance.
     *
     * @var Vinelab\NeoEloquent\Query\Builder
     */
    protected $query;

    /**
	 * Get the appropriate query parameter place-holder for a value.
	 *
	 * @param  mixed   $value
	 * @return string
	 */
	public function parameter($value)
	{
        // Validate whether the requested field is the
        // node id, in that case id(n) doesn't work as
        // a placeholder so we transform it to _nodeId instead
        $property = preg_match('/^id(\(.*\))?$/', $value['column']) ? '_nodeId' : $value['column'];

		return $this->isExpression($property) ? $this->getValue($property) : '{' . $property . '}';
	}

    /**
     * Prepare a label by formatting it as expected,
     * trim out trailing spaces and add backticks
     *
     * @var  string  $label
     * @return string
     */
    public function prepareLabels(array $labels)
    {
        // get the labels prepared and back to a string imploded by : they go.
        return implode('', array_map(array($this, 'wrapLabel'), $labels));
    }

    /**
     * Make sure the label is wrapped with backticks
     *
     * @param  string $label
     * @return string
     */
    public function wrapLabel($label)
    {
        // every label must begin with a ':' so we need to check
        // and reformat if need be.
        return trim(':' . preg_replace('/^:/', '', "`$label`"));
    }

    /**
     * Prepare a relationship label.
     *
     * @param  string $relation
     * @return string
     */
    public function prepareRelation($relation, $related)
    {
        return "rel_". mb_strtolower($relation) .'_'. $related .":{$relation}";
    }

    /**
     * Turn labels like this ':User:Admin'
     * into this 'user_admin'
     *
     * @param  string $labels
     * @return string
     */
    public function normalizeLabels($labels)
    {
        return mb_strtolower(str_replace(':', '_', $labels));
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    public function wrap($value)
    {
        // We will only wrap the value unless it has parentheses
        // in it which is the case where we're matching a node by id
        if (preg_match('/[(|)]/', $value) or $value == '*') return $value;

        // In the case where the developer specifies the properties and not returning
        // everything, we need to check whether the primaryKey is meant to be returned
        // since Neo4j's way of evaluating returned properties for the Node id is
        // different: id(n) instead of n.id

        if ($value == 'id')
        {
            return 'id(' . $this->query->modelAsNode() . ')';
        }

        return $this->query->modelAsNode() . '.' . $value;
    }

    /**
     * Turn an array of values into a comma separated string of values
     * that are escaped and ready to be passed as values in a query
     *
     * @param  array $values
     * @return  string
     */
    public function valufy(array $values)
    {
        // escape and wrap them with a quote.
        $values = array_map(function ($value)
        {
            // We need to keep the data type of values
            // except when they're strings, we need to
            // escape wrap them.
            if (is_string($value))
            {
                $value = "'" . addslashes($value) . "'";
            }

            return $value;

        }, $values);

        // stringify them.
        return implode(', ', $values);
    }

    /**
     * Get a model's name as a Node placeholder
     *
     * i.e. in "MATCH (user:`User`)"... "user" is what this method returns
     *
     * @param  string|array $labels The labels we're choosing from
     * @return string
     */
    public function modelAsNode($labels = null)
    {
        if (is_null($labels))
        {
            return 'n';
        } elseif (is_array($labels))
        {
            return mb_strtolower(reset($labels));
        }

        return mb_strtolower($labels);
    }

    /**
     * Set the query builder for this grammar instance.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     */
    public function setQuery($query)
    {
        $this->query = $query;
    }
}
