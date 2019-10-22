<?php

namespace Vinelab\NeoEloquent\Query\Processors;

use Illuminate\Database\Query\Builder as IlluminateBuilder;
use Illuminate\Database\Query\Processors\Processor as IlluminateProcessor;

class Processor extends IlluminateProcessor
{
    /**
     * Process an  "insert get ID" query.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string                             $sql
     * @param array                              $values
     * @param string                             $sequence
     *
     * @return int
     */
    public function processInsertGetId(IlluminateBuilder $query, $sql, $values, $sequence = null)
    {
        $query->getConnection()->insert($sql, $values);
        $id = $query->getConnection()->lastInsertedId();

        return is_numeric($id) ? (int) $id : $id;
    }
}
