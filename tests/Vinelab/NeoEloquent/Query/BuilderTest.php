<?php

namespace Vinelab\NeoEloquent\Tests\Query;

use InvalidArgumentException;
use Laudis\Neo4j\Common\Uri;
use Laudis\Neo4j\Databags\DatabaseInfo;
use Laudis\Neo4j\Databags\ResultSummary;
use Laudis\Neo4j\Databags\ServerInfo;
use Laudis\Neo4j\Databags\Statement;
use Laudis\Neo4j\Databags\SummarizedResult;
use Laudis\Neo4j\Databags\SummaryCounters;
use Laudis\Neo4j\Enum\ConnectionProtocol;
use Laudis\Neo4j\Enum\QueryTypeEnum;
use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\CypherMap;
use Mockery as M;
use Laudis\Neo4j\Types\Node;
use Laudis\Neo4j\Contracts\ClientInterface;
use Vinelab\NeoEloquent\Query\Builder;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Query\Grammars\CypherGrammar;

class BuilderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->grammar = M::mock('Vinelab\NeoEloquent\Query\Grammars\CypherGrammar')->makePartial();
        $this->connection = M::mock('Vinelab\NeoEloquent\Connection')->makePartial();

        $this->neoClient = M::mock(ClientInterface::class);
        $this->connection->shouldReceive('getClient')->andReturn($this->neoClient);

        $this->builder = new Builder($this->connection, $this->grammar);
    }

    public function tearDown(): void
    {
        M::close();

        parent::tearDown();
    }

    public function testSettingNodeLabels()
    {
        $this->builder->from(array('labels'));
        $this->assertEquals(array('labels'), $this->builder->from);

        $this->builder->from('User:Fan');
        $this->assertEquals('User:Fan', $this->builder->from);
    }

    public function testInsertingAndGettingId()
    {
        $label = array('Hero');
        $this->builder->from($label);

        $values = array(
            'length' => 123,
            'height' => 343,
            'power' => 'Strong Fart Noises',
        );

        $query = [
            'statement' => 'CREATE (hero:`Hero`) SET hero.length = $length_create, hero.height = $height_create, hero.power = $power_create RETURN hero',
            'parameters' => [
                'length_create' => $values['length'],
                'height_create' => $values['height'],
                'power_create' => $values['power'],
            ],
        ];

        $id = 69;
        $node = new Node($id, new CypherList(['Hero']), new CypherMap($values));
        $result = new CypherList([new CypherMap(['hero' => $node])]);

        $this->neoClient->shouldReceive('run')
            ->once()
            ->with($query['statement'], $query['parameters'])
            ->andReturn(new CypherList($result));

        $this->assertEquals($id, $this->builder->insertGetId($values));
    }

    public function testTransformingQueryToCypher()
    {
        $this->grammar->shouldReceive('compileSelect')->once()->with($this->builder)->andReturn(true);
        $this->assertTrue($this->builder->toCypher());
    }

    public function testMakingLabel()
    {
        $label = array('MaLabel');

        $this->neoClient->shouldReceive('makeLabel')->with($label)->andReturn($label);
        $this->assertEquals($label, $this->builder->makeLabel($label));
    }

    /**
     * @depends testTransformingQueryToCypher
     */
    public function testSelectResult()
    {
        $cypher = 'Some cypher here';
        $this->grammar->shouldReceive('compileSelect')->once()->andReturn($cypher);
        $this->connection->shouldReceive('select')->once()
            ->with($cypher, array())->andReturn('result');

        $result = $this->builder->getFresh();

        $this->assertEquals($result, 'result');
    }

    /**
     * @depends testTransformingQueryToCypher
     */
    public function testSelectingProperties()
    {
        $cypher = 'Some cypher here';
        $this->grammar->shouldReceive('compileSelect')->once()->andReturn($cypher);
        $this->connection->shouldReceive('select')->once()
            ->with($cypher, array())->andReturn('result');

        $result = $this->builder->getFresh(array('poop', 'head'));

        $this->assertEquals($result, 'result');
        $this->assertEquals($this->builder->columns, array('poop', 'head'), 'make sure the columns were set');
    }


    public function testFailingWhereWithNullValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectErrorMessage('Value must be provided.');
        $this->builder->where('id', '>', null);
    }

    public function testBasicWhereBindings()
    {
        $this->builder->where('id', 19);

        $this->assertEquals(array(
            array(
                'type' => 'Basic',
                'column' => 'id(n)',
                'operator' => '=',
                'value' => 19,
                'boolean' => 'and',
                'binding' => 'id(n)',
            ),
        ), $this->builder->wheres, 'make sure the statement was atted to $wheres');
        // When the '$from' attribute is not set on the query builder, the grammar
        // will use 'n' as the default node identifier.
        $this->assertEquals(array('idn' => 19), $this->builder->getBindings());
    }

    public function testBasicWhereBindingsWithFromField()
    {
        $this->builder->from = array('user');
        $this->builder->where('id', 19);

        $this->assertEquals(array(
            array(
                'type' => 'Basic',
                'column' => 'id(user)',
                'operator' => '=',
                'value' => 19,
                'boolean' => 'and',
                'binding' => 'id(user)',
            ),
        ), $this->builder->wheres, 'make sure the statement was atted to $wheres');
        // When no query builder is passed to the grammar then it will return 'n'
        // as node identifier by default.
        $this->assertEquals(array('iduser' => 19), $this->builder->getBindings());
    }

    public function testNullWhereBindings()
    {
        $this->builder->where('farted', null);

        $this->assertEquals(array(
            array(
                'type' => 'Null',
                'boolean' => 'and',
                'column' => 'farted',
                'binding' => 'farted',
            ),
        ), $this->builder->wheres);

        $this->assertEmpty($this->builder->getBindings(), 'no bindings should be added when dealing with null stuff..');
    }

    public function testWhereTransformsNodeIdBinding()
    {
        // when requesting a Node by its id we need to use
        // 'id(n)' but that won't be helpful when returned or dealt with
        // so we need to tranform it back to 'id'
        $this->builder->where('id(n)', 200);

        $this->assertEquals(array(
            array(
                'type' => 'Basic',
                'column' => 'id(n)',
                'boolean' => 'and',
                'operator' => '=',
                'value' => 200,
                'binding' => 'id(n)',
            ),
        ), $this->builder->wheres);

        $this->assertEquals(array('idn' => 200), $this->builder->getBindings());
    }

    public function testNestedWhere()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testSubWhere()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testBasicSelect()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('User');
        $this->assertEquals('MATCH (user:User) RETURN *', $builder->toCypher());
    }

    public function testBasicAlias()
    {
        $builder = $this->getBuilder();
        $builder->select('foo as bar')->from('User');

        $this->assertEquals('MATCH (user:User) RETURN user.foo as bar, user', $builder->toCypher());
    }

    public function testAddigSelects()
    {
        $builder = $this->getBuilder();
        $builder->select('foo')->addSelect('bar')->addSelect(array('baz', 'boom'))->from('User');
        $this->assertEquals('MATCH (user:User) RETURN user.foo, user.bar, user.baz, user.boom, user', $builder->toCypher());
    }

    public function testBasicWheres()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('User')->where('username', '=', 'bakalazma');

        $bindings = $builder->getBindings();
        $this->assertEquals('MATCH (user:User) WHERE user.username = $userusername RETURN *', $builder->toCypher());
        $this->assertEquals(array('userusername' => 'bakalazma'), $bindings);
    }

    public function testBasicSelectDistinct()
    {
        $builder = $this->getBuilder();
        $builder->distinct()->select('foo', 'bar')->from('User');

        $this->assertEquals('MATCH (user:User) RETURN DISTINCT user.foo, user.bar, user', $builder->toCypher());
    }

    public function testAddBindingWithArrayMergesBindings()
    {
        $builder = $this->getBuilder();
        $builder->addBinding(array('foo' => 'bar'));
        $builder->addBinding(array('bar' => 'baz'));

        $this->assertEquals(array(
            'foo' => 'bar',
            'bar' => 'baz',
        ), $builder->getBindings());
    }

    public function testAddBindingWithArrayMergesBindingsInCorrectOrder()
    {
        $builder = $this->getBuilder();
        $builder->addBinding(array('bar' => 'baz'), 'having');
        $builder->addBinding(array('foo' => 'bar'), 'where');

        $this->assertEquals(array(
            'bar' => 'baz',
            'foo' => 'bar',
        ), $builder->getBindings());
    }

    public function testMergeBuilders()
    {
        $builder = $this->getBuilder();
        $builder->addBinding(array('foo' => 'bar'));

        $otherBuilder = $this->getBuilder();
        $otherBuilder->addBinding(array('baz' => 'boom'));

        $builder->mergeBindings($otherBuilder);

        $this->assertEquals(array(
            'foo' => 'bar',
            'baz' => 'boom',
        ), $builder->getBindings());
    }

    /*
     *  Utility functions down this line
     */

    public function setupCacheTestQuery($cache, $driver)
    {
        $connection = m::mock('Vinelab\NeoEloquent\Connection');
        $connection->shouldReceive('getClient')->once()->andReturn(M::mock('Everyman\Neo4j\Client'));
        $connection->shouldReceive('getName')->andReturn('default');
        $connection->shouldReceive('getCacheManager')->once()->andReturn($cache);
        $cache->shouldReceive('driver')->once()->andReturn($driver);
        $grammar = new CypherGrammar();

        $builder = $this->getMock('Vinelab\NeoEloquent\Query\Builder', array('getFresh'), array($connection, $grammar));
        $builder->expects($this->once())->method('getFresh')->with($this->equalTo(array('*')))->will($this->returnValue(array('results')));

        return $builder->select('*')->from('User')->where('email', 'foo@bar.com');
    }

    protected function getBuilder()
    {
        $connection = M::mock('Vinelab\NeoEloquent\Connection');
        $client = M::mock('Everyman\Neo4j\Client');
        $connection->shouldReceive('getClient')->once()->andReturn($client);
        $grammar = new CypherGrammar();

        return new Builder($connection, $grammar);
    }
}
