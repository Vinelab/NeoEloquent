<?php

namespace Vinelab\NeoEloquent\Tests\Query;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use Vinelab\NeoEloquent\LabelAction;
use Vinelab\NeoEloquent\Query\Builder;
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

        $this->assertEquals(69, $this->builder->insertGetId($values));
        $this->assertEquals($values, $this->builder->from('Hero')->first());
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

    public function testNestedWhere(): void
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testSubWhere(): void
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testBasicSelect(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('User');
        $this->assertMatchesRegularExpression('/MATCH \(var\w+:User\) RETURN var\w+/', $builder->toCypher());
    }

    public function testBasicAlias(): void
    {
        $builder = $this->getBuilder();
        $builder->select('foo as bar')->from('User');

        $this->assertMatchesRegularExpression(
            '/MATCH \(var\w+:User\) RETURN var\w+\.foo AS bar/',
            $builder->toCypher()
        );
    }

    public function testAddingSelects(): void
    {
        $builder = $this->getBuilder();
        $builder->select('foo')->addSelect('bar')->addSelect(['baz', 'boom'])->from('User');
        $this->assertMatchesRegularExpression(
            '/MATCH \(var\w+:User\) RETURN var\w+\.foo, var\w+\.bar, var\w+\.baz, var\w+\.boom/',
            $builder->toCypher()
        );
    }

    public function testBasicWheres(): void
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('User')->where('username', '=', 'bakalazma');

        $this->assertMatchesRegularExpression(
            '/MATCH \(var\w+:User\) WHERE \(var\w+\.username = \$param\w+\) RETURN var\w+/',
            $builder->toCypher()
        );

        $bindings = $builder->getBindings();
        $this->assertTrue(Arr::isAssoc($bindings));
        $this->assertEquals(['bakalazma'], array_values($bindings));
    }

    public function testBasicSelectDistinct(): void
    {
        $builder = $this->getBuilder();
        $builder->distinct()->select('foo', 'bar')->from('User');

        $this->assertMatchesRegularExpression(
            '/MATCH \(var\w+:User\) RETURN DISTINCT var\w+\.foo, var\w+\.bar/',
            $builder->toCypher()
        );
    }

    public function testAddBindingWithArrayMergesBindings(): void
    {
        $builder = $this->getBuilder();
        $builder->addBinding(['foo' => 'bar']);
        $builder->addBinding(['bar' => 'baz']);

        $this->assertEquals([
            'foo' => 'bar',
            'bar' => 'baz',
        ], $builder->getBindings());
    }

    public function testAddBindingWithArrayMergesBindingsInCorrectOrder(): void
    {
        $builder = $this->getBuilder();
        $builder->addBinding(['bar' => 'baz'], 'having');
        $builder->addBinding(['foo' => 'bar'], 'where');

        $this->assertEquals([
            'bar' => 'baz',
            'foo' => 'bar',
        ], $builder->getBindings());
    }

    public function testMergeBuilders(): void
    {
        $builder = $this->getBuilder();
        $builder->addBinding(['foo' => 'bar']);

        $otherBuilder = $this->getBuilder();
        $otherBuilder->addBinding(['baz' => 'boom']);

        $builder->mergeBindings($otherBuilder);

        $this->assertEquals([
            'foo' => 'bar',
            'baz' => 'boom',
        ], $builder->getBindings());
    }

    protected function getBuilder(): Builder
    {
        return new Builder($this->getConnection());
    }
}
