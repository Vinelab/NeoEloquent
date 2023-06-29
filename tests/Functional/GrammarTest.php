<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Support\Facades\DB;
use Mockery as M;
use PHPUnit\Framework\MockObject\MockObject;
use Vinelab\NeoEloquent\Grammars\CypherGrammar;
use Vinelab\NeoEloquent\ParameterStack;
use Vinelab\NeoEloquent\Tests\TestCase;

class FinalModel extends Model
{
    protected $guarded = [];

    protected $connection = 'mock';
}
class OtherModel extends Model
{
    protected $guarded = [];

    protected $connection = 'mock';

    public function hasManyMainModels(): HasMany
    {
        return $this->hasMany(MainModel::class);
    }
}

class MainModel extends Model
{
    protected $guarded = [];

    protected $connection = 'mock';

    public function hasOneExample(): HasOne
    {
        return $this->hasOne(OtherModel::class, 'main_id', 'id');
    }

    public function belongsToExample(): BelongsTo
    {
        return $this->belongsTo(OtherModel::class, 'main_id', 'id', 'hasManyMainModels');
    }

    public function hasManyExample(): HasMany
    {
        return $this->hasMany(OtherModel::class, 'main_id', 'id');
    }

    public function hasOneThroughExample(): HasOneThrough
    {
        return $this->hasOneThrough(OtherModel::class, FinalModel::class);
    }
}

class GrammarTest extends TestCase
{
    private CypherGrammar $grammar;

    /** @var Connection&MockObject */
    private Connection $connection;

    private Builder $table;

    private MainModel $model;

    public function setUp(): void
    {
        parent::setUp();
        $this->grammar = new CypherGrammar();
        $this->table = DB::table('Node');
        $this->connection = $this->createMock(Connection::class);
        $this->connection->method('setReadWriteType')->willReturn($this->connection);
        $this->connection->method('query')->willReturn(new Builder($this->connection, new CypherGrammar(), new Processor()));
        $this->table->connection = $this->connection;
        $this->table->grammar = $this->grammar;

        $this->model = new MainModel(['id' => 'a']);
        Connection::resolverFor('mock', (function ($connection, string $database, string $prefix, array $config) {
            return $this->connection;
        })(...));
        \config()->set('database.connections.mock', ['database' => 'a', 'prefix' => 'prefix', 'driver' => 'mock']);
    }

    public function tearDown(): void
    {
        M::close();

        parent::tearDown();
    }

    public function testOrderBy(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with('MATCH (Node:Node) RETURN * ORDER BY Node.x,Node.y,Node.z DESC', [], true);

        //        $this->table->grammar = new MySqlGrammar();
        $this->table->orderBy('x', 'desc')->orderBy('y')->orderBy('z', 'desc')->get();
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
                'MATCH (Node:Node) WHERE Node.x = $param0 CALL { WITH Node MATCH (Y:Y) WHERE Node.x = Y.y RETURN count(*) AS sub0 } WITH Node, sub0 WHERE sub0 >= 1 RETURN *',
                ['y', []],
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
                'MATCH (Node:Node) CALL { WITH Node, Y MATCH (Y:Y) WHERE Node.i = Y.i RETURN Y.i, Y.i AS sub0 LIMIT 1 } CALL { WITH Node, Y, sub0, ZZ MATCH (ZZ:ZZ) WHERE Node.i = ZZ.har RETURN i AS har LIMIT 1 } CALL { WITH Node, Y, sub0, ZZ, sub1, Node MATCH (Node:Node) WHERE Node.i = $param0 RETURN Node.i, Node.i AS sub2 LIMIT 1 } WHERE (Node.x = sub0) AND ((Node.i = har) OR (Node.j = sub2)) RETURN *',
                [[], [[], ['i']], [[]], [1 => ['i']]],
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
                ['y', ['z']],
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
                'MATCH (Node:Node) WHERE Node.x = $param0 RETURN * UNION ALL MATCH (X:X) WHERE X.y = $param1 RETURN * ORDER BY Node.x ASC, X.y ASC LIMIT 10 SKIP 5',
                ['y', ['z']],
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
                    ['y', 'x'],
                    'zz',
                    ['y'],
                    [1 => 'x'],
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
                [[0, 2, 3]],
                true
            );

