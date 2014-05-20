<?php namespace Vinelab\NeoEloquent\Tests;

use Vinelab\NeoEloquent\Connection;
use PHPUnit_Framework_TestCase as PHPUnit;

class TestCase extends PHPUnit {

    public function __construct()
    {
        parent::__construct();

        // load custom configuration file
        $this->dbConfig = require 'config/database.php';
    }

    public static function setUpBeforeClass()
    {
        date_default_timezone_set('Asia/Beirut');
    }

    protected function getConnectionWithConfig($config = null)
    {
        $connection = is_null($config) ? $this->dbConfig['connections']['default'] :
                                         $this->dbConfig['connections'][$config];

        return new Connection($connection);
    }

}
