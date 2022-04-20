<?php

namespace Vinelab\NeoEloquent\Tests\Query;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Mockery as M;
use Vinelab\NeoEloquent\Query\CypherGrammar;
use Vinelab\NeoEloquent\Tests\TestCase;

class GrammarTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->grammar = new CypherGrammar();
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
        $query = DB::table('Test');
        $select = $this->grammar->compileSelect($query);

        self::assertEquals('MATCH (Test:Test) RETURN Test', $select);
    }

    public function testSimpleCrossJoin(): void
    {
        $query = DB::table('Test')->crossJoin('NewTest');
        $select = $this->grammar->compileSelect($query);

        self::assertEquals('MATCH (Test:Test) WITH Test MATCH (NewTest:NewTest) RETURN Test, NewTest', $select);
    }

    public function testInnerJoin(): void
    {
        $query = DB::table('Test')->join('NewTest', 'Test.id', '=', 'NewTest.test_id');
        $select = $this->grammar->compileSelect($query);

        self::assertEquals('MATCH (Test:Test) WITH Test MATCH (NewTest:NewTest) WHERE Test.id = NewTest.`test_id` RETURN Test, NewTest', $select);
    }
}
