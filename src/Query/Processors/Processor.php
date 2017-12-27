<?php

namespace Vinelab\NeoEloquent\Query\Processors;

use \Illuminate\Database\Query\Processors\Processor as IlluminateProcessor;
use \Illuminate\Database\Query\Builder as IlluminateBuilder;

class Processor extends IlluminateProcessor
{
    /**
     * Process an  "insert get ID" query.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @param  string  $sql
     * @param  array   $values
     * @param  string  $sequence
     * @return int
     */
    public function processInsertGetId(IlluminateBuilder $query, $sql, $values, $sequence = NULL)
    {
        $query->getConnection()->insert($sql, $values);
        $id = $query->getConnection()->lastInsertedId();

        return is_numeric($id) ? (int) $id : $id;
    }
}
