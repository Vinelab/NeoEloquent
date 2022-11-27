<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Database\Eloquent\Model;
use Vinelab\NeoEloquent\Tests\TestCase;

class OrdersAndLimitsTest extends TestCase
{
    public function testFetchingOrderedRecords()
    {
        $c1 = Click::create(['num' => 1]);
        $c2 = Click::create(['num' => 2]);
        $c3 = Click::create(['num' => 3]);

        $clicks = Click::orderBy('num', 'desc')->get();

        $this->assertCount(3, $clicks);

        $this->assertEquals($c3->toArray(), $clicks[0]->toArray());
        $this->assertEquals($c2->toArray(), $clicks[1]->toArray());
        $this->assertEquals($c1->toArray(), $clicks[2]->toArray());

        $asc = Click::orderBy('num', 'asc')->get();

        $this->assertEquals($c1->toArray(), $asc[0]->toArray());
        $this->assertEquals($c2->toArray(), $asc[1]->toArray());
        $this->assertEquals($c3->toArray(), $asc[2]->toArray());
    }

    public function testFetchingLimitedOrderedRecords()
    {
        $c1 = Click::create(['num' => 1]);
        $c2 = Click::create(['num' => 2]);
        $c3 = Click::create(['num' => 3]);

        $click = Click::orderBy('num', 'desc')->take(1)->get();
        $this->assertEquals(1, count($click));
        $this->assertEquals($c3->toArray(), $click[0]->toArray());

        $another = Click::orderBy('num', 'asc')->take(2)->get();
        $this->assertEquals(2, count($another));
        $this->assertEquals($c1->toArray(), $another[0]->toArray());
        $this->assertEquals($c2->toArray(), $another[1]->toArray());
    }
}

class Click extends Model
{
    protected $table = 'Click';

    protected $fillable = ['num'];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $primaryKey = 'num';
}
