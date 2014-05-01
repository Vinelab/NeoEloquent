<?php namespace Vinelab\NeoEloquent\Tests;

use DB;

class ConnectionTest extends TestCase {

    public function testConnection()
    {
        $c = DB::connection('neo4j');

        $this->assertInstanceOf('Vinelab\NeoEloquent\Connection', $c);

        $c1 = DB::connection('neo4j');
		$c2 = DB::connection('neo4j');

		$this->assertEquals(spl_object_hash($c1), spl_object_hash($c2));
    }

    public function testConnectionClientInstance()
    {
        $c = DB::connection('neo4j');

        $client = $c->getClient();

        $this->assertInstanceOf('Everyman\Neo4j\Client', $client);
    }

    public function testGettingConfigParam()
    {
        $c = DB::connection('neo4j');

        $this->assertEquals($c->getConfig('port'), 7474);
        $this->assertEquals($c->getConfig('host'), 'localhost');
    }

    public function testDriverName()
    {
        $c = DB::connection('neo4j');

        $this->assertEquals('neo4j', $c->getDriverName());
    }

}
