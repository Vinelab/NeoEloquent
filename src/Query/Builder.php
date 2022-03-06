<?php

namespace Vinelab\NeoEloquent\Query;

use Vinelab\NeoEloquent\Connection;

/**
 * @method Connection getConnection()
 * @method CypherGrammar getGrammar()
 */
class Builder extends \Illuminate\Database\Query\Builder
{
    public function cleanBindings(array $bindings): array
    {
        // The Neo4J driver handles bindings and parametrization
        return $bindings;
    }

    public function insertGetId(array $values, $sequence = null)
    {
        $this->applyBeforeQueryCallbacks();

        $cypher = $this->getGrammar()->compileInsertGetId($this, $values, $sequence);

        return $this->getConnection()->select($cypher, $values, false)[0]['id'];
    }

    public function toCypher(): string
    {
        return $this->toSql();
    }

    public function makeLabel(string $label): string
    {
        $cypher = $this->getGrammar()->compileLabel($label);

        $this->getConnection()->affectingStatement($cypher);

        return $label;
    }
}
