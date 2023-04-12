<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinelab\NeoEloquent\Tests\Fixtures\Profile;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Tests\Fixtures\User;

class HasOneRelationTest extends TestCase
{
    use RefreshDatabase;

    public function testDynamicLoadingHasOne()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = Profile::create(['guid' => uniqid(), 'service' => 'twitter']);

        $user->profile()->save($profile);

        $this->assertEquals($profile->toArray(), $user->profile->toArray());
    }

    public function testDynamicLoadingHasOneFromFoundRecord()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = Profile::create(['guid' => uniqid(), 'service' => 'twitter']);

        $user->profile()->save($profile);

        $found = User::find($user->getKey());

        $this->assertEquals($profile->toArray(), $found->profile->toArray());
    }

    public function testEagerLoadingHasOne()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = Profile::create(['guid' => uniqid(), 'service' => 'twitter']);

        $relation = $user->profile()->save($profile);

        $found = User::with('profile')->find($user->getKey());
        $relations = $found->getRelations();

        $this->assertInstanceOf(Profile::class, $relation);
        $this->assertArrayHasKey('profile', $relations);

        $this->assertEquals($profile->toArray(), $user->profile->toArray());
    }

    public function testSavingMultipleRelationsKeepsOnlyTheLastOne()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = new Profile(['guid' => uniqid(), 'service' => 'twitter']);

        $user->profile()->save($profile);
        $user->refresh();
        $cv = new Profile(['guid' => uniqid(), 'service' => 'linkedin']);
        $user->profile()->update([$user->profile()->getForeignKeyName() => null]);

        $user->profile()->save($cv);
        $user->refresh();
        $this->assertEquals('linkedin', $user->profile->service);
    }
}
