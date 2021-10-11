<?php

namespace Vinelab\NeoEloquent\Tests\Eloquent;

use Laudis\Neo4j\Types\CypherList;
use Laudis\Neo4j\Types\CypherMap;
use Laudis\Neo4j\Types\Relationship;
use Mockery as M;
use GraphAware\Neo4j\Client\Formatter\Type\Node;
use GraphAware\Neo4j\Client\Formatter\Result;
use Neoxygen\NeoClient\Formatter;
use PHPUnit\Framework\MockObject\MockBuilder;
use stdClass;
use Vinelab\NeoEloquent\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Collection;
use Vinelab\NeoEloquent\Exceptions\ModelNotFoundException;
use Vinelab\NeoEloquent\Query\Grammars\CypherGrammar;
use Vinelab\NeoEloquent\Tests\TestCase;

class EloquentBuilderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->query = M::mock('Vinelab\NeoEloquent\Query\Builder');
        $this->query->shouldReceive('modelAsNode')->andReturn('n');
        $this->model = M::mock('Vinelab\NeoEloquent\Eloquent\Model');

        $this->builder = new Builder($this->query);
    }

    public function tearDown(): void
    {
        M::close();

        parent::tearDown();
    }

    public function testFindMethod()
    {
        $builder = M::mock('Vinelab\NeoEloquent\Eloquent\Builder[first]', array($this->getMockQueryBuilder()));
        $builder->setModel($this->getMockModel());
        $builder->getQuery()->shouldReceive('where')->once()->with('foo', '=', 'bar');
        $builder->shouldReceive('first')->with(array('column'))->andReturn('baz');

        $result = $builder->find('bar', array('column'));
        $this->assertEquals('baz', $result);
    }

    public function testFindOrFailMethodThrowsModelNotFoundException()
    {
        $builder = m::mock('Vinelab\NeoEloquent\Eloquent\Builder[first]', array($this->getMockQueryBuilder()));
        $builder->setModel($this->getMockModel());
        $builder->getQuery()->shouldReceive('where')->once()->with('foo', '=', 'bar');
        $builder->shouldReceive('first')->with(array('column'))->andReturn(null);

        $this->expectException(ModelNotFoundException::class);
        $result = $builder->findOrFail('bar', array('column'));
    }

    public function testFindOrFailMethodWithManyThrowsModelNotFoundException()
    {
        $builder = m::mock('Vinelab\NeoEloquent\Eloquent\Builder[get]', array($this->getMockQueryBuilder()));
        $builder->setModel($this->getMockModel());
        $builder->getQuery()->shouldReceive('whereIn')->once()->with('foo', [1, 2]);
        $builder->shouldReceive('get')->with(array('column'))->andReturn(new Collection([1]));
        $this->expectException(ModelNotFoundException::class);
        $result = $builder->findOrFail([1, 2], array('column'));
    }


    public function testFirstOrFailMethodThrowsModelNotFoundException()
    {
        $builder = m::mock('Vinelab\NeoEloquent\Eloquent\Builder[first]', array($this->getMockQueryBuilder()));
        $builder->setModel($this->getMockModel());
        $builder->shouldReceive('first')->with(array('column'))->andReturn(null);
        $this->expectException(ModelNotFoundException::class);
        $result = $builder->firstOrFail(array('column'));
    }

    public function testFindWithMany()
    {
        $builder = m::mock('Vinelab\NeoEloquent\Eloquent\Builder[get]', array($this->getMockQueryBuilder()));
        $builder->getQuery()->shouldReceive('whereIn')->once()->with('foo', array(1, 2));
        $builder->setModel($this->getMockModel());
        $builder->shouldReceive('get')->with(array('column'))->andReturn('baz');

        $result = $builder->find(array(1, 2), array('column'));
        $this->assertEquals('baz', $result);
    }

    public function testFirstMethod()
    {
        $builder = m::mock('Vinelab\NeoEloquent\Eloquent\Builder[get,take]', array($this->getMockQueryBuilder()));
        $builder->shouldReceive('take')->with(1)->andReturn($builder);
        $builder->shouldReceive('get')->with(array('*'))->andReturn(new Collection(array('bar')));

        $result = $builder->first();
        $this->assertEquals('bar', $result);
    }

    public function testGetMethodLoadsModelsAndHydratesEagerRelations()
    {
        $builder = m::mock('Vinelab\NeoEloquent\Eloquent\Builder[getModels,eagerLoadRelations]', array($this->getMockQueryBuilder()));
        $builder->shouldReceive('getModels')->with(array('foo'))->andReturn(array('bar'));
        $builder->shouldReceive('eagerLoadRelations')->with(array('bar'))->andReturn(array('bar', 'baz'));
        $builder->setModel($this->getMockModel());
        $builder->getModel()->shouldReceive('newCollection')->with(array('bar', 'baz'))->andReturn(new Collection(array('bar', 'baz')));

        $results = $builder->get(array('foo'));
        $this->assertEquals(array('bar', 'baz'), $results->all());
    }

    public function testGetMethodDoesntHydrateEagerRelationsWhenNoResultsAreReturned()
    {
        $builder = m::mock('Vinelab\NeoEloquent\Eloquent\Builder[getModels,eagerLoadRelations]', array($this->getMockQueryBuilder()));
        $builder->shouldReceive('getModels')->with(array('foo'))->andReturn(array());
        $builder->shouldReceive('eagerLoadRelations')->never();
        $builder->setModel($this->getMockModel());
        $builder->getModel()->shouldReceive('newCollection')->with(array())->andReturn(new Collection(array()));

        $results = $builder->get(array('foo'));
        $this->assertEquals(array(), $results->all());
    }

    public function testPluckMethodWithModelFound()
    {
        $builder = m::mock('Vinelab\NeoEloquent\Eloquent\Builder[first]', array($this->getMockQueryBuilder()));
        $mockModel = new StdClass();
        $mockModel->name = 'foo';
        $builder->shouldReceive('first')->with(array('name'))->andReturn($mockModel);

        $this->assertEquals('foo', $builder->pluck('name'));
    }

    public function testPluckMethodWithModelNotFound()
    {
        $builder = m::mock('Vinelab\NeoEloquent\Eloquent\Builder[first]', array($this->getMockQueryBuilder()));
        $builder->shouldReceive('first')->with(array('name'))->andReturn(null);

        $this->assertNull($builder->pluck('name'));
    }

    public function testChunkExecuteCallbackOverPaginatedRequest()
    {
        $builder = m::mock('Vinelab\NeoEloquent\Eloquent\Builder[forPage,get]', array($this->getMockQueryBuilder()));
        $builder->shouldReceive('forPage')->once()->with(1, 2)->andReturn($builder);
        $builder->shouldReceive('forPage')->once()->with(2, 2)->andReturn($builder);
        $builder->shouldReceive('forPage')->once()->with(3, 2)->andReturn($builder);
        $builder->shouldReceive('get')->times(3)->andReturn(array('foo1', 'foo2'), array('foo3'), array());

        $callbackExecutionAssertor = m::mock('StdClass');
        $callbackExecutionAssertor->shouldReceive('doSomething')->with('foo1')->once();
        $callbackExecutionAssertor->shouldReceive('doSomething')->with('foo2')->once();
        $callbackExecutionAssertor->shouldReceive('doSomething')->with('foo3')->once();

        $builder->chunk(2, function ($results) use ($callbackExecutionAssertor) {
            foreach ($results as $result) {
                $callbackExecutionAssertor->doSomething($result);
            }
        });

        self::assertTrue(true);
    }

    public function testListsReturnsTheMutatedAttributesOfAModel()
    {
        $builder = $this->getBuilder();
        $builder->getQuery()->shouldReceive('lists')->with('name', '')->andReturn(array('bar', 'baz'));
        $builder->setModel($this->getMockModel());
        $builder->getModel()->shouldReceive('hasGetMutator')->with('name')->andReturn(true);
        $builder->getModel()->shouldReceive('newFromBuilder')->with(array('name' => 'bar'))->andReturn(new EloquentBuilderTestListsStub(array('name' => 'bar')));
        $builder->getModel()->shouldReceive('newFromBuilder')->with(array('name' => 'baz'))->andReturn(new EloquentBuilderTestListsStub(array('name' => 'baz')));

        $this->assertEquals(new Collection(['foo_bar', 'foo_baz']), $builder->lists('name'));
    }

    public function testListsWithoutModelGetterJustReturnTheAttributesFoundInDatabase()
    {
        $builder = $this->getBuilder();
        $builder->getQuery()->shouldReceive('lists')->with('name', '')->andReturn(array('bar', 'baz'));
        $builder->setModel($this->getMockModel());
        $builder->getModel()->shouldReceive('hasGetMutator')->with('name')->andReturn(false);

        $this->assertEquals(new Collection(['bar', 'baz']), $builder->lists('name'));
    }

    public function testGetModelsProperlyHydratesModels()
    {
        $query = $this->getMockQueryBuilder();
        $query->columns = array('n.name', 'n.age');

        $builder = M::mock('Vinelab\NeoEloquent\Eloquent\Builder[get]', array($query));

        $records[] = array('id' => 1902, 'name' => 'taylor', 'age' => 26);
        $records[] = array('id' => 6252, 'name' => 'dayle', 'age' => 28);

        $resultSet = $this->createNodeResultSet($records, array('n.name', 'n.age'));

        $builder->getQuery()->shouldReceive('get')->once()->with(array('foo'))->andReturn($resultSet);
        $grammar = M::mock('Vinelab\NeoEloquent\Query\Grammars\CypherGrammar')->makePartial();
        $builder->getQuery()->shouldReceive('getGrammar')->andReturn($grammar);

        $model = M::mock('Vinelab\NeoEloquent\Eloquent\Model[nodeLabel,getConnectionName,newInstance]');
        $model->shouldReceive('nodeLabel')->once()->andReturn('foo_table');

        $builder->setModel($model);

        $model->shouldReceive('getConnectionName')->once()->andReturn('foo_connection');
        $model->shouldReceive('newInstance')->andReturnUsing(function () { return new EloquentBuilderTestModelStub(); });
        $models = $builder->getModels(array('foo'));

        $this->assertEquals('taylor', $models[0]->name);
        $this->assertEquals($models[0]->getAttributes(), $models[0]->getOriginal());
        $this->assertEquals('dayle', $models[1]->name);
        $this->assertEquals($models[1]->getAttributes(), $models[1]->getOriginal());
        $this->assertEquals('foo_connection', $models[0]->getConnectionName());
        $this->assertEquals('foo_connection', $models[1]->getConnectionName());
    }

    public function testEagerLoadRelationsLoadTopLevelRelationships()
    {
        $builder = m::mock('Vinelab\NeoEloquent\Eloquent\Builder[loadRelation]', array($this->getMockQueryBuilder()));
        $nop1 = function () {};
        $nop2 = function () {};
        $builder->setEagerLoads(array('foo' => $nop1, 'foo.bar' => $nop2));
        $builder->shouldAllowMockingProtectedMethods()->shouldReceive('loadRelation')->with(array('models'), 'foo', $nop1)->andReturn(array('foo'));

        $results = $builder->eagerLoadRelations(array('models'));
        $this->assertEquals(array('foo'), $results);
    }

    public function testGetRelationProperlySetsNestedRelationships()
    {
        $builder = $this->getBuilder();
        $builder->setModel($this->getMockModel());
        $builder->getModel()->shouldReceive('orders')->once()->andReturn($relation = m::mock('stdClass'));
        $relationQuery = m::mock('stdClass');
        $relation->shouldReceive('getQuery')->andReturn($relationQuery);
        $relationQuery->shouldReceive('with')->once()->with(array('lines' => null, 'lines.details' => null));
        $builder->setEagerLoads(array('orders' => null, 'orders.lines' => null, 'orders.lines.details' => null));

        $relation = $builder->getRelation('orders');

        self::assertInstanceOf(stdClass::class, $relation);
    }

    public function testGetRelationProperlySetsNestedRelationshipsWithSimilarNames()
    {
        $builder = $this->getBuilder();
        $builder->setModel($this->getMockModel());
        $builder->getModel()->shouldReceive('orders')->once()->andReturn($relation = m::mock('stdClass'));
        $builder->getModel()->shouldReceive('ordersGroups')->once()->andReturn($groupsRelation = m::mock('stdClass'));

        $relationQuery = m::mock('stdClass');
        $relation->shouldReceive('getQuery')->andReturn($relationQuery);

        $groupRelationQuery = m::mock('stdClass');
        $groupsRelation->shouldReceive('getQuery')->andReturn($groupRelationQuery);
        $groupRelationQuery->shouldReceive('with')->once()->with(array('lines' => null, 'lines.details' => null));

        $builder->setEagerLoads(array('orders' => null, 'ordersGroups' => null, 'ordersGroups.lines' => null, 'ordersGroups.lines.details' => null));

        $relation = $builder->getRelation('orders');
        $relation = $builder->getRelation('ordersGroups');

        self::assertInstanceOf(stdClass::class, $relation);
    }

    public function testEagerLoadParsingSetsProperRelationships()
    {
        $builder = $this->getBuilder();
        $builder->with(array('orders', 'orders.lines'));
        $eagers = $builder->getEagerLoads();

        $this->assertEquals(array('orders', 'orders.lines'), array_keys($eagers));
        $this->assertInstanceOf('Closure', $eagers['orders']);
        $this->assertInstanceOf('Closure', $eagers['orders.lines']);

        $builder = $this->getBuilder();
        $builder->with('orders', 'orders.lines');
        $eagers = $builder->getEagerLoads();

        $this->assertEquals(array('orders', 'orders.lines'), array_keys($eagers));
        $this->assertInstanceOf('Closure', $eagers['orders']);
        $this->assertInstanceOf('Closure', $eagers['orders.lines']);

        $builder = $this->getBuilder();
        $builder->with(array('orders.lines'));
        $eagers = $builder->getEagerLoads();

        $this->assertEquals(array('orders', 'orders.lines'), array_keys($eagers));
        $this->assertInstanceOf('Closure', $eagers['orders']);
        $this->assertInstanceOf('Closure', $eagers['orders.lines']);

        $builder = $this->getBuilder();
        $builder->with(array('orders' => function () { return 'foo'; }));
        $eagers = $builder->getEagerLoads();

        $this->assertEquals('foo', $eagers['orders']());

        $builder = $this->getBuilder();
        $builder->with(array('orders.lines' => function () { return 'foo'; }));
        $eagers = $builder->getEagerLoads();

        $this->assertInstanceOf('Closure', $eagers['orders']);
        $this->assertNull($eagers['orders']());
        $this->assertEquals('foo', $eagers['orders.lines']());
    }

    public function testQueryPassThru()
    {
        $builder = $this->getBuilder();
        $model = \Vinelab\NeoEloquent\Eloquent\Model::class;
        $model = M::mock($model);
        $model->shouldReceive('nodeLabel')->once()->andReturn('Model');
        $builder->setModel($model);
        $builder->getQuery()->shouldReceive('foobar')->once()->andReturn('foo');

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Builder', $builder->foobar());

        $builder = $this->getBuilder();
        $model = \Vinelab\NeoEloquent\Eloquent\Model::class;
        $model = M::mock($model);
        $model->shouldReceive('nodeLabel')->once()->andReturn('Model');
        $builder->setModel($model);
        $builder->getQuery()->shouldReceive('insert')->once()->with(array('bar'))->andReturn('foo');

        $this->assertEquals('foo', $builder->insert(array('bar')));
    }

    public function testQueryScopes()
    {
        $builder = $this->getBuilder();
        $builder->getQuery()->shouldReceive('from');
        $builder->getQuery()->shouldReceive('where')->once()->with('foo', 'bar');
        $builder->setModel($model = new EloquentBuilderTestScopeStub());
        $result = $builder->approved();

        $this->assertEquals($builder, $result);
    }

    public function testSimpleWhere()
    {
        $builder = $this->getBuilder();
        $builder->getQuery()->shouldReceive('where')->once()->with('foo', '=', 'bar');
        $result = $builder->where('foo', '=', 'bar');
        $this->assertEquals($result, $builder);
    }

    public function testNestedWhere()
    {
        $this->markTestIncomplete('Getting error: Static method Mockery_1_Vinelab_NeoEloquent_Eloquent_Model::resolveConnection() does not exist on this mock object');

        $nestedQuery = m::mock('Vinelab\NeoEloquent\Eloquent\Builder');
        $nestedRawQuery = $this->getMockQueryBuilder();
        $nestedQuery->shouldReceive('getQuery')->once()->andReturn($nestedRawQuery);
        $model = $this->getMockModel()->makePartial();
        $model->shouldReceive('newQueryWithoutScopes')->once()->andReturn($nestedQuery);
        $builder = $this->getBuilder();
        $builder->getQuery()->shouldReceive('from');
        $builder->setModel($model);
        $builder->getQuery()->shouldReceive('addNestedWhereQuery')->once()->with($nestedRawQuery, 'and');
        $nestedQuery->shouldReceive('foo')->once();

        $result = $builder->where(function ($query) { $query->foo(); });
        $this->assertEquals($builder, $result);
    }

    public function testDeleteOverride()
    {
        $this->markTestIncomplete('Getting the error BadMethodCallException: Method Mockery_2_Vinelab_NeoEloquent_Query_Builder::onDelete() does not exist on this mock object');
        $builder = $this->getBuilder();
        $builder->onDelete(function ($builder) {
            return array('foo' => $builder);
        });
        $this->assertEquals(array('foo' => $builder), $builder->delete());
    }

    public function testFindingById()
    {
        $this->query->shouldReceive('getGrammar')->andReturn(new CypherGrammar());

        $resultSet = new CypherList([ new CypherMap(['node' => new \Laudis\Neo4j\Types\Node(1, new CypherList(), new CypherMap())])]);

        $this->query->shouldReceive('where')->once()->with('id(n)', '=', 1);
        $this->query->shouldReceive('from')->once()->with('Model')->andReturn(array('Model'));
        $this->query->shouldReceive('take')->once()->with(1)->andReturn($this->query);
        $this->query->shouldReceive('get')->once()->with(array('*'))->andReturn($resultSet);

        $this->model->shouldReceive('getKeyName')->times()->andReturn('id');
        $this->model->shouldReceive('nodeLabel')->once()->andReturn('Model');
        $this->model->shouldReceive('getConnectionName')->once()->andReturn('default');

        $result = M::mock('Neoxygen\NeoClient\Formatter\Result');
        $collection = new \Illuminate\Support\Collection(array($result));
        $this->model->shouldReceive('newCollection')->once()->andReturn($collection);
        $this->model->shouldReceive('getAttributes')->once()->andReturn([]);
        $this->model->shouldReceive('setConnection')->once();
        $this->model->shouldReceive('newFromBuilder')->once()->andReturn($this->model);

        $this->builder->setModel($this->model);

        $result = $this->builder->find(1);

        $this->assertInstanceOf('Neoxygen\NeoClient\Formatter\Result', $result);
    }

    public function testFindingByIdWithProperties()
    {
        // the intended Node id
        $id = 6;

        // the expected result set
        $result = array(

            'id' => $id,
            'name' => 'Some Name',
            'email' => 'some@mail.net',
        );

        // the properties that we need returned of our model
        $properties = array('id(n)', 'n.name', 'n.email', 'n.somthing');

        $resultSet = $this->createNodeResultSet($result, $properties);

        // usual query expectations
        $this->query->shouldReceive('where')->once()->with('id(n)', '=', $id)
                    ->shouldReceive('take')->once()->with(1)->andReturn($this->query)
                    ->shouldReceive('get')->once()->with($properties)->andReturn($resultSet)
                    ->shouldReceive('from')->once()->with('Model')
                        ->andReturn(array('Model'));

        // our User object that we expect to have returned
        $user = M::mock('User');
        $user->shouldReceive('setConnection')->once()->with('default');

        // model method calls expectations
        $attributes = array_merge($result, array('id' => $id));

        // the Collection that represents the returned result by Eloquent holding the User as an item
        $collection = new \Illuminate\Support\Collection(array($user));

        $this->model->shouldReceive('newCollection')->once()->andReturn($collection)
                    ->shouldReceive('getKeyName')->times(3)->andReturn('id')
                    ->shouldReceive('nodeLabel')->once()->andReturn('Model')
                    ->shouldReceive('getConnectionName')->once()->andReturn('default')
                    ->shouldReceive('newFromBuilder')->once()->with($attributes)->andReturn($user);

        // assign the builder's $model to our mock
        $this->builder->setModel($this->model);
        $grammar = M::mock('Vinelab\NeoEloquent\Query\Grammars\CypherGrammar')->makePartial();
        $this->query->shouldReceive('getGrammar')->andReturn($grammar);
        // put things to the test
        $this->model->shouldReceive('getAttributes')->once()->andReturn([]);
        $found = $this->builder->find($id, $properties);

        $this->assertInstanceOf('User', $found);
    }

    public function testGettingModels()
    {
        // the expected result set
        $results = array(

            array(

                'id' => 10,
                'name' => 'Some Name',
                'email' => 'some@mail.net',
            ),

            array(

                'id' => 11,
                'name' => 'Another Person',
                'email' => 'person@diff.io',
            ),

        );

        $resultSet = $this->createNodeResultSet($results);

        $grammar = M::mock('Vinelab\NeoEloquent\Query\Grammars\CypherGrammar')->makePartial();
        $this->query->shouldReceive('get')->once()->with(array('*'))->andReturn($resultSet)
                    ->shouldReceive('from')->once()->andReturn('User')
                    ->shouldReceive('getGrammar')->andReturn($grammar);

        // our User object that we expect to have returned
        $user = M::mock('User');
        $user->shouldReceive('setConnection')->twice()->with('default');

        $this->model->shouldReceive('nodeLabel')->once()->andReturn('User')
                    ->shouldReceive('getKeyName')->twice()->andReturn('id')
                    ->shouldReceive('getConnectionName')->once()->andReturn('default')
                    ->shouldReceive('newFromBuilder')->once()
                        ->with($results[0])->andReturn($user)
                    ->shouldReceive('newFromBuilder')->once()
                        ->with($results[1])->andReturn($user)
                    ->shouldReceive('getAttributes')->andReturn([]);

        $this->builder->setModel($this->model);

        $models = $this->builder->getModels();

        $this->assertIsArray($models);
        $this->assertInstanceOf('User', $models[0]);
        $this->assertInstanceOf('User', $models[1]);
    }

    public function testGettingModelsWithProperties()
    {
        // the expected result set
        $results = array(
            'id' => 138,
            'name' => 'Nicolas Jaar',
            'email' => 'noise@space.see',
        );

        $properties = array('id', 'name');

        $resultSet = $this->createNodeResultSet($results);

        $grammar = M::mock('Vinelab\NeoEloquent\Query\Grammars\CypherGrammar')->makePartial();
        $this->query->shouldReceive('get')->once()->with($properties)->andReturn($resultSet)
                    ->shouldReceive('from')->once()->andReturn('User')
                    ->shouldReceive('getGrammar')->andReturn($grammar);

        // our User object that we expect to have returned
        $user = M::mock('User');
        $user->shouldReceive('setConnection')->once()->with('default');

        $this->model->shouldReceive('nodeLabel')->once()->andReturn('User')
                    ->shouldReceive('getKeyName')->once()->andReturn('id')
                    ->shouldReceive('getConnectionName')->once()->andReturn('default')
                    ->shouldReceive('newFromBuilder')->once()
                        ->with($results)->andReturn($user)
                    ->shouldReceive('getAttributes')->once()->andReturn([]);

        $this->builder->setModel($this->model);

        $models = $this->builder->getModels($properties);

        $this->assertIsArray($models);
        $this->assertInstanceOf('User', $models[0]);
    }

    public function testCheckingIsRelationship()
    {
        $this->assertTrue($this->builder->isRelationship(['user', 'account']));
        $this->assertFalse($this->builder->isRelationship(['user.name', 'account.id']));
        $this->assertFalse($this->builder->isRelationship(['user', 'user.name', 'account.id']));
    }

    /**
     *     Utility methods down below this area.
     */

    /**
     * Create a new ResultSet out of an array of properties and values.
     *
     * @param array $data       The values you want returned could be of the form
     *                          [ [name => something, username => here] ]
     *                          or specify the attributes straight in the array
     * @param array $properties The expected properties (columns)
     *
     * @return CypherList
     */
    public function createNodeResultSet($data = array(), $properties = array())
    {
        $result = [];

        if (is_array(reset($data))) {
            foreach ($data as $index => $attributes) {
                $result[] = new CypherMap(['node' => $this->createNode($attributes)]);
            }
        } else {
            $node = $this->createNode($data);
            $result[] = new CypherMap(['node' => $node]);
        }

        // the ResultSet $result part

        return new CypherList($result);
    }

    /**
     * Get a row with a Node inside of it having $data as properties.
     *
     * @param array $data
     *
     * @return \Laudis\Neo4j\Types\Node
     */
    public function createNode(array $data)
    {
        return new \Laudis\Neo4j\Types\Node($data['id'], new CypherList(), new CypherMap($data));
    }

    public function createRowWithPropertiesAtIndex($index, array $properties)
    {
        $row = M::mock('Everyman\Neo4j\Query\Row');
        // $row->shouldReceive('offsetGet')->with($index)->andReturn($properties);

        foreach ($properties as $key => $value) {
            // prepare the row's offsetGet to rerturn the desired value when asked
            // by prepending the key with an n. representing the node in the Cypher query.
            $row->shouldReceive('offsetGet')
                ->with("n.{$key}")
                ->andReturn($properties[$key]);

            $row->shouldReceive('offsetGet')
                ->with("{$key}")
                ->andReturn($properties[$key]);
        }

        return $row;
    }

    protected function getMockModel()
    {
        $model = m::mock('Vinelab\NeoEloquent\Eloquent\Model');
        $model->shouldReceive('getKeyName')->andReturn('foo');
        $model->shouldReceive('nodeLabel')->andReturn('foo_table');
        $model->shouldReceive('getQualifiedKeyName')->andReturn('foo');

        return $model;
    }

    protected function getMockQueryBuilder()
    {
        $query = m::mock('Vinelab\NeoEloquent\Query\Builder');
        $query->shouldReceive('from')->with('foo_table');
        $query->shouldReceive('modelAsNode')->andReturn('n');

        return $query;
    }

    protected function getBuilder()
    {
        $query = M::mock('Vinelab\NeoEloquent\Query\Builder');
        $query->shouldReceive('from')->andReturn('foo_table');
        $query->shouldReceive('modelAsNode')->andReturn('n');
        return new Builder($query);
    }
}

// Don't ask what this is, brought in from
// laravel/framework/tests/Databases/DatabaseEloquentBuilderTest.php
// and it makes the tests pass, so leave it :P
class EloquentBuilderTestModelStub extends \Vinelab\NeoEloquent\Eloquent\Model
{
}

class EloquentBuilderTestScopeStub extends \Vinelab\NeoEloquent\Eloquent\Model
{
    public function scopeApproved($query)
    {
        $query->where('foo', 'bar');
    }
}

class EloquentBuilderTestListsStub
{
    protected $attributes;

    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }
    public function __get($key)
    {
        return 'foo_'.$this->attributes[$key];
    }
}
