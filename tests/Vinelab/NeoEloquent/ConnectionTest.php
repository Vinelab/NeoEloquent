<?php

namespace Vinelab\NeoEloquent\Tests;

use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\CypherMap;
use Mockery as M;

class ConnectionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->user = array(
            'name' => 'Mulkave',
            'email' => 'me@mulkave.io',
            'username' => 'mulkave',
        );

        $this->client = $this->getClient();
    }

    public function tearDown(): void
    {
        M::close();

        parent::tearDown();
    }

    public function testConnection()
    {
        $c = $this->getConnectionWithConfig('neo4j');

        $this->assertInstanceOf('Vinelab\NeoEloquent\Connection', $c);
    }

    public function testConnectionClientInstance()
    {
        $c = $this->getConnectionWithConfig('neo4j');

        $client = $c->getClient();

        $this->assertInstanceOf(ClientInterface::class, $client);
    }

    public function testGettingConfigParam()
    {
        $c = $this->getConnectionWithConfig('neo4j');

        $config = require(__DIR__.'/../../config/database.php');
        $this->assertEquals($c->getConfigOption('port'), $config['connections']['neo4j']['port']);
        $this->assertEquals($c->getConfigOption('host'), $config['connections']['neo4j']['host']);
    }

    public function testDriverName()
    {
        $c = $this->getConnectionWithConfig('neo4j');

        $this->assertEquals('neo4j', $c->getDriverName());
    }

    public function testGettingClient()
    {
        $c = $this->getConnectionWithConfig('neo4j');

        $this->assertInstanceOf(ClientInterface::class, $c->getClient());
    }

    public function testGettingDefaultHost()
    {
        $c = $this->getConnectionWithConfig('default');

        $this->assertEquals('localhost', $c->getHost([]));
        $this->assertEquals(7687, $c->getPort([]));
    }

    public function testGettingDefaultPort()
    {
        $c = $this->getConnectionWithConfig('default');

        $port = $c->getPort([]);

        $this->assertEquals(7687, $port);
        $this->assertIsInt($port);
    }

    public function testGettingQueryCypherGrammar()
    {
        $c = $this->getConnectionWithConfig('default');

        $grammar = $c->getQueryGrammar();

        $this->assertInstanceOf('Vinelab\NeoEloquent\Query\Grammars\CypherGrammar', $grammar);
    }

    public function testPrepareBindings()
    {
        $date = M::mock('DateTime');
        $date->shouldReceive('format')->once()->with('foo')->andReturn('bar');

        $bindings = array('test' => $date);

        $conn = $this->getMockConnection();
        $grammar = M::mock('Vinelab\NeoEloquent\Query\Grammars\CypherGrammar');
        $grammar->shouldReceive('getDateFormat')->once()->andReturn('foo');
        $conn->setQueryGrammar($grammar);
        $result = $conn->prepareBindings($bindings);

        $this->assertEquals(array('test' => 'bar'), $result);
    }

    public function testLogQueryFiresEventsIfSet()
    {
        $connection = $this->getMockConnection();
        $connection->logQuery('foo', array(), time());
        $connection->setEventDispatcher($events = M::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('dispatch')->once()->with('illuminate.query', array('foo', array(), null, null));
        $connection->logQuery('foo', array(), null);

        self::assertTrue(true);
    }

    public function testPretendOnlyLogsQueries()
    {
        $connection = $this->getMockConnection();
        $connection->enableQueryLog();
        $queries = $connection->pretend(function ($connection) {
            $connection->select('foo bar', array('baz'));
        });
        $this->assertEquals('foo bar', $queries[0]['query']);
        $this->assertEquals(array('baz'), $queries[0]['bindings']);
    }

    public function testPreparingSimpleBindings()
    {
        $bindings = array(
            'username' => 'jd',
            'name' => 'John Doe',
        );

        $c = $this->getConnectionWithConfig('default');

        $prepared = $c->prepareBindings($bindings);

        $this->assertEquals($bindings, $prepared);
    }

    public function testPreparingWheresBindings()
    {
        $bindings = array(
            'username' => 'jd',
            'email' => 'marie@curie.sci',
        );

        $c = $this->getConnectionWithConfig('default');

        $expected = array(
            'username' => 'jd',
            'email' => 'marie@curie.sci',
        );

        $prepared = $c->prepareBindings($bindings);

        $this->assertEquals($expected, $prepared);
    }

    public function testPreparingFindByIdBindings()
    {
        $bindings = array(
            'id' => 6,
        );

        $c = $this->getConnectionWithConfig('default');

        $expected = array('idn' => 6);

        $prepared = $c->prepareBindings($bindings);

        $this->assertEquals($expected, $prepared);
    }

    public function testPreparingWhereInBindings()
    {
        $bindings = array(
            'mc' => 'mc',
            'ae' => 'ae',
            'animals' => 'animals',
            'mulkave' => 'mulkave',
        );

        $c = $this->getConnectionWithConfig('default');

        $expected = array(
            'mc' => 'mc',
            'ae' => 'ae',
            'animals' => 'animals',
            'mulkave' => 'mulkave',
        );

        $prepared = $c->prepareBindings($bindings);

        $this->assertEquals($expected, $prepared);
    }

    public function testGettingCypherGrammar()
    {
        $c = $this->getConnectionWithConfig('default');

        $cypher = 'MATCH (u:`User`) RETURN * LIMIT 10';
        $query = $c->getCypherQuery($cypher, array());

        $this->assertIsArray($query);
        $this->assertArrayHasKey('statement', $query);
        $this->assertArrayHasKey('parameters', $query);
        $this->assertEquals($cypher, $query['statement']);
    }

    public function testCheckingIfBindingIsABinding()
    {
        $c = $this->getConnectionWithConfig('default');

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
        $c = $this->getConnectionWithConfig('default');

        $connection = $c->createConnection();

        $this->assertInstanceOf(ClientInterface::class, $connection);
    }

    public function testSelectWithBindings()
    {
        $created = $this->createUser();

        $query = 'MATCH (n:`User`) WHERE n.username = $username RETURN * LIMIT 1';

        $bindings = ['username' => $this->user['username']];

        $c = $this->getConnectionWithConfig('default');

        $c->enableQueryLog();
        $results = $c->select($query, $bindings);

        $log = $c->getQueryLog();
        $log = reset($log);

        $this->assertEquals($log['query'], $query);
        $this->assertEquals($log['bindings'], $bindings);
        $this->assertInstanceOf(CypherList::class, $results);

        // This is how we get the first row of the result (first [0])
        // and then we get the Node instance (the 2nd [0])
        // and then ask it to return its properties
        $selected = $results->first()->first()->getValue()->getProperties()->toArray();

        $this->assertEquals($this->user, $selected, 'The fetched User must be the same as the one we just created');
    }

    /**
     * @depends testSelectWithBindings
     */
    public function testSelectWithBindingsById()
    {
        // Create the User record
        $created = $this->createUser();

        $c = $this->getConnectionWithConfig('default');
        $c->enableQueryLog();

        $query = 'MATCH (n:`User`) WHERE n.username = $username RETURN * LIMIT 1';

        // Get the ID of the created record
        $results = $c->select($query, array('username' => $this->user['username']));

        $node = $results->first()->first()->getValue();
        $id = $node->getId();

        $bindings = array(
            'id' => $id,
        );

        // Select the Node containing the User record by its id
        $query = 'MATCH (n:`User`) WHERE id(n) = $idn RETURN * LIMIT 1';

        $results = $c->select($query, $bindings);

        $log = $c->getQueryLog();

        $this->assertEquals($log[1]['query'], $query);
        $this->assertEquals($log[1]['bindings'], $bindings);
        $this->assertInstanceOf(CypherList::class, $results);

        $selected = $results->first()->first()->getValue()->getProperties()->toArray();

        $this->assertEquals($this->user, $selected);
    }

    public function testAffectingStatement()
    {
        $c = $this->getConnectionWithConfig('default');

        $created = $this->createUser();

        $type = 'dev';

        // Now we update the type and set it to $type
        $query = 'MATCH (n:`User`) WHERE n.username = $username '.
                 'SET n.type = $type, n.updated_at = $updated_at '.
                 'RETURN count(n)';

        $bindings = array(
            'type' => $type,
            'updated_at' => '2014-05-11 13:37:15',
            'username' => $this->user['username'],
        );

        $results = $c->affectingStatement($query, $bindings);

        $this->assertInstanceOf(SummarizedResult::class, $results);

        /** @var CypherMap $result */
        foreach ($results as $result) {
            $count = $result->first()->getValue();
            $this->assertEquals(1, $count);
        }

        // Try to find the updated one and make sure it was updated successfully
        $query = 'MATCH (n:User) WHERE n.username = $username RETURN n';
        $cypher = $c->getCypherQuery($query, array('username' => $this->user['username']));

        $results = $this->client->run($cypher['statement'], $cypher['parameters']);

        $this->assertInstanceOf(CypherList::class, $results);

        $user = $results->first()->first()->getValue()->getProperties()->toArray();

        $this->assertEquals($type, $user['type']);
    }

    public function testAffectingStatementOnNonExistingRecord()
    {
        $c = $this->getConnectionWithConfig('default');

        $type = 'dev';

        // Now we update the type and set it to $type
        $query = 'MATCH (n:`User`) WHERE n.username = $username '.
                 'SET n.type = $type, n.updated_at = $updated_at '.
                 'RETURN count(n)';

        $bindings = array(
            array('type' => $type),
            array('updated_at' => '2014-05-11 13:37:15'),
            array('username' => $this->user['username']),
        );

        $results = $c->affectingStatement($query, $bindings);

        $this->assertInstanceOf(SummarizedResult::class, $results);

        /** @var CypherMap $result */
        foreach ($results as $result) {
            $count = $result->first()->getValue();
            $this->assertEquals(0, $count);
        }
    }

    public function testSettingDefaultCallsGetDefaultGrammar()
    {
        $connection = $this->getMockConnection();
        $mock = M::mock('StdClass');
        $connection->expects($this->once())->method('getDefaultQueryGrammar')->will($this->returnValue($mock));
        $connection->useDefaultQueryGrammar();
        $this->assertEquals($mock, $connection->getQueryGrammar());
    }

    public function testSettingDefaultCallsGetDefaultPostProcessor()
    {
        $connection = $this->getMockConnection();
        $mock = M::mock('StdClass');
        $connection->expects($this->once())->method('getDefaultPostProcessor')->will($this->returnValue($mock));
        $connection->useDefaultPostProcessor();
        $this->assertEquals($mock, $connection->getPostProcessor());
    }

    public function testSelectOneCallsSelectAndReturnsSingleResult()
    {
        $connection = $this->getMockConnection(array('select'));
        $connection->expects($this->once())->method('select')->with('foo', array('bar' => 'baz'))->will($this->returnValue(array('foo')));
        $this->assertEquals('foo', $connection->selectOne('foo', array('bar' => 'baz')));
    }

    public function testInsertCallsTheStatementMethod()
    {
        $connection = $this->getMockConnection(array('statement'));
        $connection->expects($this->once())->method('statement')
            ->with($this->equalTo('foo'), $this->equalTo(array('bar')))
            ->will($this->returnValue('baz'));
        $results = $connection->insert('foo', array('bar'));
        $this->assertEquals('baz', $results);
    }

    public function testUpdateCallsTheAffectingStatementMethod()
    {
        $connection = $this->getMockConnection(array('affectingStatement'));
        $connection->expects($this->once())->method('affectingStatement')->with($this->equalTo('foo'), $this->equalTo(array('bar')))->will($this->returnValue('baz'));
        $results = $connection->update('foo', array('bar'));
        $this->assertEquals('baz', $results);
    }

    public function testDeleteCallsTheAffectingStatementMethod()
    {
        $connection = $this->getMockConnection(array('affectingStatement'));
        $connection->expects($this->once())->method('affectingStatement')->with($this->equalTo('foo'), $this->equalTo(array('bar')))->will($this->returnValue('baz'));
        $results = $connection->delete('foo', array('bar'));
        $this->assertEquals('baz', $results);
    }

    public function testBeganTransactionFiresEventsIfSet()
    {
        $connection = $this->getMockConnection(array('getName'));
        $connection->expects($this->once())->method('getName')->will($this->returnValue('name'));
        $connection->setEventDispatcher($events = M::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('dispatch')->once()->with('connection.name.beganTransaction', $connection);
        $connection->beginTransaction();
    }

    public function testCommitedFiresEventsIfSet()
    {
        $connection = $this->getMockConnection(array('getName'));
        $connection->expects($this->once())->method('getName')->will($this->returnValue('name'));
        $connection->setEventDispatcher($events = M::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('dispatch')->once()->with('connection.name.committed', $connection);
        $connection->commit();
    }

    public function testRollBackedFiresEventsIfSet()
    {
        $connection = $this->getMockConnection(array('getName'));
        $connection->expects($this->once())->method('getName')->will($this->returnValue('name'));
        $connection->setEventDispatcher($events = M::mock('Illuminate\Contracts\Events\Dispatcher'));
        $events->shouldReceive('dispatch')->once()->with('connection.name.rollingBack', $connection);
        $connection->rollback();
    }

    public function testTransactionMethodRunsSuccessfully()
    {
        $connection = $this->getMockConnection();
        $connection->setClient($this->getClient());

        $result = $connection->transaction(function ($db) { return $db; });
        $this->assertEquals($connection, $result);
    }

    public function testTransactionMethodRollsbackAndThrows()
    {
        $connection = $this->getMockConnection();
        $connection->setClient($this->getClient());

        try {
            $connection->transaction(function () { throw new \Exception('foo'); });
        } catch (\Exception $e) {
            $this->assertEquals('foo', $e->getMessage());
        }
    }

    public function testFromCreatesNewQueryBuilder()
    {
        $conn = $this->getMockConnection();
        $conn->setQueryGrammar(M::mock('Vinelab\NeoEloquent\Query\Grammars\CypherGrammar')->makePartial());
        $builder = $conn->node('User');
        $this->assertInstanceOf('Vinelab\NeoEloquent\Query\Builder', $builder);
        $this->assertEquals('User', $builder->from);
    }

    /*
     * Utility methods below this line
     */

    public function createUser()
    {
        $c = $this->getConnectionWithConfig('default');

        // First we create the record that we need to update
        $create = 'CREATE (u:User {name: $name, email: $email, username: $username})';
        // The bindings structure is a little weird, I know
        // but this is how they are collected internally
        // so bare with it =)
        $createCypher = $c->getCypherQuery($create, array(
            'name' => $this->user['name'],
            'email' => $this->user['email'],
            'username' => $this->user['username'],
        ));

        return $this->client->run($createCypher['statement'], $createCypher['parameters']);
    }

    protected function getMockConnection($methods = array())
    {
        $defaults = array('getDefaultQueryGrammar', 'getDefaultPostProcessor', 'getDefaultSchemaGrammar');

        return $this->getMockBuilder('Vinelab\NeoEloquent\Connection')
            ->setMethods(array_merge($defaults, $methods))
            ->setConstructorArgs( array($this->dbConfig['connections']['neo4j']))
            ->getMock();

    }
}

class DatabaseConnectionTestMockNeo
{
}
