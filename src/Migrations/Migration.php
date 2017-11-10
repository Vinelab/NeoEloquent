<?php namespace Vinelab\NeoEloquent\Migrations;

abstract class Migration {

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
