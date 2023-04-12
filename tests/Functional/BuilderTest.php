<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Laudis\Neo4j\Types\Node;
use Vinelab\NeoEloquent\Tests\TestCase;

class BuilderTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->builder = new Builder($this->getConnection());
    }

    public function testDBIntegration(): void
    {
        self::assertInstanceOf(Builder::class, DB::table('Node'));
    }

    public function testSettingNodeLabels(): void
    {
        $this->builder->from('labels');
        $this->assertEquals('labels', $this->builder->from);

        $this->builder->from('User:Fan');
        $this->assertEquals('User:Fan', $this->builder->from);
    }

    public function testInsertingAndGettingId(): void
    {
        $this->builder->from('Hero');

        $values = [
            'length' => 123,
            'height' => 343,
            'power' => 'Strong Fart Noises',
            'id' => 69
        ];

        $hero = $this->builder->insertGetId($values);
        $this->assertInstanceOf(Node::class, $hero);
        $this->assertEquals(123, $hero->getProperty('length'));
        $this->assertEquals(343, $hero->getProperty('height'));
        $this->assertEquals('Strong Fart Noises', $hero->getProperty('power'));
        $this->assertEquals(69, $hero->getProperty('id'));
    }

    public function testBatchInsert(): void
    {
        $this->builder->from('Hero')->insert([
            ['a' => 'b'],
            ['c' => 'd']
        ]);

        $results = $this->builder->orderBy('a')->get();
        self::assertEquals([
            ['a' => 'b'],
            ['c' => 'd']
        ], $results-> toArray());
    }

    public function testUpsert(): void
    {
        $this->builder->from('Hero')->upsert([
            ['a' => 'aa', 'b' => 'bb', 'c' => 'cc'],
            ['a' => 'aaa', 'b' => 'bbb', 'c' => 'ccc'],
        ], ['a'], ['c']);

        self::assertEqualsCanonicalizing([
            ['a' => 'aa', 'b' => 'bb', 'c' => 'cc'],
            ['a' => 'aaa', 'b' => 'bbb', 'c' => 'ccc'],
        ], $this->builder->get()->toArray());

        $this->builder->from('Hero')->upsert([
            ['a' => 'aa', 'b' => 'bb', 'c' => 'cdc'],
            ['a' => 'aaa', 'b' => 'bbb', 'c' => 'ccdc'],
        ], ['a'], ['c']);

        self::assertEqualsCanonicalizing([
            ['a' => 'aa', 'b' => 'bb', 'c' => 'cdc'],
            ['a' => 'aaa', 'b' => 'bbb', 'c' => 'ccdc'],
        ], $this->builder->get()->toArray());
    }


    public function testFailingWhereWithNullValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->builder->where('id', '>', null);
    }

    public function testBasicWhereBindings(): void
    {
        $this->builder->where('id', 19);

        $this->assertEquals([
            [
                'type' => 'Basic',
                'column' => 'id',
                'operator' => '=',
                'value' => 19,
                'boolean' => 'and'
            ],
        ], $this->builder->wheres, 'make sure the statement was atted to $wheres');
    }

    public function testBasicWhereBindingsWithFromField(): void
    {
        $this->builder->from = ['user'];
        $this->builder->where('id', 19);

        $this->assertEquals([
            [
                'type' => 'Basic',
                'column' => 'id',
                'operator' => '=',
                'value' => 19,
                'boolean' => 'and'
            ],
        ], $this->builder->wheres);
    }

    public function testNullWhereBindings(): void
    {
        $this->builder->where('farted', null);

        $this->assertEquals([
            [
                'type' => 'Null',
                'boolean' => 'and',
                'column' => 'farted'
            ],
        ], $this->builder->wheres);
    }

    public function testWhereTransformsNodeIdBinding(): void
    {
        // when requesting a Node by its id we need to use
        // 'id(n)' but that won't be helpful when returned or dealt with
        // so we need to tranform it back to 'id'
        $this->builder->where('id(n)', 200);

        $this->assertEquals([
            [
                'type' => 'Basic',
                'column' => 'id(n)',
                'boolean' => 'and',
                'operator' => '=',
                'value' => 200,
            ],
        ], $this->builder->wheres);
    }

    protected function getBuilder(): Builder
    {
        return new Builder($this->getConnection());
    }
}
