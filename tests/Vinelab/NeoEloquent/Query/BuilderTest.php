<?php namespace Vinelab\NeoEloquent\Tests\Query;

use Mockery as M;
use Vinelab\NeoEloquent\Query\Builder;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Query\Grammars\CypherGrammar;

class QueryBuilderTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->grammar    = M::mock('Vinelab\NeoEloquent\Query\Grammars\CypherGrammar');
        $this->connection = M::mock('Vinelab\NeoEloquent\Connection');

        $this->neoClient = M::mock('Everyman\Neo4j\Client');
        $this->connection->shouldReceive('getClient')->once()->andReturn($this->neoClient);

        $this->builder = new Builder($this->connection, $this->grammar);
    }

    public function tearDown()
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
            'power'  => 'Strong Fart Noises'
        );

        $node=  M::mock('Everyman\Neo4j\Node');

        $this->neoClient->shouldReceive('makeNode')->once()->andReturn($node);
        $this->neoClient->shouldReceive('makeLabel')->once()->andReturn($label);

        foreach ($values as $key => $value)
        {
            $node->shouldReceive('setProperty')->once()->with($key, $value);
        }

        // node should save
        $node->shouldReceive('save')->once();
        // get the node id
        $node->shouldReceive('getId')->once()->andReturn(9);
        // add the labels
        $node->shouldReceive('addLabels')->once()->with(M::type('array'));

        $this->builder->insertGetId($values);
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

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Value must be provided.
     */
    public function testFailingWhereWithNullValue()
    {
        $this->builder->where('id', '>', null);
    }

    public function testBasicWhereBindings()
    {
        $this->builder->where('id', 19);

        $this->assertEquals(array(
            array(
                'type'     => 'Basic',
                'column'   => 'id',
                'operator' => '=',
                'value'    => 19,
                'boolean'  => 'and'
            )
        ), $this->builder->wheres, 'make sure the statement was atted to $wheres');

        $this->assertEquals(array(
            array('id' => 19)
        ), $this->builder->getBindings());
    }

    public function testNullWhereBindings()
    {
        $this->builder->where('farted', null);

        $this->assertEquals(array(
            array(
                'type'    => 'Null',
                'boolean' => 'and',
                'column'  => 'farted'
            )
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
                'type'     => 'Basic',
                'column'   => 'id(n)',
                'boolean'  => 'and',
                'operator' => '=',
                'value'    => 200
            )
        ), $this->builder->wheres);

        $this->assertEquals(array(
            array('id' => 200)
        ), $this->builder->getBindings());
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
        $this->assertEquals('MATCH (n:User) RETURN *', $builder->toCypher());
    }

    public function testBasicAlias()
    {
        $builder = $this->getBuilder();
        $builder->select('foo as bar')->from('User');

        $this->assertEquals('MATCH (n:User) RETURN n.foo as bar', $builder->toSql());
    }

    public function testAddigSelects()
    {
        $builder = $this->getBuilder();
        $builder->select('foo')->addSelect('bar')->addSelect(array('baz', 'boom'))->from('User');
        $this->assertEquals('MATCH (n:User) RETURN n.foo, n.bar, n.baz, n.boom', $builder->toCypher());
    }


    public function testBasicWheres()
    {
        $builder = $this->getBuilder();
        $builder->select('*')->from('User')->where('username', '=', 'bakalazma');

        $bindings = $builder->getBindings();
        $this->assertEquals('MATCH (n:User) WHERE n.username = {username} RETURN *', $builder->toCypher());
        $this->assertEquals(array('username' => 'bakalazma'), reset($bindings));
    }

    public function testBasicSelectDistinct()
    {
        $builder = $this->getBuilder();
        $builder->distinct()->select('foo', 'bar')->from('User');

        $this->assertEquals('MATCH (n:User) RETURN DISTINCT n.foo, n.bar', $builder->toCypher());
    }

    public function testSelectWithCaching()
    {
        $cache = m::mock('stdClass');
        $driver = m::mock('stdClass');
        $query = $this->setupCacheTestQuery($cache, $driver);

        $query = $query->remember(5);

        $driver->shouldReceive('remember')
                         ->once()
                         ->with($query->getCacheKey(), 5, m::type('Closure'))
                         ->andReturnUsing(function($key, $minutes, $callback) { return $callback(); });

        $this->assertEquals($query->get(), array('results'));
    }

    public function testSelectWithCachingForever()
    {
        $cache = m::mock('stdClass');
        $driver = m::mock('stdClass');
        $query = $this->setupCacheTestQuery($cache, $driver);

        $query = $query->rememberForever();

        $driver->shouldReceive('rememberForever')
                                                ->once()
                                                ->with($query->getCacheKey(), m::type('Closure'))
                                                ->andReturnUsing(function($key, $callback) { return $callback(); });



        $this->assertEquals($query->get(), array('results'));
    }

    public function testSelectWithCachingAndTags()
    {
        $taggedCache = m::mock('StdClass');
        $cache = m::mock('stdClass');
        $driver = m::mock('stdClass');

        $driver->shouldReceive('tags')
                ->once()
                ->with(array('foo','bar'))
                ->andReturn($taggedCache);

        $query = $this->setupCacheTestQuery($cache, $driver);
        $query = $query->cacheTags(array('foo', 'bar'))->remember(5);

        $taggedCache->shouldReceive('remember')
                        ->once()
                        ->with($query->getCacheKey(), 5, m::type('Closure'))
                        ->andReturnUsing(function($key, $minutes, $callback) { return $callback(); });

        $this->assertEquals($query->get(), array('results'));
    }

    public function testAddBindingWithArrayMergesBindings()
    {
        $builder = $this->getBuilder();
        $builder->addBinding(array('foo' => 'bar'));
        $builder->addBinding(array('bar' => 'baz'));

        $this->assertEquals(array(
            array('foo' => 'bar'),
            array('bar' => 'baz')
        ), $builder->getBindings());
    }

    public function testAddBindingWithArrayMergesBindingsInCorrectOrder()
    {
        $builder = $this->getBuilder();
        $builder->addBinding(array('bar' => 'baz'), 'having');
        $builder->addBinding(array('foo' => 'bar'), 'where');

        $this->assertEquals(array(
            array('bar' => 'baz'),
            array('foo' => 'bar'),
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
            array('foo' => 'bar'),
            array('baz' => 'boom'),
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
        $grammar = new CypherGrammar;

        $builder = $this->getMock('Vinelab\NeoEloquent\Query\Builder', array('getFresh'), array($connection, $grammar));
        $builder->expects($this->once())->method('getFresh')->with($this->equalTo(array('*')))->will($this->returnValue(array('results')));

        return $builder->select('*')->from('User')->where('email', 'foo@bar.com');
    }

    protected function getBuilder()
    {
        $connection = M::mock('Vinelab\NeoEloquent\Connection');
        $client = M::mock('Everyman\Neo4j\Client');
        $connection->shouldReceive('getClient')->once()->andReturn($client);
        $grammar = new CypherGrammar;

        return new Builder($connection, $grammar);
    }
}
