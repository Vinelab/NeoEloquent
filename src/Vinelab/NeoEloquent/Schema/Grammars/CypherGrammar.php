<?php
namespace Vinelab\NeoEloquent\Schema\Grammars;

use Illuminate\Support\Fluent;
use Vinelab\NeoEloquent\Schema\Blueprint;

class CypherGrammar extends Grammar
{

    /**
     * Compile a drop table command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileDrop(Blueprint $blueprint, Fluent $command)
    {
        $match = $this->compileFrom($blueprint);
        $label = $this->prepareLabels(array($blueprint));

        return $match . " REMOVE n" . $label;
    }

    /**
     * Compile a drop table (if exists) command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileDropIfExists(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileDrop($blueprint, $command);
    }

    /**
     * Compile the query to determine if the label exists.
     *
     * @var string $label
     * @return string
     */
    public function compileLabelExists($label)
    {
        $match = $this->compileFrom($label);

        return $match . "  RETURN n LIMIT 1;";
    }

    /**
     * Compile the query to find the relation.
     *
     * @var string $relation
     * @return string
     */
    public function compileRelationExists($relation)
    {
        $relation = mb_strtoupper($this->prepareLabels(array($relation)));

        return "MATCH n-[r$relation]->m RETURN r LIMIT 1";
    }

    /**
     * Compile a rename label command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileRenameLabel(Blueprint $blueprint, Fluent $command)
    {
        $match = $this->compileFrom($blueprint);
        $from = $this->prepareLabels(array($blueprint));
        $to = $this->prepareLabels(array($command->to));

        return $match . " REMOVE n$from SET n$to";
    }

    /**
     * Compile a unique property command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileUnique(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileUniqueKey('CREATE', $blueprint, $command);
    }

    /**
     * Compile a index property command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileIndexKey('CREATE', $blueprint, $command);
    }


    /**
     * Compile a drop unique property command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileDropUnique(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileUniqueKey('DROP', $blueprint, $command);
    }

    /**
     * Compile a drop index property command.
     *
     * @param  Blueprint  $blueprint
     * @param  Fluent  $command
     * @return string
     */
    public function compileDropIndex(Blueprint $blueprint, Fluent $command)
    {
        return $this->compileIndexKey('DROP', $blueprint, $command);
    }

    /**
     * Compiles index operation.
     *
     * @param  string    $operation
     * @param  Blueprint $blueprint
     * @param  Fluent    $command
     * @return string
     */
    protected function compileIndexKey($operation, Blueprint $blueprint, Fluent $command)
    {
        $label = $this->wrapLabel($blueprint);
        $property = $this->propertize($command->property);

        return "$operation INDEX ON $label($property)";
    }

    /**
     * Compiles unique operation.
     *
     * @param  string    $operation
     * @param  Blueprint $blueprint
     * @param  Fluent    $command
     * @return string
     */
    protected function compileUniqueKey($operation, Blueprint $blueprint, Fluent $command)
    {
        $label = $this->wrapLabel($blueprint);
        $property = $this->propertize($command->property);

        return "$operation CONSTRAINT ON (n$label) ASSERT n.$property IS UNIQUE";
    }

    /**
     * Compile the "from" portion of the query
     * which in cypher represents the nodes we're MATCHing
     *
     * @param  string  $labels
     * @return string
     */
    public function compileFrom($labels)
    {
        // first we will check whether we need
        // to reformat the labels from an array
        if (is_array($labels))
        {
            $labels = $this->prepareLabels($labels);
        }

        // every label must begin with a ':' so we need to check
        // and reformat if need be.
        $labels = ':' . preg_replace('/^:/', '', $labels);

        // now we add the default placeholder for this node
        $labels = $this->modelAsNode() . $labels;

        return sprintf("MATCH (%s)", $labels);
    }

}
