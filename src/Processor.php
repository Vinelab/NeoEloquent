<?php

namespace Vinelab\NeoEloquent;

use Illuminate\Database\Query\Builder;
use Laudis\Neo4j\Contracts\HasPropertiesInterface;
use Laudis\Neo4j\Databags\SummarizedResult;

use function is_iterable;
use function is_numeric;
use function is_object;
use function method_exists;
use function str_contains;
use function str_replace;

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
                            $processedRow[$prop] = $this->filterDateTime($x);
                        }
                    }
                } elseif (str_contains($query->from . '.', $key) || !str_contains('.', $key)) {
                    $key = str_replace($query->from . '.', '', $key);
                    $processedRow[$key] = $this->filterDateTime($value);
                }
            }
            $tbr[] = $processedRow;
        }

        return $tbr;
    }

    /**
     * @return mixed
     */
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        /** @var SummarizedResult $result */
        $result = $query->getConnection()->insert($sql, $values);

        return $result->first()->first()->getValue();
    }

    /**
     * @param $x
     *
     * @return mixed
     */
    private function filterDateTime($x)
    {
        if (is_object($x) && method_exists($x, 'toDateTime')) {
            return $x->toDateTime();
        }

        return $x;
    }
}