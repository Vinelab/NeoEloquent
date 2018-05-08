<?php namespace Vinelab\NeoEloquent\Query\Grammars;

use DateTime;
use Carbon\Carbon;
use Illuminate\Database\Query\Grammars\Grammar as IlluminateGrammar;

class Grammar extends IlluminateGrammar {

    /**
     * The Query builder instance.
     *
     * @var Vinelab\NeoEloquent\Query\Builder
     */
    protected $query;

    protected $labelPostfix = '_neoeloquent_';

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
        // a placeholder so we transform it to the id replacement instead.

        // When coming from a WHERE statement we'll have to pluck out the column
        // from the collected attributes.
        if(is_array($value) && isset($value['binding']))
        {
            $value = $value['binding'];
        }
        elseif (is_array($value) && isset($value['column']))
        {
            $value = $value['column'];
        }
        elseif ($this->isExpression($value))
        {
            $value = $this->getValue($value);
        }

        $property = $this->getIdReplacement($value);

        if (strpos($property, '.') !== false) $property = explode('.', $property)[1];

        return '{' . $property . '}';
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
        return trim(':`'. preg_replace('/^:/', '', $label) .'`');
    }

    /**
     * Prepare a relationship label.
     *
     * @param  string $relation
     * @return string
     */
    public function prepareRelation($relation, $related)
    {
        return "`rel_". mb_strtolower($relation) .'_'. $related ."`:`{$relation}`";
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
        return mb_strtolower(str_replace(':', '_', preg_replace('/^:/', '', $labels)));
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param  string  $value
     * @return string
     */
    public function wrap($value, $prefixAlias = false)
    {
        // We will only wrap the value unless it has parentheses
        // in it which is the case where we're matching a node by id, or an *
        // and last whether this is a pre-formatted key.
        if (preg_match('/[(|)]/', $value) || $value == '*' || strpos($value, '.') !== false) return $value;

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
    public function valufy($values)
    {
        // we'll only deal with arrays so let's turn it into one if it isn't
        if ( ! is_array($values)) $values = [$values];

        // escape and wrap them with a quote.
        $values = array_map(function ($value)
        {
            // First, we check whether we have a date instance so that
            // we take its string representation instead.
            if ($value instanceof DateTime || $value instanceof Carbon)
            {
                $value = $value->format($this->getDateFormat());
            }

            // We need to keep the data type of values
            // except when they're strings, we need to
            // escape wrap them.
            if (is_string($value))
            {
                $value = "'" . addslashes($value) . "'";
            }
            // In order to support boolean value types and not have PHP convert them to their
            // corresponding string values, we'll have to handle boolean values and add their literal string representation.
            elseif (is_bool($value))
            {
                $value = ($value) ? 'true' : 'false';
            }

            return $value;

        }, $values);

        // stringify them.
        return implode(', ', $values);
    }

    /**
     * Get a model's name as a Node placeholder
     *
     * Downcases first letter in labels - i.e. in "MATCH (user:`User`)"... "user"
     * if $relation != null then 'with_' is appended to labels
     *
     * @param  string|array $labels The labels we're choosing from
     * @param  boolean $relation Tells whether this is a related node so that we append a 'with_' to label.
     * @return string
     */
    public function modelAsNode($labels = null, $relation = null)
    {
        if (is_null($labels))
        {
            return 'n';
        } elseif (is_array($labels))
        {
            $labels = reset($labels);
        }

        // When this is a related node we'll just prepend it with 'with_' that way we avoid
        // clashing node models in the cases like using recursive model relations.
        // @see https://github.com/Vinelab/NeoEloquent/issues/7
        if ( ! is_null($relation)) $labels = 'with_'. $relation .'_'. $labels;

        // patch to fix bug 49.  this downcases only first letter of label which is
        // compatible with how labels are recased in the rest of the library
        // @see https://github.com/Vinelab/NeoEloquent/issues/49
        if (is_array($labels)) {
            foreach ($labels as $label) {
                $firstChar = substr($label, 0, 1);
                $suffix = substr($label, 1, strlen($label) - 1);
                $label = mb_strtolower($firstChar) . $suffix;
            }
        } else {
            $firstChar = substr($labels, 0, 1);
            $suffix = substr($labels, 1, strlen($labels) - 1);
            $labels = mb_strtolower($firstChar) . $suffix;
        }
        return $labels;
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

    /**
     * Get the replacement of an id property.
     *
     * @return string
     */
    public function getIdReplacement($column)
    {
        // If we have id(n) we're removing () and keeping idn
        $column = preg_replace('/[(|)]/', '', $column);
        // Check whether the column is still id so that we transform it to the form id(n) and then
        // recursively calling ourself to reformat accordingly.
        if($column == 'id')
        {
            $from = ( ! is_null($this->query)) ? $this->query->from : null;
            $column = $this->getIdReplacement('id('. $this->modelAsNode($from) .')');
        }
        // When it's a form of node.attribute we'll just remove the '.' so that
        // we get a consistent form of binding key/value pairs.
        elseif (strpos($column, '.'))
        {
            return str_replace('.', '', $column);
        }

        return $column;
    }

    /**
     * Prepare properties and values to be injected in a query.
     *
     * @param  array $values
     * @return string
     */
    protected function prepareEntities(array $entities)
    {
        return implode(', ', array_map([$this, 'prepareEntity'], $entities));
    }

    /**
     * Prepare an entity's values to be used in a query, performs sanitization and reformatting.
     *
     * @param  array $entity
     * @return string
     */
    protected function prepareEntity($entity, $identifier = false)
    {
        $label = (is_array($entity['label'])) ? $this->prepareLabels($entity['label']) : $entity['label'];

        if ($identifier)
        {
            // when the $identifier is used as a flag, we'll take care of generating it.
            if ($identifier === true) $identifier = $this->modelAsNode($entity['label']);

            $label = $identifier.$label;
        }

        $bindings = $entity['bindings'];

        $properties = [];
        foreach ($bindings as $key => $value)
        {
            // From the Neo4j docs:
            //  "NULL is not a valid property value. NULLs can instead be modeled by the absence of a key."
            // So we'll just ignore null keys if they occur.
            if (is_null($value)) continue;

            $key   = $this->propertize($key);
            $value = $this->valufy($value);
            $properties[] = "$key: $value";
        }

        return "($label { ". implode(', ', $properties) .'})';
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
     * Get the unique identifier for the given label.
     *
     * @param  array   $label  The normalized label(s)
     * @param  integer $number Will be appended for uniqueness (must be handled on the client side)
     *
     * @return string
     */
    public function getLabelIdentifier(array $label)
    {
        return $this->getUniqueLabel(reset($label));
    }

    /**
     * Get a unique label for the given label.
     *
     * @param  string $label
     *
     * @return string
     */
    public function getUniqueLabel($label)
    {
        return $label.$this->labelPostfix.bin2hex(random_bytes(8));
    }

    /**
     * Crop the postfixed part of the label removes the part that
     * gets added by getUniqueLabel.
     *
     * @param  string $id
     *
     * @return string
     */
    public function cropLabelIdentifier($id)
    {
        return preg_replace('/_neoeloquent_.*/', '', $id);
    }
    
    /**
     * Prepare bindings for cleanBindings function
     *
     * @param  array $bindings
     *
     * @return array
     */
    public function prepareBindingsForDelete(array $bindings)
    {
        return $bindings;
    }
    
}
