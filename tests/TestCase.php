<?php namespace Vinelab\NeoEloquent\Tests;

use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\DatabaseManager;
use Mockery as M;
use Vinelab\NeoEloquent\Connection;
use Vinelab\NeoEloquent\Eloquent\Model;
use PHPUnit\Framework\TestCase as PHPUnit;
use \Illuminate\Database\Schema\Builder as SchemaBuilder;


class Stub extends Model
{

}

class TestCase extends PHPUnit
{

    protected $resolver;

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
        $connection = (new Stub)->getConnection();
        $client = $connection->getClient();
        // Remove all relationships and related nodes
        $query = new \Everyman\Neo4j\Cypher\Query($client, 'MATCH (n) OPTIONAL MATCH (n) - [r] - (c) DELETE n, r, c');
        $query->getResultSet();
    }

    protected function prepareDatabase()
    {
        $config = [
            'database.default' => 'neo4j',
            'database.connections' => $this->dbConfig['connections']
        ];
        $container = m::mock('Illuminate\Container\Container');
        $container->shouldReceive('bound')->andReturn(false);
        $container->shouldReceive('offsetGet')->with('config')->andReturn($config);
        $db = new DatabaseManager(
            $container,
            new ConnectionFactory($container)
        );
        $this->db = $db;
        $sqliteConnection = $this->db->connection('sqlite');
        $sqliteConnection->setSchemaGrammar(new \Illuminate\Database\Schema\Grammars\SQLiteGrammar);
        $sqliteConnection->setQueryGrammar(new \Illuminate\Database\Query\Grammars\SQLiteGrammar);
        $this->schema = new SchemaBuilder($sqliteConnection);

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')
            ->withArgs(["sqlite"])
            ->andReturn($sqliteConnection);

        $neo4jConnection = $this->getConnectionWithConfig('neo4j');

        $resolver->shouldReceive('connection')
            ->withArgs(["neo4j"])
            ->andReturn($neo4jConnection);

        $resolver->shouldReceive('connection')
            ->withArgs([null])
            ->andReturn($neo4jConnection);

        $resolver->shouldReceive('getDefaultConnection')
            ->andReturn($this->getConnectionWithConfig('default'));

        $this->resolver = $resolver;
    }

}
