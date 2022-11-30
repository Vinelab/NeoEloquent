<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinelab\NeoEloquent\Tests\Fixtures\Location;
use Vinelab\NeoEloquent\Tests\TestCase;

class BelongsToRelationTest extends TestCase
{
    use RefreshDatabase;

    public function testDynamicLoadingBelongsTo(): void
    {
        $location = Location::create([
            'lat' => 89765,
            'long' => -876521234,
            'country' => 'The Netherlands',
            'city' => 'Amsterdam'
        ]);
        $user = \Vinelab\NeoEloquent\Tests\Fixtures\User::create([
            'name' => 'Daughter',
            'alias' => 'daughter'
        ]);

        $user->location()->associate($location);
        $user->save();

        $fetched = \Vinelab\NeoEloquent\Tests\Fixtures\User::first();
        $this->assertEquals($location->toArray(), $fetched->location->toArray());

        $fetched->location()->disassociate();
        $fetched->save();

        $fetched = \Vinelab\NeoEloquent\Tests\Fixtures\User::first();

        $this->assertNull($fetched->location);
    }

    public function testDynamicLoadingBelongsToFromFoundRecord(): void
    {
        $location = Location::create(['lat' => 89765, 'long' => -876521234, 'country' => 'The Netherlands', 'city' => 'Amsterdam']);
        /** @var \Vinelab\NeoEloquent\Tests\Functional\Fixtures\User $user */
        $user = \Vinelab\NeoEloquent\Tests\Fixtures\User::create(['name' => 'Daughter', 'alias' => 'daughter']);
        $user->location()->associate($location);
        $user->save();

        $found = \Vinelab\NeoEloquent\Tests\Fixtures\User::query()->find($user->getKey());

        $this->assertEquals($location->toArray(), $found->location->toArray());
    }

    public function testEagerLoadingBelongsTo(): void
    {
        /** @var \Vinelab\NeoEloquent\Tests\Fixtures\Location $location */
        $location = Location::query()->create(['lat' => 89765, 'long' => -876521234, 'country' => 'The Netherlands', 'city' => 'Amsterdam']);
        /** @var \Vinelab\NeoEloquent\Tests\Functional\Fixtures\User $user */
        $user = \Vinelab\NeoEloquent\Tests\Fixtures\User::query()->create(['name' => 'Daughter', 'alias' => 'daughter']);
        $user->location()->associate($location);
        $user->save();

        $relations = \Vinelab\NeoEloquent\Tests\Fixtures\User::with('location')->find($user->getKey())->getRelations();

        $this->assertArrayHasKey('location', $relations);
        $this->assertEquals($location->toArray(), $relations['location']->toArray());
    }
}
