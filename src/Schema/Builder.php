<?php

namespace Vinelab\NeoEloquent\Schema;

use Closure;

use Illuminate\Database\Schema\Builder as IlluminateBuilder;

class Builder extends IlluminateBuilder
{
    /**
     * Fallback.
     *
     * @param string $label
     *
     * @return bool
     *
     * @throws RuntimeException
     */
    public function hasTable($label)
    {
        throw new \RuntimeException("
Please use commands from namespace:
    neo4j:
    neo4j:migrate
    neo4j:migrate:make
    neo4j:migrate:reset
    neo4j:migrate:rollback
If your default database is set to 'neo4j' and you want use other databases side by side with Neo4j
you can do so by passing additional arguments to default migration command like:
    php artisan neo4j:migrate --database=other-neo4j
        ");
    }

    /**
     * Create a new data defintion on label schema.
     *
     * @param string  $label
     * @param Closure $callback
     *
     * @return \Vinelab\NeoEloquent\Schema\Blueprint
     */
    public function label($label, Closure $callback)
    {
        return $this->build(
            $this->createBlueprint($label, $callback)
        );
    }

    /**
     * Determine if the given label exists.
     *
     * @param string $label
     *
     * @return bool
     */
    public function hasLabel($label)
    {
        $cypher = $this->connection->getSchemaGrammar()->compileLabelExists($label);

        return $this->getConnection()->select($cypher, [])->count() > 0;
    }

    /**
     * Determine if the given relation exists.
     *
     * @param string $relation
     *
     * @return bool
     */
    public function hasRelation($relation)
    {
        $cypher = $this->connection->getSchemaGrammar()->compileRelationExists($relation);

        return $this->getConnection()->select($cypher, [])->count() > 0;
    }

    /**
     * Rename a label.
     *
     * @param string $from
     * @param string $to
     *
     * @return \Vinelab\NeoEloquent\Schema\Blueprint|bool
     */
    public function renameLabel($from, $to)
    {
        $blueprint = $this->createBlueprint($from);

        $blueprint->renameLabel($to);

        return $this->build($blueprint);
    }

    /**
     * Create a new command set with a Closure.
     *
     * @param string  $label
     * @param Closure $callback
     *
     * @return \Vinelab\NeoEloquent\Schema\Blueprint
     */
    protected function createBlueprint($label, Closure $callback = null)
    {
        if (isset($this->resolver)) {
            return call_user_func($this->resolver, $label, $callback);
        } else {
            return new Blueprint($label, $callback);
        }
    }
}
