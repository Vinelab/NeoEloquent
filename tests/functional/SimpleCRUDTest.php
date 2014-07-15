<?php namespace Vinelab\NeoEloquent\Tests\Functional;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\SoftDeletingTrait;

class Wiz extends Model {

    protected $label = ':Wiz';

    protected $fillable = ['fiz', 'biz', 'triz'];
}

class WizDel extends Model {

    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];

    protected $label = ':Wiz';

    protected $fillable = ['fiz', 'biz', 'triz'];
}

class SimpleCRUDTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));
        Wiz::setConnectionResolver($resolver);
    }

    public function tearDown()
    {
        M::close();

        // Mama said, always clean up before you go. =D
        $w = Wiz::all();
        $w->each(function($me){ $me->delete(); });

        parent::tearDown();
    }

    /**
     * @expectedException \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testFindingAndFailing()
    {
        User::findOrFail(0);
    }

    public function testCreatingRecord()
    {
        $w = new Wiz(['fiz' => 'foo', 'biz' => 'boo']);

        $this->assertTrue($w->save());
        $this->assertTrue($w->exists);
        $this->assertInternalType('int', $w->id);
        $this->assertTrue($w->id > 0);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Wiz', $w);
    }

    /**
     * @depends testCreatingRecord
     */
    public function testFindingRecordById()
    {
        $w = new Wiz(['fiz' => 'foo', 'biz' => 'boo']);

        $this->assertTrue($w->save());
        $this->assertTrue($w->exists);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Wiz', $w);

        $w2 = Wiz::find($w->id);
        $this->assertEquals($w, $w2);
    }

    /**
     * depends testFindingRecordById
     */
    public function testDeletingRecord()
    {
        $w = new Wiz(['fiz' => 'foo', 'biz' => 'boo']);
        $w->save();

        $this->assertTrue($w->delete());
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Wiz', $w);
        $this->assertFalse($w->exists);
    }

    /**
     * @depends testCreatingRecord
     */
    public function testMassAssigningAttributes()
    {
        $w = Wiz::create([
            'fiz' => 'foo',
            'biz' => 'boo',
            'nope' => 'nope'
        ]);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Wiz', $w);
        $this->assertTrue($w->exists);
        $this->assertInternalType('int', $w->id);
        $this->assertNull($w->nope);
    }

    /**
     * @depends testMassAssigningAttributes
     * @depends testFindingRecordById
     */
    public function testUpdatingFreshRecord()
    {
        $w = Wiz::create([
            'fiz' => 'foo',
            'biz' => 'boo'
        ]);

        $found = Wiz::find($w->id);
        $this->assertNull($found->nectar, 'make sure it is not there first, just in case some alien invasion put it or something');

        $w->nectar = 'pulp'; // yummy, freshly saved!
        $this->assertTrue($w->save());

        $after = Wiz::find($w->id);

        $this->assertEquals('pulp', $w->nectar);
        $this->assertEquals('pulp', $after->nectar);
    }

    /**
     * @depends testMassAssigningAttributes
     * @depends testFindingRecordById
     */
    public function testUpdatingRecordFoundById()
    {
        $w = Wiz::create([
            'fiz' => 'foo',
            'biz' => 'boo'
        ]);

        $found = Wiz::find($w->id);
        $this->assertNull($found->hurry, 'make sure it is not there first, just in case some alien invasion put it or something');

        $found->hurry = 'up';
        $this->assertTrue($found->save());

        $after = Wiz::find($w->id);

        $this->assertEquals('up', $found->hurry);
        $this->assertEquals('up', $after->hurry);
    }

    public function testInsertingBatch()
    {
        $batch = [
            [
                'fiz' => 'foo',
                'biz' => 'boo'
            ],
            [
                'fiz' => 'morefoo',
                'biz' => 'moreboo'
            ],
            [
                'fiz' => 'otherfoo',
                'biz' => 'otherboo'
            ],
            [
                'fiz' => 'somefoo',
                'biz' => 'someboo'
            ]
        ];

        $inserted = Wiz::insert($batch);

        $this->assertTrue($inserted);

        // Let's fetch them to see if that's really true.
        $wizzez = Wiz::all();

        foreach ($wizzez as $key => $wizz)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Wiz', $wizz);
            $values = $wizz->toArray();
            $this->assertArrayHasKey('id', $values);
            $this->assertGreaterThanOrEqual(0, $values['id']);
            unset($values['id']);
            $this->assertEquals($batch[$key], $values);
        }
    }

    public function testInsertingSingleAndGettingId()
    {
        $id = Wiz::insertGetId(['foo' => 'fiz', 'boo' => 'biz']);

        $this->assertInternalType('int', $id);
        $this->assertGreaterThan(0, $id, 'message');
    }

    public function testSavingBooleanValuesStayBoolean()
    {
        $w = Wiz::create(['fiz' => true, 'biz' => false]);

        $g = Wiz::find($w->id);
        $this->assertTrue($g->fiz);
        $this->assertFalse($g->biz);
    }

    public function testNumericValuesPreserveDataTypes()
    {
        $w = Wiz::create(['fiz' => 1, 'biz' => 8.276123, 'triz' => 0]);

        $g = Wiz::find($w->id);
        $this->assertInternalType('int', $g->fiz);
        $this->assertInternalType('int', $g->triz);
        $this->assertInternalType('float', $g->biz);
    }

    public function testSoftDeletingModel()
    {
        $w = WizDel::create([]);

        $g = WizDel::all()->first();
        $g->delete();
        $this->assertFalse($g->exists);
        $this->assertInstanceOf('Carbon\Carbon', $g->deleted_at);
    }

    public function testGettingModelCount()
    {
        $count = WizDel::count();
        $this->assertEquals(0, $count);

        WizDel::create([]);
        $countAfter = WizDel::count();
        $this->assertEquals(1, $countAfter);
    }

    public function testFirstOrCreate()
    {
        $w = Wiz::firstOrCreate([
            'fiz' => 'foo',
            'biz' => 'boo',
            'triz' => 'troo'
        ]);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Wiz', $w);

        $found = Wiz::firstOrCreate([
            'fiz' => 'foo',
            'biz' => 'boo',
            'triz' => 'troo'
        ]);

        $this->assertEquals($w, $found);
    }
}
