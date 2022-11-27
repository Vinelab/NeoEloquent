<?php

namespace Vinelab\NeoEloquent\Tests\Functional\Relations\BelongsToMany;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Vinelab\NeoEloquent\Tests\TestCase;

use function func_get_args;
use function func_num_args;

class User extends Model
{
    protected $table = 'Individual';

    protected $fillable = ['uuid', 'name'];

    protected $primaryKey = 'uuid';

    protected $keyType = 'string';

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}

class Role extends Model
{
    protected $table = 'Role';

    protected $fillable = ['title'];

    protected $primaryKey = 'title';

    protected $keyType = 'string';

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}

class BelongsToManyRelationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        (new Role())->getConnection()->getPdo()->run('MATCH (x) DETACH DELETE x');
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
        $user   = User::create(['uuid' => '64753', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin  = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $user->roles()->attach([$master->getKey(), $admin->getKey(), $editor->getKey()]);

        $this->assertCount(3, $user->roles);
        $this->assertEquals(['Master', 'Admin', 'Editor'], $user->roles->pluck('title')->toArray());
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

        $relations = $user->roles()->attach([$master->getKey(), $admin->getKey(), $editor->getKey()]);

        $this->assertCount(3, $relations->all());

        // make sure they were successfully saved
        $relations->each(function ($relation) {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $relation);
            $this->assertTrue($relation->exists());
            $this->assertGreaterThanOrEqual(0, $relation->getKey());
        });

        // Try retrieving them before detaching
        $edges = $user->roles()->edges();
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Collection', $edges);
        $this->assertCount(3, $edges->toArray());

        $edges->each(function ($edge) { $edge->delete(); });
    }

    public function testSyncingModelIds()
    {
        $user = User::create(['uuid' => '25467', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $relation = $user->roles()->attach($master->getKey());

        $user->roles()->sync([$admin->getKey(), $editor->getKey()]);

        $edges = $user->roles()->edges();

        $edgesIds = array_map(function ($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        $this->assertTrue(in_array($admin->getKey(), $edgesIds));
        $this->assertTrue(in_array($editor->getKey(), $edgesIds));
        $this->assertFalse(in_array($master->getKey(), $edgesIds));

        foreach ($edges as $edge) {
            $edge->delete();
        }
    }

    public function testSyncingUpdatesModels()
    {
        $user = User::create(['uuid' => '14285', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $relation = $user->roles()->attach($master->getKey());

        $user->roles()->sync([$master->getKey(), $admin->getKey(), $editor->getKey()]);

        $edges = $user->roles()->edges();

        $edgesIds = array_map(function ($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        $this->assertTrue(in_array($admin->getKey(), $edgesIds));
        $this->assertTrue(in_array($editor->getKey(), $edgesIds));
        $this->assertTrue(in_array($master->getKey(), $edgesIds));

        foreach ($edges as $edge) {
            $edge->delete();
        }
    }

    public function testSyncingWithAttributes()
    {
        $user = User::create(['uuid' => '83532', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $relation = $user->roles()->attach($master->getKey());

        $user->roles()->sync([
            $master->getKey() => ['type' => 'Master'],
            $admin->getKey() => ['type' => 'Admin'],
            $editor->getKey() => ['type' => 'Editor'],
        ]);

        $edges = $user->roles()->edges();

        $edgesIds = array_map(function ($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        // count the times that $master->getKey() exists, it it were more than 1 then the relationship hasn't been updated,
        // instead it was duplicated
        $count = array_count_values((array) $master->getKey());

        $this->assertEquals(1, $count[$master->getKey()]);
        $this->assertTrue(in_array($admin->getKey(), $edgesIds));
        $this->assertTrue(in_array($editor->getKey(), $edgesIds));
        $this->assertTrue(in_array($master->getKey(), $edgesIds));

        $expectedEdgesTypes = array('Editor', 'Admin', 'Master');

        foreach ($edges as $key => $edge) {
            $attributes = $edge->toArray();
            $this->assertArrayHasKey('type', $attributes);
            $this->assertTrue(in_array($edge->type, $expectedEdgesTypes));
            $index = array_search($edge->type, $expectedEdgesTypes);
            unset($expectedEdgesTypes[$index]);
            $edge->delete();
        }
    }

    public function testDynamicLoadingBelongsToManyRelatedModels()
    {
        $user = User::create(['uuid' => '67887', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);

        $user->roles()->attach([$master, $admin]);

        foreach ($user->roles as $role) {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Relations\BelongsToMany\Role', $role);
            $this->assertTrue($role->exists);
            $this->assertGreaterThan(0, $role->getKey());
        }

        $user->roles()->edges()->each(function ($edge) { $edge->delete(); });
    }

    public function testEagerLoadingBelongsToMany()
    {
        $user = User::create(['uuid' => '44352', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $edges = $user->roles()->attach([$master, $admin, $editor]);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Collection', $edges);

        $creep = User::with('roles')->find($user->getKey());
        $relations = $creep->getRelations();

        $this->assertArrayHasKey('roles', $relations);
        $this->assertCount(3, $relations['roles']);

        $edges->each(function ($relation) { $relation->delete(); });
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

        $edges = $user->roles()->attach([$master, $admin, $editor]);

        $fetched = User::find($user->getKey());
        $this->assertEquals(3, count($user->roles), 'relations created successfully');

        $deleted = $fetched->roles()->delete();
        $this->assertTrue((bool) $deleted);

        $again = User::find($user->getKey());
        $this->assertEquals(0, count($again->roles));

        // roles should've been deleted too.
        $masterDeleted = Role::where('title', 'Master')->first();
        $this->assertNull($masterDeleted);

        $adminDeleted = Role::where('title', 'Admin')->first();
        $this->assertNull($adminDeleted);

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

        $edges = $user->roles()->attach([$master, $admin, $editor]);

        $fetched = User::find($user->getKey());
        $this->assertEquals(3, count($user->roles), 'relations created successfully');

        $deleted = $fetched->roles()->delete(true);
        $this->assertTrue((bool) $deleted);

        $again = User::find($user->getKey());
        $this->assertEquals(0, count($again->roles));

        // roles should've been deleted too.
        $masterDeleted = Role::find($master->getKey());
        $this->assertEquals($master->toArray(), $masterDeleted->toArray());

        $adminDeleted = Role::find($admin->getKey());
        $this->assertEquals($admin->toArray(), $adminDeleted->toArray());

        $editorDeleted = Role::find($editor->getKey());
        $this->assertEquals($editor->toArray(), $editorDeleted->toArray());
    }

    /**
     * Regression for issue #120.
     *
     * @see https://github.com/Vinelab/NeoEloquent/issues/120
     */
    public function testDeletingModelBelongsToManyWithWhereHasRelation()
    {
        $user = User::create(['uuid' => '54556', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $edges = $user->roles()->attach([$master, $admin, $editor]);

        $fetched = User::find($user->getKey());
        $this->assertEquals(3, count($user->roles), 'relations created successfully');

        $deleted = $fetched->whereHas('roles', function ($q) {
            $q->where('title', 'Master');
        })->delete();

        $this->assertTrue((bool) $deleted);

        $again = User::find($user->getKey());
        $this->assertNull($again);

        // roles should've been deleted too.
        $masterDeleted = Role::find($master->getKey());
        $this->assertEquals($master->toArray(), $masterDeleted->toArray());

        $adminDeleted = Role::find($admin->getKey());
        $this->assertEquals($admin->toArray(), $adminDeleted->toArray());

        $editorDeleted = Role::find($editor->getKey());
        $this->assertEquals($editor->toArray(), $editorDeleted->toArray());
    }
}
