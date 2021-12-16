<?php

namespace Vinelab\NeoEloquent\Tests;

use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Databags\SummarizedResult;
use Mockery as M;
use Vinelab\NeoEloquent\Connection;
use PHPUnit\Framework\TestCase as PHPUnit;
use Vinelab\NeoEloquent\Eloquent\Model;

class Stub extends Model
{
}

class TestCase extends PHPUnit
{
    public function __construct()
    {
        parent::__construct();

        // load custom configuration file
        $this->dbConfig = require 'config/database.php';
    }

    public function setUp(): void
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));

        Stub::setConnectionResolver($resolver);
        $this->flushDb();
    }

    public function tearDown(): void
    {
        // everything should be clean before every test
        $this->flushDb();

        parent::tearDown();
    }

    public static function setUpBeforeClass(): void
    {
        date_default_timezone_set('Asia/Beirut');
    }

    /**
     * Get the connection with a given or the default configuration.
     *
     * @param string $config As specified in config/database.php
     *
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
     */
    protected function flushDb()
    {
        /** @var ClientInterface $client */
        $client = $this->getClient();

        $flushQuery = 'MATCH (n) DETACH DELETE n';

        $client->run($flushQuery);
    }

    protected function getClient()
    {
        $connection = (new Stub())->getConnection();

        return $connection->getClient();
    }

    /**
     * get the node by the given id.
     *
     * @param int $id
     *
     * @return \Neoxygen\NeoClient\Formatter\Node
     */
    protected function getNodeById($id)
    {
        //get the labels using NeoClient
        $connection = $this->getConnectionWithConfig('neo4j');
        $client = $connection->getClient();
        /** @var SummarizedResult $result */
        $result = $client->run("MATCH (n) WHERE id(n)=$id RETURN n");

        return $result->first()->first()->getValue();
    }

    /**
     * Get node labels of a node by the given id.
     *
     * @param int $id
     *
     * @return array
     */
    protected function getNodeLabels($id)
    {
        return $this->getNodeById($id)->labels()->toArray();
    }
}
