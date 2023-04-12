<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinelab\NeoEloquent\Tests\Fixtures\FacebookAccount;
use Vinelab\NeoEloquent\Tests\Fixtures\User;
use Vinelab\NeoEloquent\Tests\TestCase;

class ParameterGroupingTest extends TestCase
{
    use RefreshDatabase;

    public function testNestedWhereClause()
    {
        $searchedUser = User::create(['name' => 'John Doe']);
        $searchedUser->facebookAccount()->save(
            FacebookAccount::create([
                'gender' => 'male',
                'age' => 20,
                'interest' => 'Dancing',
            ]));

        $anotherUser = User::create(['name' => 'John Smith']);
        $anotherUser->facebookAccount()->save(
            FacebookAccount::create([
                'gender' => 'male',
                'age' => 30,
                'interest' => 'Music',
            ]));

        $users = User::whereHas('facebookAccount', function ($query) {
            $query->where('gender', 'male')->where(function ($query) {
                $query->orWhere('age', '<', 24)->orWhere('interest', 'Entertainment');
            });
        })->get();

        $this->assertCount(1, $users);
        $this->assertEquals($searchedUser->name, $users->shift()->name);
    }
}
