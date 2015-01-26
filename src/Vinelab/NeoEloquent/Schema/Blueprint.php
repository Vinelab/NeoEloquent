<?php namespace Vinelab\NeoEloquent\Schema;

use Closure;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Schema\Grammars\Grammar as IlluminateSchemaGrammar;
use Illuminate\Support\Fluent;

class Blueprint {

    /**
     * The label the blueprint describes.
     *
     * @var string
     */
    protected $label;

    /**
     * The commands that should be run for the label.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * @param  string   $label
     * @param  Closure  $callback
     * @return void
     */
    public function __construct($label, Closure $callback = null)
    {
        $this->label = $label;

        if ( ! is_null($callback))
        {
            $callback($this);
        }
    }

    /**
     * Execute the blueprint against the label.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  \Illuminate\Database\Schema\Grammars\Grammar $grammar
     * @return void
     */
    public function build(ConnectionInterface $connection, IlluminateSchemaGrammar $grammar)
    {
        foreach ($this->toCypher($connection, $grammar) as $statement)
        {
            $connection->statement($statement);
        }
    }

    /**
     * Get the raw Cypher statements for the blueprint.
     *
     * @param  \Illuminate\Database\ConnectionInterface  $connection
     * @param  \Illuminate\Database\Schema\Grammars\Grammar  $grammar
     * @return array
     */
    public function toCypher(ConnectionInterface $connection, IlluminateSchemaGrammar $grammar)
    {
        $statements = [];

        // Each type of command has a corresponding compiler function on the schema
        // grammar which is used to build the necessary SQL statements to build
        // the blueprint element, so we'll just call that compilers function.
        foreach ($this->commands as $command)
        {
            $method = 'compile'.ucfirst($command->name);

            if (method_exists($grammar, $method))
            {
                if ( ! is_null($cypher = $grammar->$method($this, $command, $connection)))
                {
                    $statements = array_merge($statements, (array) $cypher);
                }
            }
        }

        return $statements;
    }

    /**
     * Indicate that the label should be dropped.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function drop()
    {
        return $this->addCommand('drop');
    }

    /**
     * Indicate that the label should be dropped if it exists.
     *
     * @return \Illuminate\Support\Fluent
     */
    public function dropIfExists()
    {
        return $this->addCommand('dropIfExists');
    }

    /**
     * Rename the label to a given name.
     *
     * @param  string  $to
     * @return \Illuminate\Support\Fluent
     */
    public function renameLabel($to)
    {
        return $this->addCommand('renameLabel', compact('to'));
    }

    /**
     * Indicate that the given unique constraint on labels properties should be dropped.
     *
     * @param  string|array  $properties
     * @return \Illuminate\Support\Fluent
     */
    public function dropUnique($properties)
    {
        $properties = (array) $properties;

        foreach ($properties as $property)
        {
            $this->indexCommand('dropUnique', $property);
        }
    }

    /**
     * Indicate that the given index on label's properties should be dropped.
     *
     * @param  string|array  $properties
     * @return \Illuminate\Support\Fluent
     */
    public function dropIndex($properties)
    {
        $properties = (array) $properties;

        foreach ($properties as $property)
        {
            $this->indexCommand('dropIndex', $property);
        }
    }

    /**
     * Specify a unique contraint for label's properties.
     *
     * @param  string|array  $properties
     * @return \Illuminate\Support\Fluent
     */
    public function unique($properties)
    {
        $properties = (array) $properties;

        foreach ($properties as $property)
        {
            $this->addCommand('unique', ['property' => $property]);
        }
    }

    /**
     * Specify an index for the label properties.
     *
     * @param  string|array  $properties
     * @return \Illuminate\Support\Fluent
     */
    public function index($properties)
    {
        $properties = (array) $properties;

        foreach ($properties as $property)
        {
            $this->addCommand('index', ['property' => $property]);
        }
    }

    /**
     * Add a new command to the blueprint.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @return \Illuminate\Support\Fluent
     */
    protected function addCommand($name, array $parameters = [])
    {
        $this->commands[] = $command = $this->createCommand($name, $parameters);

        return $command;
    }

    /**
     * Create a new Fluent command.
     *
     * @param  string  $name
     * @param  array   $parameters
     * @return \Illuminate\Support\Fluent
     */
    protected function createCommand($name, array $parameters = [])
    {
        return new Fluent(
            array_merge(
                compact('name'),
                $parameters)
        );
    }

    /**
     * Add a new index command to the blueprint.
     *
     * @param  string        $type
     * @param  string|array  $property
     * @param  string        $index
     * @return \Illuminate\Support\Fluent
     */
    protected function indexCommand($type, $property)
    {
        return $this->addCommand($type, compact('property'));
    }

    /**
     * Set the label that blueprint describes.
     *
     * @return string
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Get the label that blueprint describes.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Get the commands on the blueprint.
     *
     * @return array
     */
    public function getCommands()
    {
        return $this->commands;
    }

    /**
     * Return the label that blueprint describes.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getLabel();
    }

}
