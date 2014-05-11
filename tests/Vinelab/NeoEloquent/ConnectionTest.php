<?php namespace Vinelab\NeoEloquent\Tests;

use DB;

class ConnectionTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->user = array(
            'name'     => 'Mulkave',
            'email'    => 'me@mulkave.io',
            'username' => 'mulkave'
        );
    }

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

    public function testGettingClient()
    {
        $c = DB::connection('neo4j');

        $this->assertInstanceOf('Everyman\Neo4j\Client', $c->getClient());
    }

    public function testGettingDefaultHost()
    {
        $c = DB::connection('default');

        $this->assertEquals('localhost', $c->getHost());
    }

    public function testGettingDefaultPort()
    {
        $c = DB::connection('default');

        $port=  $c->getPort();

        $this->assertEquals(7474, $port);
        $this->assertInternalType('int', $port);
    }

    public function testGettingQueryCypherGrammar()
    {
        $c = DB::connection('default');

        $grammar = $c->getQueryGrammar();

        $this->assertInstanceOf('Vinelab\NeoEloquent\Query\Grammars\CypherGrammar', $grammar);
    }

    public function testPreparingSimpleBindings()
    {
        $bindings = array(
            'username' => 'jd',
            'name' => 'John Doe'
        );

        $c = DB::connection('default');

        $prepared = $c->prepareBindings($bindings);

        $this->assertEquals($bindings, $prepared);
    }

    public function testPreparingWheresBindings()
    {
        $bindings = array(
            array('username' => 'jd'),
            array('email'    => 'marie@curie.sci')
        );

        $c = DB::connection('default');

        $expected = array(
            'username' => 'jd',
            'email'    => 'marie@curie.sci'
        );

        $prepared = $c->prepareBindings($bindings);

        $this->assertEquals($expected, $prepared);
    }

    public function testPreparingFindByIdBindings()
    {
        $bindings = array(
            array('id' => 6)
        );

        $c = DB::connection('default');

        $expected = array('_nodeId' => 6);

        $prepared = $c->prepareBindings($bindings);

        $this->assertEquals($expected, $prepared);
    }

    public function testPreparingWhereInBindings()
    {
        $bindings = array('mc', 'ae', '2pac', 'mulkave');

        $c = DB::connection('default');

        $expected = array(
            'mc'      => 'mc',
            'ae'      => 'ae',
            '2pac'    => '2pac',
            'mulkave' => 'mulkave'
        );

        $prepared = $c->prepareBindings($bindings);

        $this->assertEquals($expected, $prepared);
    }

    public function testGettingCypherGrammar()
    {
        $c = DB::connection('default');

        $query = $c->getCypherQuery('MATCH (u:`User`) RETURN * LIMIT 10', array());

        $this->assertInstanceOf('Everyman\Neo4j\Cypher\Query', $query);
    }

    public function testCheckingIfBindingIsABinding()
    {
        $c = DB::connection('default');

        $empty = array();
        $valid = array('key' => 'value');
        $invalid = array(array('key' => 'value'));
        $bastard = array(array('key' => 'value'), 'another' => 'value');

        $this->assertFalse($c->isBinding($empty));
        $this->assertFalse($c->isBinding($invalid));
        $this->assertFalse($c->isBinding($bastard));
        $this->assertTrue($c->isBinding($valid));
    }

    public function testCreatingConnection()
    {
        $c = DB::connection('default');

        $connection = $c->createConnection();

        $this->assertInstanceOf('Everyman\Neo4j\Client', $connection);
    }

    public function testSelectWithBindings()
    {
        $created = $this->createUser();

        $query = 'MATCH (n:`User`) WHERE n.username = {username} RETURN * LIMIT 1';

        $bindings = array(array('username' => $this->user['username']));

        $c = DB::connection('default');

        $results = $c->select($query, $bindings);

        $log = reset($c->getQueryLog());

        $this->assertEquals($log['query'], $query);
        $this->assertEquals($log['bindings'], $bindings);
        $this->assertInstanceOf('Everyman\Neo4j\Query\ResultSet', $results);

        // This is how we get the first row of the result (first [0])
        // and then we get the Node instance (the 2nd [0])
        // and then ask it to return its properties
        $selected = $results[0][0]->getProperties();

        $this->assertEquals($this->user, $selected, 'The fetched User must be the same as the one we just created');
    }

    /**
     * @depends testSelectWithBindings
     */
    public function testSelectWithBindingsById()
    {

        // Create the User record
        $created = $this->createUser();

        $c = DB::connection('default');

        $query = 'MATCH (n:`User`) WHERE n.username = {username} RETURN * LIMIT 1';

        // Get the ID of the created record
        $results = $c->select($query, array(array('username' => $this->user['username'])));

        $node = $results[0][0];
        $id = $node->getId();

        $bindings = array(
            array('id' => $id)
        );

        // Select the Node containing the User record by its id
        $query = 'MATCH (n:`User`) WHERE id(n) = {_nodeId} RETURN * LIMIT 1';

        $results = $c->select($query, $bindings);

        $log = $c->getQueryLog();

        $this->assertEquals($log[1]['query'], $query);
        $this->assertEquals($log[1]['bindings'], $bindings);
        $this->assertInstanceOf('Everyman\Neo4j\Query\ResultSet', $results);

        $selected = $results[0][0]->getProperties();

        $this->assertEquals($this->user, $selected);
    }

    public function testAffectingStatement()
    {
        $c = DB::connection('default');

        $created = $this->createUser();

        $type = 'dev';

        // Now we update the type and set it to $type
        $query = 'MATCH (n:`User`) WHERE n.username = {username} ' .
                 'SET n.type = {type}, n.updated_at = {updated_at} ' .
                 'RETURN count(n)';

        $bindings = array(
            array('type'       => $type),
            array('updated_at' => '2014-05-11 13:37:15'),
            array('username'   => $this->user['username'])
        );

        $results = $c->affectingStatement($query, $bindings);

        $this->assertInstanceOf('Everyman\Neo4j\Query\ResultSet', $results);

        foreach($results as $result)
        {
            $count = $result[0];
            $this->assertEquals(1, $count);
        }

        // Try to find the updated one and make sure it was updated successfully
        $query = 'MATCH (n:User) WHERE n.username = {username} RETURN n';
        $cypher = $c->getCypherQuery($query, array(array('username' => $this->user['username'])));

        $results = $cypher->getResultSet();

        $this->assertInstanceOf('Everyman\Neo4j\Query\ResultSet', $results);

        $user = null;

        foreach ($results as $result)
        {
            $node = $result[0];
            $user = $node->getProperties();

        }

        $this->assertEquals($type, $user['type']);
    }

    public function testAffectingStatementOnNonExistingRecord()
    {
        $c = DB::connection('default');

        $type = 'dev';

        // Now we update the type and set it to $type
        $query = 'MATCH (n:`User`) WHERE n.username = {username} ' .
                 'SET n.type = {type}, n.updated_at = {updated_at} ' .
                 'RETURN count(n)';

        $bindings = array(
            array('type'       => $type),
            array('updated_at' => '2014-05-11 13:37:15'),
            array('username'    => $this->user['username'])
        );

        $results = $c->affectingStatement($query, $bindings);

        $this->assertInstanceOf('Everyman\Neo4j\Query\ResultSet', $results);

        foreach($results as $result)
        {
            $count = $result[0];
            $this->assertEquals(0, $count);
        }
    }

    public function tearDown()
    {
        $query = 'MATCH (n:User) WHERE n.username = {username} DELETE n RETURN count(n)';

        $c = DB::connection('default');

        $cypher = $c->getCypherQuery($query, array(array('username' => $this->user['username'])));
        $cypher->getResultSet();

        parent::tearDown();
    }

    public function createUser()
    {
        $c = DB::connection('default');

        // First we create the record that we need to update
        $create = 'CREATE (u:User {name: {name}, email: {email}, username: {username}})';
        // The bindings structure is a little weird, I know
        // but this is how they are collected internally
        // so bare with it =)
        $createCypher = $c->getCypherQuery($create, array(
            array('name'     => $this->user['name']),
            array('email'    => $this->user['email']),
            array('username' => $this->user['username'])
        ));

        return $createCypher->getResultSet();
    }

}
