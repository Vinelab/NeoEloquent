<?php

namespace Vinelab\NeoEloquent\Query\Processors;

use Vinelab\NeoEloquent\Query\Builder;

class Processor
{
    /**
     * Process the results of a "select" query.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     * @param array                              $results
     *
     * @return array
     */
    public function processSelect(Builder $query, $results)
    {
        return $results;
    }

    /**
     * Process an  "insert get ID" query.
     *
     * @param \Vinelab\NeoEloquent\Query\Builder $query
     * @param string                             $cypher
     * @param array                              $values
     * @param string                             $sequence
     *
     * @return int
     */
    public function processInsertGetId(Builder $query, $cypher, $values, $sequence = null)
    {
        $query->getConnection()->insert($cypher, $values);

        $id = $query->getConnection()->getPdo()->lastInsertId($sequence);

        return is_numeric($id) ? (int) $id : $id;
    }

    /**
     * Process the results of a column listing query.
     *
     * @param array $results
     *
     * @return array
     */
    public function processColumnListing($results)
    {
        return $results;
    }
}
