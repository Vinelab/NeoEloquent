<?php namespace Vinelab\NeoEloquent\Query\Grammars;

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
        $property = ($value['column'] == 'id(n)') ? '_nodeId' : $value['column'];

		return $this->isExpression($value) ? $this->getValue($value) : '{' . $property . '}';
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

}
