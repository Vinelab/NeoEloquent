<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use DateTime;
use Carbon\Carbon;
use Laudis\Neo4j\Types\CypherList;
use Mockery as M;
use Vinelab\NeoEloquent\Exceptions\ModelNotFoundException;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\SoftDeletes;

class Wiz extends Model
{
    protected $label = ['Wiz', 'SOmet'];

    protected $fillable = ['fiz', 'biz', 'triz'];
}

class WizDel extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $label = ':Wiz';

    protected $fillable = ['fiz', 'biz', 'triz'];
}

class SimpleCRUDTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));
        Wiz::setConnectionResolver($resolver);
    }

    public function tearDown(): void
    {
        M::close();

        // Mama said, always clean up before you go. =D
        $w = Wiz::all();
        $w->each(function ($me) { $me->delete(); });

        parent::tearDown();
    }

    public function testFindingAndFailing()
    {
        $this->expectException(ModelNotFoundException::class);
        Wiz::findOrFail(0);
    }

    /**
     * Regression test for issue #27.
     *
     * @see https://github.com/Vinelab/NeoEloquent/issues/27
     */
    public function testDoesntCrashOnNonIntIds()
    {
        $u = Wiz::create([]);
        $id = (string) $u->id;
        $found = Wiz::where('id', "$id")->first();
        $this->assertEquals($found->toArray(), $u->toArray());

        $foundAgain = Wiz::find("$id");
        $this->assertEquals($foundAgain->toArray(), $u->toArray());
    }

    public function testCreatingRecord()
    {
        $w = new Wiz(['fiz' => 'foo', 'biz' => 'boo']);

        $this->assertTrue($w->save());
        $this->assertTrue($w->exists);
        $this->assertIsInt($w->id);
        $this->assertTrue($w->id > 0);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Wiz', $w);
    }

    public function testCreatingRecordWithArrayProperties()
    {
        $w = Wiz::create(['fiz' => ['not', '123', 'helping']]);

        $expected = [
            $w->getKeyName() => $w->getKey(),
            'fiz' => new CypherList(['not', '123', 'helping']),
            'created_at' => $w->created_at->toDateTimeString(),
            'updated_at' => $w->updated_at->toDateTimeString(),
        ];

        $fetched = Wiz::first();
        $this->assertEquals($expected, $fetched->toArray());
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
        $this->assertEquals($w->toArray(), $w2->toArray());
    }

    /**
     * depends testFindingRecordById.
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
            'nope' => 'nope',
        ]);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Wiz', $w);
        $this->assertTrue($w->exists);
        $this->assertIsInt($w->id);
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
            'biz' => 'boo',
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
            'biz' => 'boo',
        ]);

        $found = Wiz::find($w->id);
        $this->assertNull($found->hurry, 'make sure it is not there first, just in case some alien invasion put it or something');

        $found->hurry = 'up';
        $this->assertTrue($found->save());

        $after = Wiz::find($w->id);

        $this->assertEquals('up', $found->hurry);
        $this->assertEquals('up', $after->hurry);
    }

    /**
     * Regression test for issue #18 where querying and updating the same
     * attributes messes up the values and keeps the old ones resulting in a failed update.
     *
     * @see  https://github.com/Vinelab/NeoEloquent/issues/18
     *
     * @return [type] [description]
     */
    public function testUpdatingRecordwithUpdateOnQuery()
    {
        $w = Wiz::create([
            'fiz' => 'foo',
            'biz' => 'boo',
        ]);

        Wiz::where('fiz', '=', 'foo')
            ->where('biz', '=', 'boo')
            ->update(['fiz' => 'notfooanymore', 'biz' => 'noNotBoo!', 'triz' => 'newhere']);

        $found = Wiz::where('fiz', '=', 'notfooanymore')
            ->orWhere('biz', '=', 'noNotBoo!')
            ->orWhere('triz', '=', 'newhere')
            ->first();

        $this->assertEquals($w->getKey(), $found->getKey());
    }

    public function testInsertingBatch()
    {
        $batch = [
            [
                'fiz' => 'foo',
                'biz' => 'boo',
            ],
            [
                'fiz' => 'morefoo',
                'biz' => 'moreboo',
            ],
            [
                'fiz' => 'otherfoo',
                'biz' => 'otherboo',
            ],
            [
                'fiz' => 'somefoo',
                'biz' => 'someboo',
            ],
        ];

        $inserted = Wiz::insert($batch);

        $this->assertTrue($inserted);

        // Let's fetch them to see if that's really true.
        $wizzez = Wiz::all();

        foreach ($wizzez as $key => $wizz) {
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

        $this->assertIsInt($id);
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
        $this->assertIsInt($g->fiz);
        $this->assertIsInt($g->triz);
        $this->assertIsFloat($g->biz);
    }

    public function testSoftDeletingModel()
    {
        $w = WizDel::create([]);

        $g = WizDel::all()->first();
        $g->delete();
        $this->assertFalse($g->exists);
        $this->assertInstanceOf('Carbon\Carbon', $g->deleted_at);
    }

    public function testRestoringSoftDeletedModel()
    {
        $w = WizDel::create([]);

        $g = WizDel::first();
        $g->delete();

        $this->assertFalse($g->exists);
        $this->assertInstanceOf('Carbon\Carbon', $g->deleted_at);

        $h = WizDel::onlyTrashed()->where('id', $g->getKey())->first();
        $this->assertInstanceOf('Carbon\Carbon', $h->deleted_at);
        $this->assertTrue($h->restore());
        $this->assertNull($h->deleted_at);
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
            'triz' => 'troo',
        ]);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Wiz', $w);

        $found = Wiz::firstOrCreate([
            'fiz' => 'foo',
            'biz' => 'boo',
            'triz' => 'troo',
        ]);

        $this->assertEquals($w->toArray(), $found->toArray());
    }

    public function testCreatingNullAndBooleanValues()
    {
        $w = Wiz::create([
            'fiz' => null,
            'biz' => false,
            'triz' => true,
        ]);

        $this->assertNotNull($w->getKey());

        $found = Wiz::where('fiz', '=', null)->where('biz', '=', false)->where('triz', '=', true)->first();

        $this->assertNull($found->fiz);
        $this->assertFalse($found->biz);
        $this->assertTrue($found->triz);
    }

    public function testUpdatingNullAndBooleanValues()
    {
        $w = Wiz::create([
            'fiz' => 'foo',
            'biz' => 'boo',
            'triz' => 'troo',
        ]);

        $this->assertNotNull($w->getKey());

        $updated = Wiz::where('fiz', 'foo')->where('biz', 'boo')->where('triz', 'troo')->update([
            'fiz' => null,
            'biz' => false,
            'triz' => true,
        ]);

        $this->assertGreaterThan(0, $updated);
    }

    public function testSavningDateTimeAndCarbonInstances()
    {
        $now = Carbon::now();
        $dt = new DateTime();
        $w = Wiz::create(['fiz' => $now, 'biz' => $dt]);

        $format = Wiz::getDateFormat();

        $fetched = Wiz::first();
        $this->assertEquals($now->format(Wiz::getDateFormat()), $fetched->fiz);
        $this->assertEquals($now->format(Wiz::getDateFormat()), $fetched->biz);

        $tomorrow = Carbon::now()->addDay();
        $after = Carbon::now()->addDays(2);

        $fetched->fiz = $tomorrow;
        $fetched->biz = $after;
        $fetched->save();

        $updated = Wiz::first();
        $this->assertEquals($tomorrow->format(Wiz::getDateFormat()), $updated->fiz);
        $this->assertEquals($after->format(Wiz::getDateFormat()), $updated->biz);
    }
}
