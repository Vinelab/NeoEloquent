<?php namespace Vinelab\NeoEloquent\Schema\Grammars;

use Illuminate\Database\Schema\Grammars\Grammar as IlluminateSchemaGrammar;

class Grammar extends IlluminateSchemaGrammar
{

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
        return trim(':`'. preg_replace('/^:/', '', $label) .'`');
    }

    /**
     * Turn a string into a valid property for a query.
     *
     * @param  string $property
     * @return string
     */
    public function propertize($property)
    {
        // Sanitize the string from all characters except alpha numeric.
        return preg_replace('/[^A-Za-z0-9_]+/i', '', $property);
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
            $labels = reset($labels);
        }

        return mb_strtolower($labels);
    }

}

