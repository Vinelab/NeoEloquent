<?php namespace Vinelab\NeoEloquent\Tests;

use Mockery as M;
use Vinelab\NeoEloquent\Connection;
use Vinelab\NeoEloquent\Eloquent\Model;
use PHPUnit_Framework_TestCase as PHPUnit;

class Stub extends Model {

}

class TestCase extends PHPUnit {

    public function __construct()
    {
        parent::__construct();

        // load custom configuration file
        $this->dbConfig = require 'config/database.php';
    }

    public function setUp()
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));

        Stub::setConnectionResolver($resolver);
    }

    public function tearDown()
    {
        // everything should be clean before every test
        $this->flushDb();

        parent::tearDown();
    }

    public static function setUpBeforeClass()
    {
        date_default_timezone_set('Asia/Beirut');
    }

    /**
     * Get the connection with a given or the default configuration.
     *
     * @param  string $config As specified in config/database.php
     * @return \Vinelab\NeoEloquent\Connection
     */
    protected function getConnectionWithConfig($config = null)
    {
        $connection = is_null($config) ? $this->dbConfig['connections']['default'] :
                                         $this->dbConfig['connections'][$config];

        return new Connection($connection);
    }

    /**
     * Flush all database records.
     *
     * @return void
     */
    protected function flushDb()
    {
        $client = $this->getClient();

        $statements = [
           ['statement' => 'MATCH (n)-[r]-(c) DELETE n,r,c'],
           ['statement' => 'MATCH (n) DELETE n']
        ];

        $client->sendMultiple($statements);
    }

    protected function getClient()
    {
        $connection = (new Stub)->getConnection();

        return $connection->getClient();
    }
}
