<?php

namespace Vinelab\NeoEloquent\Tests\Query;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\MySqlGrammar;
use Illuminate\Support\Facades\DB;
use Mockery as M;
use PHPUnit\Framework\MockObject\MockObject;
use Vinelab\NeoEloquent\Query\CypherGrammar;
use Vinelab\NeoEloquent\Tests\TestCase;

class GrammarTest extends TestCase
{
    /**
     * @var CypherGrammar
     */
    private CypherGrammar $grammar;
    /** @var Connection&MockObject  */
    private Connection $connection;
    private Builder $table;

    public function setUp(): void
    {
        parent::setUp();
        $this->grammar = new CypherGrammar();
        $this->table = DB::table('Node');
        $this->connection = $this->createMock(Connection::class);
        $this->table->connection = $this->connection;
        $this->table->grammar = $this->grammar;
    }

    public function tearDown(): void
    {
        M::close();

        parent::tearDown();
    }

    public function testGettingQueryParameterFromRegularValue(): void
    {
        $p = $this->grammar->parameter('value');
        $this->assertStringStartsWith('$param', $p);
    }

    public function testGettingIdQueryParameter(): void
    {
        $p = $this->grammar->parameter('id');
        $this->assertStringStartsWith('$param', $p);

        $p1 = $this->grammar->parameter('id');
        $this->assertStringStartsWith('$param', $p);

        $this->assertNotEquals($p, $p1);
    }

    public function testTable(): void
    {
        $p = $this->grammar->wrapTable('Node');

        $this->assertEquals('(Node:Node)', $p);
    }

    public function testTableAlias(): void
    {
        $p = $this->grammar->wrapTable('Node AS x');

        $this->assertEquals('(x:Node)', $p);
    }

    public function testTablePrefixAlias(): void
    {
        $this->grammar->setTablePrefix('x_');
        $p = $this->grammar->wrapTable('Node AS x');

        $this->assertEquals('(x:`x_Node`)', $p);
    }

    public function testTablePrefix(): void
    {
        $this->grammar->setTablePrefix('x_');
        $p = $this->grammar->wrapTable('Node');

        $this->assertEquals('(`x_Node`:`x_Node`)', $p);
    }

    public function testSimpleFrom(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with('MATCH (Node:Node) RETURN Node', [], true);

        $this->table->get();
    }

    public function testSimpleCrossJoin(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WITH Node MATCH (NewTest:NewTest) RETURN Node, NewTest',
                [],
                true
            );

        $this->table->crossJoin('NewTest')->get();
    }

    public function testInnerJoin(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WITH Node MATCH (NewTest:NewTest) WHERE Node.id = NewTest.`test_id` RETURN Node, NewTest',
                [],
                true
            );

        $this->table->join('NewTest', 'Node.id', '=', 'NewTest.test_id')->get();
    }

    public function testAggregate(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WITH count(Node.views) AS Node RETURN Node',
                [],
                true
            );

        $this->table->aggregate('count', 'views');
    }

    public function testAggregateDefault(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WITH count(*) AS Node RETURN Node',
                [],
                true
            );

        $this->table->aggregate('count');
    }

    public function testAggregateIgnoresMultiple(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WITH count(Node.views) AS Node RETURN Node',
                [],
                true
            );

        $this->table->aggregate('count', ['views', 'other']);
    }
}
