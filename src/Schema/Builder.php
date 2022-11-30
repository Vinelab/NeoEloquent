<?php

namespace Vinelab\NeoEloquent\Schema;

use Closure;
use LogicException;
use Illuminate\Database\Schema\Blueprint;

class Builder extends \Illuminate\Database\Schema\Builder
{
    /**
     * Create a new data defintion on label schema.
     *
     * @param string  $label
     * @param Closure $callback
     *
     * @return Blueprint
     */
    public function label($label, Closure $callback)
    {
        return $this->build(
            $this->createBlueprint($label, $callback)
        );
    }

    /**
     * Drop a label from the schema.
     *
     * @param string $label
     *
     * @return Blueprint
     */
    public function drop($label)
    {
        $blueprint = $this->createBlueprint($label);

        $blueprint->drop();

        return $this->build($blueprint);
    }


    /**
     * Drop all tables from the database.
     *
     * @return void
     *
     * @throws LogicException
     */
    public function dropAllTables()
    {
        $this->getConnection()->affectingStatement('MATCH (x) DETACH DELETE x');
    }

    /**
     * Drop a label from the schema if it exists.
     *
     * @param string $label
     *
     * @return Blueprint
     */
    public function dropIfExists($label)
    {
        $blueprint = $this->createBlueprint($label);

        $blueprint->dropIfExists();

        return $this->build($blueprint);
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
        $cypher = $this->conn->getSchemaGrammar()->compileLabelExists($label);

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
        $cypher = $this->conn->getSchemaGrammar()->compileRelationExists($relation);

        return $this->getConnection()->select($cypher, [])->count() > 0;
    }

    /**
     * Rename a label.
     *
     * @param string $from
     * @param string $to
     *
     * @return Blueprint|bool
     */
    public function renameLabel($from, $to)
    {
        $blueprint = $this->createBlueprint($from);

        $blueprint->renameLabel($to);

        return $this->build($blueprint);
    }
}
