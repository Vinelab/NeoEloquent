<?php

namespace Vinelab\NeoEloquent\Migrations;

use Illuminate\Database\Migrations\Migration as IlluminateMigration;

abstract class Migration extends IlluminateMigration
{
    /**
     * The name of the database connection to use.
     *
     * @var string
     */
    protected $connection;

    /**
     * Get the migration connection name.
     *
     * @return string
     */
    public function getConnection()
    {
        return $this->connection;
    }
}
