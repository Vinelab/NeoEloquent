<?php

namespace Vinelab\NeoEloquent\Query\Processors;

use \Illuminate\Database\Query\Processors\Processor as BaseProcessor;

class Processor extends BaseProcessor
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
    public function processInsertGetId(Builder $query, $sql, $values, $sequence = null)
    {
        $query->getConnection()->insert($sql, $values);
        dd($query->getConnection());
        $id = $query->getConnection()->getPdo()->lastInsertId($sequence);

        return is_numeric($id) ? (int) $id : $id;
    }
}
