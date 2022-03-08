<?php

namespace Vinelab\NeoEloquent\Tests\Eloquent;

use Vinelab\NeoEloquent\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Model as NeoEloquent;
use Vinelab\NeoEloquent\Query\Builder as BaseBuilder;
use Vinelab\NeoEloquent\Tests\TestCase;

class Model extends NeoEloquent
{
}

class Labeled extends NeoEloquent
{
    protected $table = 'Labeled';

    protected $fillable = ['a'];
}

class Table extends NeoEloquent
{
    protected $table = 'Table';
}

class ModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->getConnection()->getPdo()->run('MATCH (x) DETACH DELETE x');
    }

    public function testDefaultNodeLabel(): void
    {
        $label = (new Model())->getLabel();

        $this->assertEquals('Model', $label);
    }

    public function testOverriddenNodeLabel(): void
    {
        $label = (new Labeled())->getLabel();

        $this->assertEquals('Labeled', $label);
    }

    public function testLabelBackwardCompatibilityWithTable(): void
    {
        $label = (new Table())->nodeLabel();

        $this->assertEquals('Table', $label);
    }

    public function testSettingLabelAtRuntime(): void
    {
        $m = new Model();

        $m->setLabel('Padrouga');

        $label = $m->getLabel();

        $this->assertEquals('Padrouga', $label);
    }

    public function testDifferentTypesOfLabelsAlwaysLandsAnArray(): void
    {
        $m = new Model();

        $m->setLabel('User:Fan');
        $label = $m->getLabel();
        $this->assertEquals('User:Fan', $label);
    }

    public function testGettingEloquentBuilder(): void
    {
        $this->assertInstanceOf(Builder::class, (new Model())->newQuery());
        $this->assertInstanceOf(Builder::class, (new Model())->newQueryForRestoration([]));
        $this->assertInstanceOf(Builder::class, (new Model())->newQueryWithoutRelationships());
        $this->assertInstanceOf(Builder::class, (new Model())->newQueryWithoutScope('x'));
        $this->assertInstanceOf(Builder::class, (new Model())->newQueryWithoutScopes());
        $this->assertInstanceOf(Builder::class, (new Model())->newModelQuery());

        $query = new BaseBuilder($this->getConnection());
        $this->assertInstanceOf(Builder::class, (new Model())->newEloquentBuilder($query));
    }

    public function testAddLabels(): void
    {
        //create a new model object
        $m = Labeled::create(['a' => 'b']);

        //add the label
        $m->addLabels(['Superuniqelabel1']);

        $this->assertEquals(1, $this->getConnection()->query()->count());
        $this->assertEquals(1, $this->getConnection()->query()->from('SuperUniqueLabel')->count());
        $this->assertEquals(1, $this->getConnection()->query()->from('Labeled')->count());
    }

    public function testDropLabels(): void
    {
        //create a new model object
        $m = new Labeled();
        $m->setLabel(['User', 'Fan', 'Superuniqelabel2']); //set some labels
        $m->save();
        //get the node id, we need it to verify if the label is actually added in graph
        $id = $m->id;

        //drop the label
        $m->dropLabels(['Superuniqelabel2']);
        $this->assertFalse(in_array('Superuniqelabel2', $this->getNodeLabels($id)));
    }
}
