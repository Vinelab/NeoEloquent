<?php

namespace Vinelab\NeoEloquent\Schema;

class Builder extends \Illuminate\Database\Schema\Builder
{
    /**
     * Drop all tables from the database.
     */
    public function dropAllTables(): void
    {
        $this->getConnection()->affectingStatement('MATCH (x) DETACH DELETE x');
    }
}
