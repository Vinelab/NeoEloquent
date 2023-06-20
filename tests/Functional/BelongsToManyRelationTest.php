<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinelab\NeoEloquent\Tests\Fixtures\Role;
use Vinelab\NeoEloquent\Tests\Fixtures\User;
use Vinelab\NeoEloquent\Tests\TestCase;

class BelongsToManyRelationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->affectingStatement('MATCH (x) DETACH DELETE x');
    }

    public function testSavingRelatedBelongsToMany(): void
    {
        $user = User::create(['uuid' => '11213', 'name' => 'Creepy Dude']);
        $role = new Role(['title' => 'Master']);

        $user->roles()->save($role);

        $this->assertCount(1, $user->roles);
        $this->assertCount(1, $role->users);
    }

    public function testAttachingModelId()
    {
        $user = User::create(['uuid' => '4622', 'name' => 'Creepy Dude']);
        $role = Role::create(['title' => 'Master']);
        $user->roles()->attach($role->getKey());

        $this->assertCount(1, $user->roles);
    }

    public function testAttachingManyModelIds()
    {
        $user = User::create(['uuid' => '64753', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $user->roles()->attach([$master->getKey(), $admin->getKey(), $editor->getKey()]);

        $this->assertCount(3, $user->roles);
        $this->assertEqualsCanonicalizing(['Master', 'Admin', 'Editor'], $user->roles->pluck('title')->toArray());
    }

    public function testAttachingModelInstance()
    {
        $user = User::create(['uuid' => '19583', 'name' => 'Creepy Dude']);
        $role = Role::create(['title' => 'Master']);

        $user->roles()->attach($role);

        $this->assertTrue($user->roles->first()->is($role));
        $this->assertTrue($role->users->first()->is($user));
    }

    public function testDetachingModelById()
    {
        $user = User::create(['uuid' => '943543', 'name' => 'Creepy Dude']);
        $role = Role::create(['title' => 'Master']);

        $user->roles()->attach($role->getKey());
        $user = User::find($user->getKey());

        $this->assertCount(1, $user->roles);

        $user->roles()->detach($role->getKey());
        $user = User::find($user->getKey());

        $this->assertCount(0, $user->roles);
    }

    public function testDetachingManyModelIds()
    {
        $user = User::create(['uuid' => '8363', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $user->roles()->attach([$master->getKey(), $admin->getKey(), $editor->getKey()]);
        $user = User::find($user->getKey());

        $this->assertCount(3, $user->roles);
        $user = User::find($user->getKey());

        $user->roles()->detach();
        $this->assertCount(0, $user->roles);
    }

    public function testSyncingModelIds()
    {
        $user = User::create(['uuid' => '25467', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $user->roles()->attach($master->getKey());

        $user->roles()->sync([$admin->getKey(), $editor->getKey()]);

        $edgesIds = $user->roles->pluck('title')->toArray();

        $this->assertTrue(in_array($admin->getKey(), $edgesIds));
        $this->assertTrue(in_array($editor->getKey(), $edgesIds));
        $this->assertFalse(in_array($master->getKey(), $edgesIds));
    }

    public function testSyncingUpdatesModels()
    {
        $user = User::create(['uuid' => '14285', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $user->roles()->attach($master->getKey());
        $user = User::find($user->getKey());
        $this->assertCount(1, $user->roles);

        $user->roles()->sync([$master->getKey(), $admin->getKey(), $editor->getKey()]);
        $user = User::find($user->getKey());

        $edges = $user->roles->pluck('title')->toArray();

        $this->assertCount(3, $edges);
        $this->assertTrue(in_array($admin->getKey(), $edges));
        $this->assertTrue(in_array($editor->getKey(), $edges));
        $this->assertTrue(in_array($master->getKey(), $edges));
    }

    public function testSyncingWithAttributes()
    {
        $user = User::create(['uuid' => '83532', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $user->roles()->attach($master->getKey());

        $user->roles()->sync([
            $master->getKey() => ['type' => 'Master'],
            $admin->getKey() => ['type' => 'Admin'],
            $editor->getKey() => ['type' => 'Editor'],
        ]);

        $edges = $user->roles()
            ->withPivot('type')
            ->orderBy('title')
            ->select(['title'])
            ->get()
            ->pluck('title', 'pivot.type')
            ->toArray();

        $this->assertEquals(['Admin' => 'Admin', 'Editor' => 'Editor', 'Master' => 'Master'], $edges);
    }

    public function testEagerLoadingBelongsToMany()
    {
        $user = User::create(['uuid' => '44352', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $user->roles()->attach([$master->getKey(), $admin->getKey(), $editor->getKey()]);

        $creep = User::with('roles')->find($user->getKey());
        $relations = $creep->getRelations();

        $this->assertArrayHasKey('roles', $relations);
        $this->assertCount(3, $relations['roles']);
    }

    /**
     * Regression for issue #120.
     *
     * @see https://github.com/Vinelab/NeoEloquent/issues/120
     */
    public function testDeletingBelongsToManyRelation()
    {
        $user = User::create(['uuid' => '34113', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $user->roles()->attach([$master->getKey(), $admin->getKey(), $editor->getKey()]);

        $fetched = User::find($user->getKey());
        $this->assertCount(3, $user->roles, 'relations created successfully');

        $deleted = $fetched->roles()->detach();
        $this->assertTrue((bool) $deleted);

        $again = User::find($user->getKey());
        $this->assertCount(0, $again->roles);

        $masterDeleted = Role::where('title', 'Master')->first();
        $this->assertNotNull($masterDeleted);

        $adminDeleted = Role::where('title', 'Admin')->first();
        $this->assertNotNull($adminDeleted);

        $editorDeleted = Role::where('title', 'Edmin')->first();
        $this->assertNull($editorDeleted);
    }

    /**
     * Regression for issue #120.
     *
     * @see https://github.com/Vinelab/NeoEloquent/issues/120
     */
    public function testDeletingBelongsToManyRelationKeepingEndModels()
    {
        $user = User::create(['uuid' => '84633', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $user->roles()->attach([$master->getKey(), $admin->getKey(), $editor->getKey()]);

        $fetched = User::find($user->getKey());
        $this->assertCount(3, $user->roles, 'relations created successfully');

        $deleted = $fetched->roles()->detach();
        $this->assertTrue((bool) $deleted);

        $again = User::find($user->getKey());
        $this->assertCount(0, $again->roles);

        // roles should've been deleted too.
        $masterDeleted = Role::find($master->getKey());
        $this->assertEquals($master->toArray(), $masterDeleted->toArray());

        $adminDeleted = Role::find($admin->getKey());
        $this->assertEquals($admin->toArray(), $adminDeleted->toArray());

        $editorDeleted = Role::find($editor->getKey());
        $this->assertEquals($editor->toArray(), $editorDeleted->toArray());
    }
}
