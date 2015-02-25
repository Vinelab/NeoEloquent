<?php namespace Vinelab\NeoEloquent\Tests\Eloquent;

use Mockery as M;
use Vinelab\NeoEloquent\Eloquent\Model as NeoEloquent;
use Vinelab\NeoEloquent\Tests\TestCase;

class Model extends NeoEloquent {

}

class Labeled extends NeoEloquent {

    protected $label = 'Labeled';
}

class Table extends NeoEloquent {

    protected $table = 'Table';
}

class ModelTest extends TestCase {

    public function testDefaultNodeLabel()
    {
        $m = new Model;

        $label = $m->getDefaultNodeLabel();

        // By default the label should be the concatenation of the class's namespace
        $this->assertEquals('VinelabNeoEloquentTestsEloquentModel', reset($label));
    }

    public function testOverriddenNodeLabel()
    {
        $m = new Labeled;

        $label = $m->getDefaultNodeLabel();

        $this->assertEquals('Labeled', reset($label));
    }

    public function testLabelBackwardCompatibilityWithTable()
    {
        $m = new Table;

        $label = $m->getTable();

        $this->assertEquals('Table', reset($label));
    }

    public function testSettingLabelAtRuntime()
    {
        $m = new Model;

        $m->setLabel('Padrouga');

        $label = $m->getDefaultNodeLabel();

        $this->assertEquals('Padrouga', reset($label));
    }

    public function testDifferentTypesOfLabelsAlwaysLandsAnArray()
    {
        $m = new Model;

        $m->setLabel(array('User', 'Fan'));
        $label = $m->getDefaultNodeLabel();
        $this->assertEquals(array('User', 'Fan'), $label);

        $m->setLabel(':User:Fan');
        $label = $m->getDefaultNodeLabel();
        $this->assertEquals(array('User', 'Fan'), $label);

        $m->setLabel('User:Fan:Maker:Baker');
        $label = $m->getDefaultNodeLabel();
        $this->assertEquals(array('User', 'Fan', 'Maker', 'Baker'), $label);
    }

    public function testGettingEloquentBuilder()
    {
        $m = new Model;

        $builder = $m->newEloquentBuilder(M::mock('Vinelab\NeoEloquent\Query\Builder'));

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Builder', $builder);
    }

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }
}
