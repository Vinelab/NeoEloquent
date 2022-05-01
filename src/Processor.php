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
        $tbr = parent::processSelect($query, $results);

        return $this->processRecursive($tbr);
    }

    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $query->getConnection()->insert($sql, $values);

        $id = $query->getConnection()->getPdo()->lastInsertId($sequence);

        return is_numeric($id) ? (int) $id : $id;
    }

    /**
     * @param mixed $x
     *
     * @return mixed
     */
    protected function processRecursive($x, int $depth = 0)
    {
        if ($x instanceof HasPropertiesInterface) {
            $x = $x->getProperties()->toArray();
        }

        if (is_iterable($x)) {
            $tbr = [];
            foreach ($x as $key => $y) {
                if ($depth === 1 && $y instanceof HasPropertiesInterface) {
                    foreach ($y->getProperties() as $prop => $value) {
                        $tbr[$prop] = $value;
                    }
                } else {
                    $tbr[$key] = $this->processRecursive($y, $depth + 1);
                }
            }
            $x = $tbr;
        }

        return $x;
    }
}