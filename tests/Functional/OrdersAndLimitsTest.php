<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinelab\NeoEloquent\Tests\Fixtures\Role;
use Vinelab\NeoEloquent\Tests\TestCase;

class OrdersAndLimitsTest extends TestCase
{
    use RefreshDatabase;

    public function testFetchingOrderedRecords()
    {
        $c1 = Role::create(['title' => 'a']);
        $c2 = Role::create(['title' => 'b']);
        $c3 = Role::create(['title' => 'c']);

        $clicks = Role::orderBy('title', 'desc')->get();

        $this->assertCount(3, $clicks);

        $this->assertEquals($c3->toArray(), $clicks[0]->toArray());
        $this->assertEquals($c2->toArray(), $clicks[1]->toArray());
        $this->assertEquals($c1->toArray(), $clicks[2]->toArray());

        $asc = Role::orderBy('title', 'asc')->get();

        $this->assertEquals($c1->toArray(), $asc[0]->toArray());
        $this->assertEquals($c2->toArray(), $asc[1]->toArray());
        $this->assertEquals($c3->toArray(), $asc[2]->toArray());
    }

    public function testFetchingLimitedOrderedRecords()
    {
        $c1 = Role::create(['title' => 'a']);
        $c2 = Role::create(['title' => 'b']);
        $c3 = Role::create(['title' => 'c']);

        $click = Role::orderBy('title', 'desc')->take(1)->get();
        $this->assertCount(1, $click);
        $this->assertEquals($c3->title, $click[0]->title);

        $another = Role::orderBy('title', 'asc')->take(2)->get();
        $this->assertCount(2, $another);
        $this->assertEquals($c1->title, $another[0]->title);
        $this->assertEquals($c2->title, $another[1]->title);
    }
}
