<?php namespace Vinelab\NeoEloquent\Tests\Query;

use Mockery as M;
use Vinelab\NeoEloquent\Query\Builder;
use Vinelab\NeoEloquent\Tests\TestCase;

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
    public function testSelect()
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
    public function testSelectWithProperties()
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

    public function testBasicWhere()
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

    public function testNullWhere()
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

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }
}
