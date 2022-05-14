<?php

namespace Vinelab\NeoEloquent\Tests\Query;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Mockery as M;
use PHPUnit\Framework\MockObject\MockObject;
use Vinelab\NeoEloquent\DSLContext;
use Vinelab\NeoEloquent\Query\CypherGrammar;
use Vinelab\NeoEloquent\Tests\TestCase;

class GrammarTest extends TestCase
{
    /** @var CypherGrammar */
    private CypherGrammar $grammar;
    /** @var Connection&MockObject */
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
        $context = new DSLContext();
        $p = $this->grammar->parameter('id', $context);
        $this->assertEquals('$param0', $p);

        $p1 = $this->grammar->parameter('id', $context);
        $this->assertEquals('$param1', $p1);

        $this->assertNotEquals($p, $p1);
    }

    public function testParametrize(): void
    {
        $this->assertEquals('$param0, $param1, $param2', $this->grammar->parameterize(['a', 'b', 'c']));
    }

    public function testParametrizeRepeat(): void
    {
        $this->assertEquals('$param0, $param1, $param2', $this->grammar->parameterize(['a', 'b', 'c']));
        $this->assertEquals('$param0, $param1, $param2', $this->grammar->parameterize(['a', 'b', 'c']));
    }

    public function testParametrizeRepeatWithContext(): void
    {
        $context = new DSLContext();
        $this->assertEquals('$param0, $param1, $param2', $this->grammar->parameterize(['a', 'b', 'c'], $context));
        $this->assertEquals('$param3, $param4, $param5', $this->grammar->parameterize(['a', 'b', 'c'], $context));
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

        $this->assertEquals('(`x_x`:`x_Node`)', $p);
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
            ->with('MATCH (Node:Node) RETURN *', [], true);

        $this->table->get();
    }

    public function testOrderBy(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with('MATCH (Node:Node) RETURN * ORDER BY Node.x, Node.y, Node.z DESC', [], true);

//        $this->table->grammar = new MySqlGrammar();
        $this->table->orderBy('x')->orderBy('y')->orderBy('z', 'desc')->get();
    }

    public function testBasicWhereEquals(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WHERE Node.x = $param0 RETURN *',
                ['y'],
                true
            );

        $this->table->where('x', 'y')->get();
    }

    public function testBasicWhereLessThan(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WHERE Node.x < $param0 RETURN *',
                ['y'],
                true
            );

        $this->table->where('x', '<', 'y')->get();
    }

    public function testWhereTime(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WHERE Node.x < time($param0) RETURN *',
                ['20:00'],
                true
            );

        $this->table->whereTime('x', '<', '20:00')->get();
    }

    public function testWhereDate(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WHERE Node.x = date($param0) RETURN *',
                ['2020-01-02'],
                true
            );

        $this->table->whereDate('x', '2020-01-02')->get();
    }

    public function testWhereYear(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WHERE Node.x.year = $param0 RETURN *',
                $this->countOf(1),
                true
            );

        $this->table->whereYear('x', 2023)->get();
    }

    public function testWhereMonth(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WHERE Node.x.month = $param0 RETURN *',
                ['05'],
                true
            );

        $this->table->whereMonth('x', '05')->get();
    }

    public function testWhereDay(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WHERE Node.x.day = $param0 RETURN *',
                [5],
                true
            );

        $this->table->whereDay('x', 5)->get();
    }

    public function testWhereExists(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) CALL { WITH Node MATCH (Y:Y) WHERE Node.x = Y.y RETURN * AS sub0 } WHERE Node.x = $param0 AND exists(sub0) RETURN *',
                ['y'],
                true
            );

        $this->table
            ->where('x', 'y')
            ->whereExists(static function (Builder $builder) {
                $builder->from('Y')->whereColumn('Node.x', 'Y.y');
            })
            ->get();
    }

    public function testWhereColumn(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WHERE Node.x = Node.y RETURN *',
                [],
                true
            );

        $this->table->whereColumn('x', 'y')->get();
    }

    public function testWhereSubComplex(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) CALL { WITH Node MATCH (Y:Y) WHERE Node.i = Y.i RETURN Y.i, Y.i AS sub0 LIMIT 1 } CALL { WITH Node MATCH (ZZ:ZZ) WHERE Node.i = ZZ.har RETURN i AS har LIMIT 1 } CALL { WITH Node MATCH (Node:Node) WHERE Node.i = $param0 RETURN Node.i, Node.i AS sub2 LIMIT 1 } WHERE (Node.x = sub0) AND ((Node.i = har) OR (Node.j = sub2)) RETURN *',
                ['i', 'i'],
                true
            );

        $this->table->where('x', '=', function (Builder $query) {
            $query->from('Y')
                ->select('i')
                ->whereColumn('Node.i', 'i')
                ->limit(1);
        })->whereNested(function (Builder $query) {
            $query->where('i', function (Builder $query) {
                $query->from('ZZ')
                    ->select('i as har')
                    ->whereColumn('Node.i', 'har')
                    ->limit(1);
            })->orWhere('j', function (Builder $query) {
                $query->select('i')
                    ->where('i', 'i')
                    ->limit(1);
            });
        })->get();
    }

    public function testUnionSimple(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WHERE Node.x = $param0 RETURN * UNION MATCH (X:X) WHERE X.y = $param1 RETURN *',
                ['y', 'z'],
                true
            );

        $this->table->where('x', 'y')->union(function (Builder $query) {
            $query->from('X')
                ->where('y', 'z');
        })->get();
    }

    public function testUnionSimpleComplexAll(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WHERE Node.x = $param0 RETURN * UNION ALL MATCH (X:X) WHERE X.y = $param1 RETURN * ORDER BY Node.x, X.y LIMIT 10 SKIP 5',
                ['y', 'z'],
                true
            );

        $this->table->where('x', 'y')->union(function (Builder $query) {
            $query->from('X')
                ->where('y', 'z');
        }, true)->orderBy('x')
            ->orderBy('X.y')
            ->limit(10)
            ->offset(5)
            ->get();
    }

    public function testWhereNested(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WHERE Node.x = $param0 OR (Node.xy = $param1 OR Node.z = $param2) AND Node.xx = $param3 RETURN *',
                [
                    'y',
                    'y',
                    'x',
                    'zz',
                    'y',
                    'x'
                ],
                true
            );

        $this->table->where('x', 'y')->whereNested(function (Builder $query) {
            $query->where('xy', 'y')->orWhere('z', 'x');
        }, 'or')->where('xx', 'zz')->get();
    }

    public function testWhereRowValues(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WHERE [Node.x, Node.y, Node.y] = [$param0, $param1, $param2] RETURN *',
                [0, 2, 3],
                true
            );

        $this->table->whereRowValues(['x', 'y', 'y'], '=', [0, 2, 3])->get();
    }

    public function testSimpleCrossJoin(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WITH Node MATCH (NewTest:NewTest) RETURN *',
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
                'MATCH (Node:Node) WITH Node MATCH (NewTest:NewTest) WHERE Node.id = NewTest.`test_id` RETURN *',
                [],
                true
            );

        $this->table->join('NewTest', 'Node.id', '=', 'NewTest.test_id')->get();
    }

    public function testLeftJoin(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WITH Node OPTIONAL MATCH (NewTest:NewTest) WHERE Node.id = NewTest.`test_id` RETURN *',
                [],
                true
            );

        $this->table->leftJoin('NewTest', 'Node.id', '=', 'NewTest.test_id')->get();
    }

    public function testCombinedJoin(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WITH Node OPTIONAL MATCH (NewTest:NewTest) WHERE Node.id = NewTest.`test_id` WITH Node, NewTest OPTIONAL MATCH (OtherTest:OtherTest) WHERE NewTest.id = OtherTest.id RETURN *',
                [],
                true
            );

        $this->table
            ->leftJoin('NewTest', 'Node.id', '=', 'NewTest.test_id')
            ->leftJoin('OtherTest', 'NewTest.id', '=', 'OtherTest.id')
            ->get();
    }

    public function testRightJoin(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'OPTIONAL MATCH (Node:Node) WITH Node MATCH (NewTest:NewTest) WHERE Node.id = NewTest.`test_id` RETURN *',
                [],
                true
            );

        $this->table->rightJoin('NewTest', 'Node.id', '=', 'NewTest.test_id')->get();
    }

    public function testExists(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WHERE Node.x < $param0 RETURN count(*) > 0 AS exists',
                [3],
                true
            );

        $this->table->where('x', '<', 3)->exists();
    }

    public function testAggregate(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) RETURN count(Node.views) AS aggregate',
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
                'MATCH (Node:Node) RETURN count(*) AS aggregate',
                [],
                true
            );

        $this->table->aggregate('count');
    }

    public function testAggregateMultiple(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WITH Node.views, Node.other WHERE Node.views IS NOT NULL OR Node.other IS NOT NULL RETURN count(*) AS aggregate',
                [],
                true
            );

        $this->table->aggregate('count', ['views', 'other']);
    }

    public function testHaving(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WITH Node.id AS id, collect(Node) AS groups WHERE id > $param0 UNWIND groups AS Node RETURN *',
                [100],
                true
            );

        $this->table
            ->groupBy('id')
            ->having('id', '>', 100)
            ->get();
    }
}
