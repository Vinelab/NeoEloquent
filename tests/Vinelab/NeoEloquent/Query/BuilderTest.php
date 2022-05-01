<?php

namespace Vinelab\NeoEloquent\Tests\Query;

use BadMethodCallException;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Vinelab\NeoEloquent\LabelAction;
use Vinelab\NeoEloquent\Tests\TestCase;
use function array_values;

class BuilderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        /** @noinspection PhpUndefinedMethodInspection */
        $this->getConnection()->getPdo()->run('MATCH (x) DETACH DELETE x');

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

        $this->expectException(BadMethodCallException::class);
        $this->builder->insertGetId($values);
    }

    public function testBatchInsert(): void
    {
        $this->builder->from('Hero')->insert([
            ['a' => 'b'],
            ['c' => 'd']
        ]);

        $results = $this->builder->get();
        self::assertEquals([
            ['a' => 'b'],
            ['c' => 'd']
        ], $results->toArray());
    }

    public function testMakingLabel(): void
    {
        $this->assertTrue($this->builder->from('Hero')->insert(['a' => 'b']));

        $this->assertEquals(1, $this->builder->update([new LabelAction('MaLabel')]));

        $node = $this->getConnection()->getPdo()->run('MATCH (x) RETURN x')->first()->get('x');
        $this->assertEquals(['Hero', 'MaLabel'], $node->getLabels()->toArray());
    }

    public function testUpsert(): void
    {
        $this->builder->from('Hero')->upsert([
            ['a' => 'aa', 'b' => 'bb', 'c' => 'cc'],
            ['a' => 'aaa', 'b' => 'bbb', 'c' => 'ccc'],
        ], ['a'], ['c']);

        self::assertEquals([
            ['a' => 'aa', 'b' => 'bb', 'c' => 'cc'],
            ['a' => 'aaa', 'b' => 'bbb', 'c' => 'ccc'],
        ], $this->builder->get()->toArray());

        $this->builder->from('Hero')->upsert([
            ['a' => 'aa', 'b' => 'bb', 'c' => 'cdc'],
            ['a' => 'aaa', 'b' => 'bbb', 'c' => 'ccdc'],
        ], ['a'], ['c']);

        self::assertEquals([
            ['a' => 'aa', 'b' => 'bb', 'c' => 'cdc'],
            ['a' => 'aaa', 'b' => 'bbb', 'c' => 'ccdc'],
        ], $this->builder->get()->toArray());
    }


    public function testFailingWhereWithNullValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Illegal operator and value combination.');
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
