<?php

namespace Vinelab\NeoEloquent\Tests;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Laudis\Neo4j\Types\Node;
use Mockery as M;
use RuntimeException;
use Throwable;
use Vinelab\NeoEloquent\Connection;
use Vinelab\NeoEloquent\Eloquent\Model;
use Illuminate\Contracts\Events\Dispatcher;
use function time;
use Vinelab\NeoEloquent\Query\Builder;

class ConnectionTest extends TestCase
{
    private array $user = [
        'name' => 'A',
        'email' => 'ABC@efg.com',
        'username' => 'H I'
    ];

    protected function setUp(): void
    {
        parent::setUp();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->getConnection()->getPdo()->run('MATCH (x) DETACH DELETE x');
    }

    public function testRegisteredConnectionResolver(): void
    {
        $resolver = Model::getConnectionResolver();

        self::assertInstanceOf(DatabaseManager::class, $resolver);
        self::assertEquals('neo4j', $resolver->getDefaultConnection());
        self::assertInstanceOf(Connection::class, $resolver->connection('neo4j'));
        self::assertInstanceOf(Connection::class, $resolver->connection('default'));

        self::assertEquals('neo4j', $resolver->connection('neo4j')->getDatabaseName());
        self::assertEquals('neo4j', $resolver->connection('default')->getDatabaseName());
    }

    public function testLogQueryFiresEventsIfSet(): void
    {
        $connection = $this->getConnection();

        $connection->logQuery('foo', [], time());

        $events = M::mock(Dispatcher::class);
        $connection->setEventDispatcher($events);
        $events->shouldReceive('dispatch')->once()->withArgs(function ($x) use ($connection) {
            self::assertEquals($x, new QueryExecuted('foo', [], null, $connection));

            return true;
        });

        $connection->logQuery('foo', []);
    }

    public function testPretendOnlyLogsQueries(): void
    {
        $connection = $this->getConnection();
        $connection->enableQueryLog();
        $queries = $connection->pretend(function ($connection) {
            $connection->select('foo bar', ['baz']);
        });
        $this->assertEquals('foo bar', $queries[0]['query']);
        $this->assertEquals(['baz'], $queries[0]['bindings']);
    }

    public function testPreparingSimpleBindings(): void
    {
        $bindings = [
            'username' => 'jd',
            'name' => 'John Doe',
        ];

        $prepared = $this->getConnection('default')->prepareBindings($bindings);

        $this->assertEquals($bindings, $prepared);
    }

    public function testPreparingWheresBindings(): void
    {
        $bindings = [
            'username' => 'jd',
            'email' => 'marie@curie.sci',
        ];

        $c = $this->getConnection('default');

        $expected = [
            'username' => 'jd',
            'email' => 'marie@curie.sci',
        ];

        $prepared = $c->prepareBindings($bindings);

        $this->assertEquals($expected, $prepared);
    }

    public function testPreparingFindByIdBindings(): void
    {
        $bindings = [
            'id' => 6,
        ];

        /** @var Connection $c */
        $c = $this->getConnection('default');

        $expected = ['idn' => 6];

        $c->useLegacyIds(true);
        $prepared = $c->prepareBindings($bindings);
        $this->assertEquals($expected, $prepared);

        $c->useLegacyIds(false);
        $prepared = $c->prepareBindings($bindings);
        $this->assertEquals($bindings, $prepared);
    }

    public function testPreparingWhereInBindings(): void
    {
        $bindings = [
            'mc' => 'mc',
            'ae' => 'ae',
            'animals' => 'animals',
            'mulkave' => 'mulkave',
        ];

        $c = $this->getConnection('default');

        $expected = [
            'mc' => 'mc',
            'ae' => 'ae',
            'animals' => 'animals',
            'mulkave' => 'mulkave',
        ];

        $prepared = $c->prepareBindings($bindings);

        $this->assertEquals($expected, $prepared);
    }

    public function testSelectWithBindings(): void
    {
        $this->createUser();

        $query = 'MATCH (n:`User`) WHERE n.username = $username RETURN * LIMIT 1';

        $bindings = ['username' => $this->user['username']];

        $c = $this->getConnection('default');

        $c->enableQueryLog();
        $results = $c->select($query, $bindings);

        $log = $c->getQueryLog();
        $log = reset($log);

        $this->assertEquals($log['query'], $query);
        $this->assertEquals($log['bindings'], $bindings);

        $this->assertIsArray($results);
        $this->assertIsArray($results[0]);
        $this->assertInstanceOf(Node::class, $results[0]['n']);

        $this->assertEquals($this->user, $results[0]['n']->getProperties()->toArray());
    }

