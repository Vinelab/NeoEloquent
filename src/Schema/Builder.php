<?php

namespace Vinelab\NeoEloquent\Schema;

use Closure;
use LogicException;
use Illuminate\Database\Schema\Blueprint;

class Builder extends \Illuminate\Database\Schema\Builder
{
    /**
     * Drop all tables from the database.
     *
     * @return void
     */
    public function dropAllTables(): void
    {
        $this->getConnection()->affectingStatement('MATCH (x) DETACH DELETE x');
    }
}
