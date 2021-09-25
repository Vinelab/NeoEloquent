<?php

namespace Vinelab\NeoEloquent\Tests\Functional\ParameterGrouping;

use Mockery as M;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Tests\TestCase;

class User extends Model
{
    protected $label = 'User';
    protected $fillable = ['name'];

    public function facebookAccount()
    {
        return $this->hasOne('Vinelab\NeoEloquent\Tests\Functional\ParameterGrouping\FacebookAccount', 'HAS_FACEBOOK_ACCOUNT');
    }
}

class FacebookAccount extends Model
{
    protected $label = 'SocialAccount';
    protected $fillable = ['gender', 'age', 'interest'];
}

class ParameterGroupingTest extends TestCase
{
    public function tearDown(): void
    {
        M::close();

        parent::tearDown();
    }

    public function setUp(): void
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));

        User::setConnectionResolver($resolver);
        FacebookAccount::setConnectionResolver($resolver);
    }

    public function testNestedWhereClause()
    {
        $searchedUser = User::create(['name' => 'John Doe']);
        $searchedUser->facebookAccount()->save(FacebookAccount::create([
            'gender' => 'male',
            'age' => 20,
            'interest' => 'Dancing',
        ]));

        $anotherUser = User::create(['name' => 'John Smith']);
        $anotherUser->facebookAccount()->save(FacebookAccount::create([
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
