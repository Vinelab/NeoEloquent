<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laudis\Neo4j\Types\Node;
use Mockery as M;
use RuntimeException;
use Throwable;
use Vinelab\NeoEloquent\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Events\Dispatcher;
use Vinelab\NeoEloquent\Tests\TestCase;
use function time;
use Vinelab\NeoEloquent\Query\Builder;

class ConnectionTest extends TestCase
{
    use RefreshDatabase;

    private array $user = [
        'name' => 'A',
        'email' => 'ABC@efg.com',
        'username' => 'H I'
    ];

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
            'param0' => 'jd',
            'param1' => 'John Doe',
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

        /** @var Connection $c */
        $c = $this->getConnection('default');

        $expected = [
            'username' => 'jd',
            'email' => 'marie@curie.sci',
        ];

        $prepared = $c->prepareBindings($bindings);

        $this->assertEquals($expected, $prepared);
    }

    public function testAffectingStatement(): void
    {
        $c = $this->getConnection('default');

        $this->createUser();

        $type = 'dev';

        // Now we update the type and set it to $type
        $query = 'MATCH (n:`User`) WHERE n.username = $param0 '.
                 'SET n.type = $param1, n.updated_at = $param2 '.
                 'RETURN count(n)';

        $bindings = [
            'param0' => $this->user['username'],
            'param1' => $type,
            'param2' => '2014-05-11 13:37:15',
        ];

        $c->affectingStatement($query, $bindings);

        // Try to find the updated one and make sure it was updated successfully
        $query = 'MATCH (n:User) WHERE n.username = $param0 RETURN n';

        $results = $this->getConnection()->select($query, $bindings);

        $user = $results[0]['n']->getProperties()->toArray();

        $this->assertEquals($type, $user['type']);
    }

    public function testAffectingStatementOnNonExistingRecord(): void
    {
        $c = $this->getConnection();

        $type = 'dev';

        // Now we update the type and set it to $type
        $query = 'MATCH (n:`User`) WHERE n.username = $param0 '.
                 'SET n.type = $param1, n.updated_at = $param2 '.
                 'RETURN count(n)';

        $bindings = [
            'param0' => $this->user['username'],
            'param1' => $type,
            'param2' => '2014-05-11 13:37:15',
        ];

        $result = $c->affectingStatement($query, $bindings);

        self::assertGreaterThan(0, $result);

        $this->createUser();

        $result = $c->affectingStatement($query, $bindings);

        self::assertGreaterThan(0, $result);
    }

    public function testSelectOneCallsSelectAndReturnsSingleResult(): void
    {
        $connection = $this->getConnection();

        $this->createUser();
        $this->createUser();

        $this->assertInstanceOf(Node::class, $connection->selectOne('MATCH (x) RETURN x')['x']);
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
