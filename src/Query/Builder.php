<?php

namespace Vinelab\NeoEloquent\Query;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Vinelab\NeoEloquent\Connection;
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_merge;
use function collect;
use function is_array;
use function is_int;
use function is_null;
use function ksort;
use function reset;

/**
 * @method Connection getConnection()
 * @method CypherGrammar getGrammar()
 */
class Builder extends \Illuminate\Database\Query\Builder
{
    /**
     * @param mixed $value
     * @param string $type
     * @return static
     */
    public function addBinding($value, $type = 'where'): self
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}.");
        }

        // We only add associative arrays as neo4j only supports named parameters
        if (is_array($value) && Arr::isAssoc($value)) {
            $this->bindings[$type] = array_map(
                [$this, 'castBinding'],
                array_merge($this->bindings[$type], $value),
            );
        }

        return $this;
    }

    public function getBindings(): array
    {
        $tbr = [];
        foreach ($this->bindings as $bindingType) {
            foreach ($bindingType as $name => $value) {
                $tbr[$name] = $value;
            }
        }
        return $tbr;
    }

    public function upsert(array $values, $uniqueBy, $update = null)
    {
        $cql = $this->grammar->compileUpsert($this, $values, $uniqueBy, $update);

        return $this->massInsert($cql, $values);
    }

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

    public function insert(array $values): bool
    {
        $cql = $this->grammar->compileInsert($this, $values);

        return $this->massInsert($cql, $values);
    }

    public function toCypher(): string
    {
        return $this->toSql();
    }

    protected function massInsert(string $cql, array $values): bool
    {
        if (empty($values)) {
            return true;
        }

        $values = !is_array(reset($values)) ? [$values] : $values;

        $this->applyBeforeQueryCallbacks();

        return $this->connection->insert($cql, ['valueSets' => $values]);
    }
}
