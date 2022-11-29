<?php

namespace Vinelab\NeoEloquent\Tests\Eloquent;

use Illuminate\Database\Eloquent\Builder;
use Vinelab\NeoEloquent\Eloquent\Model as NeoEloquent;
use Vinelab\NeoEloquent\Tests\TestCase;

class Model extends NeoEloquent
{
}

class Labeled extends NeoEloquent
{
    protected $table = 'Labeled';

    protected $fillable = ['a'];

    protected $primaryKey = 'a';
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

    public function testCreateAndFind(): void
    {
        $labeled = Labeled::query()->create(['a' => 'b']);

        $find = Labeled::query()->find('b');

        $this->assertEquals($labeled->getAttributes(), $find->getAttributes());
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
    }
}
