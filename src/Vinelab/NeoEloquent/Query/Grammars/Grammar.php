<?php namespace Vinelab\NeoEloquent\Query\Grammars;

use Illuminate\Database\Query\Builder as Builder;
use Illuminate\Database\Query\Grammars\Grammar as IlluminateGrammar;

class Grammar extends IlluminateGrammar {

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
        $property = ($value['column'] == 'id(n)' or $value['column'] == 'id') ? '_nodeId' : $value['column'];

		return $this->isExpression($property) ? $this->getValue($property) : '{' . $property . '}';
	}

    /**
     * Prepare a label by formatting it as expected,
     * trim out trailing spaces and add backticks
     *
     * @var  string  $label
     * @return string
     */
    protected function prepareLabel($label)
    {
        // we do not accept any existing backticks so we remove them
        // and add them as they should be, around the label string
        return '`' . preg_replace('/`/', '', trim($label)) . '`';
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
        if ($value == 'id') return 'id(n)';

        return 'n.' . $value;
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

}
