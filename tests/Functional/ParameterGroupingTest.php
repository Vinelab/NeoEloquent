<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinelab\NeoEloquent\Tests\Fixtures\FacebookAccount;
use Vinelab\NeoEloquent\Tests\Fixtures\Profile;
use Vinelab\NeoEloquent\Tests\Fixtures\User;
use Vinelab\NeoEloquent\Tests\TestCase;

class ParameterGroupingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->affectingStatement('MATCH (x) DETACH DELETE x');
    }

    public function testNestedWhereClause()
    {
        $searchedUser = User::create(['name' => 'John Doe']);
        $searchedUser->profile()->save(
            Profile::create([
                'guid' => 'abcd',
                'service' => 'Music',
            ]));

        $anotherUser = User::create(['name' => 'John Smith']);
        $anotherUser->profile()->save(
            Profile::create([
                'guid' => 'abc',
                'service' => 'Music',
            ]));

        $users = User::whereHas('profile', function ($query) {
            $query->where('guid', 'abc')->where(function ($query) {
                $query->orWhere('service', 'Music')->orWhere('service', 'Video');
            });
        })->get();

        $this->assertCount(1, $users);
        $this->assertEquals('John Smith', $users->first()->name);
    }
}