    /**
     * @depends testSelectWithBindings
     */
    public function testSelectWithBindingsById(): void
    {
        $this->createUser();

        /** @var Connection $c */
        $c = $this->getConnection('default');
        $c->useLegacyIds();

        $c->enableQueryLog();

        $query = 'MATCH (n:`User`) WHERE n.username = $username RETURN * LIMIT 1';

        // Get the ID of the created record
        $results = $c->select($query, ['username' => $this->user['username']]);

        $id = $results[0]['n']->getId();

        $bindings = [
            'id' => $id,
        ];


        // Select the Node containing the User record by its id
        $query = 'MATCH (n:`User`) WHERE id(n) = $idn RETURN * LIMIT 1';

        $results = $c->select($query, $bindings);

        $log = $c->getQueryLog();

        $this->assertEquals($log[1]['query'], $query);
        $this->assertEquals($log[1]['bindings'], $bindings);
        $this->assertIsArray($results);
        $this->assertIsArray($results[0]);

        $selected = $results[0]['n']->getProperties()->toArray();

        $this->assertEquals($this->user, $selected);
    }

    public function testAffectingStatement(): void
    {
        $c = $this->getConnection('default');

        $this->createUser();

        $type = 'dev';

        // Now we update the type and set it to $type
        $query = 'MATCH (n:`User`) WHERE n.username = $username '.
                 'SET n.type = $type, n.updated_at = $updated_at '.
                 'RETURN count(n)';

        $bindings = [
            'type' => $type,
            'updated_at' => '2014-05-11 13:37:15',
            'username' => $this->user['username'],
        ];

        $c->affectingStatement($query, $bindings);

        // Try to find the updated one and make sure it was updated successfully
        $query = 'MATCH (n:User) WHERE n.username = $username RETURN n';

        $results = $this->getConnection()->select($query, $bindings);

        $user = $results[0]['n']->getProperties()->toArray();

        $this->assertEquals($type, $user['type']);
    }

    public function testAffectingStatementOnNonExistingRecord(): void
    {
        $c = $this->getConnection();

        $type = 'dev';

        // Now we update the type and set it to $type
        $query = 'MATCH (n:`User`) WHERE n.username = $username '.
                 'SET n.type = $type, n.updated_at = $updated_at '.
                 'RETURN count(n)';

        $bindings = [
            'type' => $type,
            'updated_at' => '2014-05-11 13:37:15',
            'username' => $this->user['username'],
        ];

        $result = $c->affectingStatement($query, $bindings);

        self::assertEquals(0, $result);

        $this->createUser();

        $result = $c->affectingStatement($query, $bindings);

        self::assertGreaterThan(0, $result);
    }

    public function testSelectOneCallsSelectAndReturnsSingleResult(): void
    {
        $connection = $this->getConnection();

        $this->assertNull($connection->selectOne('MATCH (x) RETURN x', ['bar' => 'baz']));

        $this->createUser();
        $this->createUser();

        $this->assertEquals($this->user, $connection->selectOne('MATCH (x) RETURN x')['x']->getProperties()->toArray());
    }

    public function testBeganTransactionFiresEventsIfSet(): void
    {
        $connection = $this->getConnection();

        $events = M::mock(Dispatcher::class);
        $connection->setEventDispatcher($events);
        $events->shouldReceive('dispatch')->once()->withArgs(function ($x) use ($connection) {
            self::assertEquals($x, new TransactionBeginning($connection));

            return true;
        });

        $connection->beginTransaction();
    }

    public function testCommittedFiresEventsIfSet(): void
    {
        $connection = $this->getConnection();

        $events = M::mock(Dispatcher::class);
        $connection->setEventDispatcher($events);
        $events->shouldReceive('dispatch')->once()->withArgs(function ($x) use ($connection) {
            self::assertEquals($x, new TransactionCommitted($connection));

            return true;
        });

        $connection->commit();
    }

    public function testRollBackedFiresEventsIfSet(): void
    {
        $connection = $this->getConnection();

        $events = M::mock(Dispatcher::class);
        $connection->setEventDispatcher($events);
        $events->shouldReceive('dispatch')->once()->withArgs(function ($x) use ($connection) {
            self::assertEquals($x, new TransactionRolledBack($connection));

            return true;
        });
        $connection->rollback();
    }

    public function testTransactionMethodRunsSuccessfully(): void
    {
        $connection = $this->getConnection();

        $result = $connection->transaction(function ($db) { return $db; });
        $this->assertEquals($connection, $result);
    }

    public function testTransactionMethodRollsbackAndThrows(): void
    {
        $connection = $this->getConnection();

        try {
            $connection->transaction(function () { throw new RuntimeException('foo'); });
        } catch (Throwable $e) {
            $this->assertEquals('foo', $e->getMessage());
        }
    }

    public function testFromCreatesNewQueryBuilder(): void
    {
        $builder = $this->getConnection()->table('User');

        $this->assertInstanceOf(Builder::class, $builder);
        $this->assertEquals('User', $builder->from);
    }

    /*
     * Utility methods below this line
     */

    public function createUser()
    {
        /** @var Connection $c */
        $c = $this->getConnection('default');

        $create = 'CREATE (u:User {name: $name, email: $email, username: $username})';

        return $c->getSession()->run($create, $this->user);
    }
}