        $this->table->whereRowValues(['x', 'y', 'y'], '=', [0, 2, 3])->get();
    }

    public function testSimpleCrossJoin(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WITH Node MATCH (NewTest:NewTest) WITH Node, NewTest RETURN *',
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
                'MATCH (Node:Node) WITH Node MATCH (NewTest:NewTest) WHERE Node.id = NewTest.`test_id` WITH Node, NewTest RETURN *',
                [[]],
                true
            );

        $this->table->join('NewTest', 'Node.id', '=', 'NewTest.test_id')->get();
    }

    public function testLeftJoin(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WITH Node OPTIONAL MATCH (NewTest:NewTest) WHERE Node.id = NewTest.`test_id` WITH Node, NewTest RETURN *',
                [[]],
                true
            );

        $this->table->leftJoin('NewTest', 'Node.id', '=', 'NewTest.test_id')->get();
    }

    public function testCombinedJoin(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) WITH Node OPTIONAL MATCH (NewTest:NewTest) WHERE Node.id = NewTest.`test_id` WITH Node, NewTest OPTIONAL MATCH (OtherTest:OtherTest) WHERE NewTest.id = OtherTest.id WITH Node, NewTest, OtherTest RETURN *',
                [[], []],
                true
            );

        $this->table
            ->leftJoin('NewTest', 'Node.id', '=', 'NewTest.test_id')
            ->leftJoin('OtherTest', 'NewTest.id', '=', 'OtherTest.id')
            ->get();
    }

    public function testWhereRelationship(): void
    {
        $sql = $this->table->whereRelationship('HAS_OTHER_NODE', 'OtherNode', '>')->toSql();

        $this->assertMatchesRegularExpression('/MATCH \(Node:Node\) WHERE -\[\w+:`HAS_OTHER_NODE`]-> RETURN \*/', $sql);
    }

    public function testRightJoin(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'OPTIONAL MATCH (Node:Node) WITH Node MATCH (NewTest:NewTest) WHERE Node.id = NewTest.`test_id` WITH Node, NewTest RETURN *',
                [[]],
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
                'MATCH (Node:Node) RETURN count(Node.views, Node.other) AS aggregate',
                [],
                true
            );

        $this->table->aggregate('count', ['views', 'other']);
    }

    public function testGrammar(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (Node:Node) RETURN count(Node.views, Node.other) AS aggregate',
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

    public function testHasOne(): void
    {
        $this->connection->expects($this->once())
            ->method('select')
            ->with(
                'MATCH (`other_models`:`other_models`) WHERE `other_models`.`main_id` = $param0 AND (`other_models`.`main_id` IS NOT NULL) RETURN * LIMIT 1',
                [0],
                true
            );

        $this->model->getRelationValue('hasOneExample');
    }

    public function testBelongsToOne(): void
    {
        $sql = $this->model->belongsToExample()->toSql();
        $this->assertEquals('MATCH (`other_models`:`other_models`) WHERE (`other_models`.id IS NULL) RETURN *', $sql);
    }

    public function testHasMany(): void
    {
        $sql = $this->model->hasManyExample()->toSql();
        $this->assertEquals('MATCH (`other_models`:`other_models`) WHERE `other_models`.`main_id` = $param0 AND (`other_models`.`main_id` IS NOT NULL) RETURN *', $sql);
    }

    public function testHasOneThrough(): void
    {
        $sql = $this->model->hasOneThroughExample()->toSql();
        $this->assertEquals('MATCH (`other_models`:`other_models`) WITH `other_models` MATCH (`final_models`:`final_models`) WHERE `final_models`.id = `other_models`.`final_model_id` WITH `other_models`, `final_models` WHERE `final_models`.`main_model_id` = $param0 RETURN *', $sql);
    }
}
