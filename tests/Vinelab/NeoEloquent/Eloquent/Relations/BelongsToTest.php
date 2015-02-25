<?php namespace Vinelab\NeoEloquent\Tests\Eloquent\Relations;

use Mockery as M;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;
use Vinelab\NeoEloquent\Eloquent\Relations\BelongsTo;

class BelongsToTest extends TestCase  {

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));
        Stub::setConnectionResolver($resolver);
    }

    public function testRelationInitializationAddsConstraints()
    {
        $relation = $this->getRelation();
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Relations\BelongsTo', $relation);
    }

    public function testUpdateMethodRetrievesModelAndUpdates()
    {
        $relation = $this->getRelation();
        $mock = M::mock('Vinelab\NeoEloquent\Eloquent\Model');
        $mock->shouldReceive('fill')->once()->with(array('attributes'))->andReturn($mock);
        $mock->shouldReceive('save')->once()->andReturn(true);
        $relation->getQuery()->shouldReceive('first')->once()->andReturn($mock);

        $this->assertTrue($relation->update(array('attributes')));
    }

    public function testEagerConstraintsAreProperlyAdded()
    {
        $models = [new Stub(['id' => 1]), new Stub(['id' => 2]), new Stub(['id' => 3])];
        $relation = $this->getEagerRelation($models);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Relations\BelongsTo', $relation);
    }

    public function testRelationIsProperlyInitialized()
    {
        $relation = $this->getRelation();
        $model = M::mock('Vinelab\NeoEloquent\Eloquent\Model');
        $model->shouldReceive('setRelation')->once()->with('foo', null);
        $models = $relation->initRelation(array($model), 'foo');

        $this->assertEquals(array($model), $models);
    }

    public function testModelsAreProperlyMatchedToParents()
    {
        $this->markTestIncomplete('We should be testing mutations');

        $relation = $this->getRelation();
        $result1 = M::mock('stdClass');
        $result1->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $result2 = M::mock('stdClass');
        $result2->shouldReceive('getAttribute')->with('id')->andReturn(2);
        $model1 = new Stub;
        $model1->foreign_key = 1;
        $model2 = new Stub;
        $model2->foreign_key = 2;
        $models = $relation->match(array($model1, $model2), new Collection(array($result1, $result2)), 'foo');

        $this->assertEquals(1, $models[0]->foo->getAttribute('id'));
        $this->assertEquals(2, $models[1]->foo->getAttribute('id'));
    }

    public function testMutationsAreProperlySet()
    {
        $this->markTestIncomplete();
    }

    protected function getEagerRelation($models)
    {

        $query = M::mock('Vinelab\NeoEloquent\Query\Builder');
        $query ->shouldReceive('modelAsNode')->with(array('Stub'))->andReturn('parent');

        $builder = M::mock('Vinelab\NeoEloquent\Eloquent\Builder');
        $builder->shouldReceive('getQuery')->times(4)->andReturn($query);
        $builder->shouldReceive('select')->once()->with('relation');
        $builder->shouldReceive('select')->once()->with('relation', 'parent');

        $related = M::mock('Vinelab\NeoEloquent\Eloquent\Model')->makePartial();
        $related->shouldReceive('getKeyName')->andReturn('id');
        $related->shouldReceive('getTable')->andReturn('relation');

        $id = 19;
        $parent = new Stub(['id' => $id]);

        $builder->shouldReceive('getModel')->once()->andReturn($related);
        $builder->shouldReceive('addMutation')->once()->with('relation', $related);
        $builder->shouldReceive('addMutation')->once()->with('parent', $parent);

        $builder->shouldReceive('where')->once()->with('id', '=', $id);

        $builder->shouldReceive('matchIn')->twice()
            ->with($parent, $related, 'relation', 'RELATIONSHIP', 'id', $id);

        $relation = new belongsTo($builder, $parent, 'RELATIONSHIP', 'id', 'relation');

        $builder->shouldReceive('whereIn')->once()
            ->with('id', array_map(function($model){ return $model->id; }, $models));

        $relation->addEagerConstraints($models);

        return $relation;
    }

    protected function getRelation($parent = null)
    {
        $query = M::mock('Vinelab\NeoEloquent\Query\Builder');
        $query ->shouldReceive('modelAsNode')->with(array('Stub'))->andReturn('parent');

        $builder = M::mock('Vinelab\NeoEloquent\Eloquent\Builder');
        $builder->shouldReceive('getQuery')->twice()->andReturn($query);
        $builder->shouldReceive('select')->once()->with('relation');

        $related = M::mock('Vinelab\NeoEloquent\Eloquent\Model')->makePartial();
        $related->shouldReceive('getKeyName')->andReturn('id');
        $related->shouldReceive('getTable')->andReturn('relation');

        $builder->shouldReceive('getModel')->once()->andReturn($related);

        $id = 19;
        $parent = new Stub(['id' => $id]);

        $builder->shouldReceive('matchIn')->once()
            ->with($parent, $related, 'relation', 'RELATIONSHIP', 'id', $id);

        $builder->shouldReceive('where')->once()
            ->with('id', '=', $id);

        return new belongsTo($builder, $parent, 'RELATIONSHIP', 'id', 'relation');
    }

}

class Stub extends Model {

    protected $label = ':Stub';

    protected $fillable = ['id'];
}
