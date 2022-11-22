<?php

namespace Vinelab\NeoEloquent;

use Illuminate\Database\Query\Builder;
use Laudis\Neo4j\Contracts\HasPropertiesInterface;
use function is_iterable;
use function is_numeric;

class Processor extends \Illuminate\Database\Query\Processors\Processor
{
    public function processSelect(Builder $query, $results)
    {
        $tbr = [];
        $from = $query->from;
        foreach ($results as $row) {
            $processedRow =  [];
            foreach ($row as $key => $value) {
                if ($value instanceof HasPropertiesInterface) {
                    if ($key === $from) {
                        foreach ($value->getProperties() as $prop => $x) {
                            $processedRow[$prop] = $x;
                        }
                    }
                } else {
                    $processedRow[$key] = $value;
                }
            }
            $tbr[] = $processedRow;
        }

        return $tbr;
    }

    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $query->getConnection()->insert($sql, $values);

        // There is no universal way to get the id until neo4j 5 is properly documented
        return $values[$sequence] ?? null;
    }
}