<?php

namespace Vinelab\NeoEloquent\Tests\Functional\Relations\BelongsToMany;

use Mockery as M;
use Vinelab\NeoEloquent\Exceptions\ModelNotFoundException;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;

class User extends Model
{
    protected $label = 'Individual';

    protected $fillable = ['uuid', 'name'];

    protected $primaryKey = 'uuid';

    public function roles()
    {
        return $this->hasMany('Vinelab\NeoEloquent\Tests\Functional\Relations\BelongsToMany\Role', 'HAS_ROLE');
    }
}

class Role extends Model
{
    protected $label = 'Role';

    protected $fillable = ['title'];

    public function users()
    {
        return $this->belongsToMany('Vinelab\NeoEloquent\Tests\Functional\Relations\BelongsToMany\User', 'HAS_ROLE');
    }
}

class BelongsToManyRelationTest extends TestCase
{
    public function tearDown(): void
    {
        M::close();

        $users = User::all();
        $users->each(function ($u) { $u->delete(); });

        $roles = Role::all();
        $roles->each(function ($r) { $r->delete(); });

        parent::tearDown();
    }

    public function setUp(): void
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));

        User::setConnectionResolver($resolver);
        Role::setConnectionResolver($resolver);
    }

    public function testSavingRelatedBelongsToMany()
    {
        $user = User::create(['uuid' => '11213', 'name' => 'Creepy Dude']);
        $role = new Role(['title' => 'Master']);
        $relation = $user->roles()->save($role);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $relation);
        $this->assertTrue($relation->exists());
        $this->assertGreaterThanOrEqual(0, $relation->id);

        $relation->delete();
    }

    public function testAttachingModelId()
    {
        $user = User::create(['uuid' => '4622', 'name' => 'Creepy Dude']);
        $role = Role::create(['title' => 'Master']);
        $relation = $user->roles()->attach($role->id);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $relation);
        $this->assertTrue($relation->exists());
        $this->assertGreaterThan(0, $relation->id);

        $relation->delete();
    }

    public function testAttachingManyModelIds()
    {
        $user = User::create(['uuid' => '64753', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $relations = $user->roles()->attach([$master->id, $admin->id, $editor->id]);

        $this->assertCount(3, $relations->all());

        $relations->each(function ($relation) {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $relation);
            $this->assertTrue($relation->exists());
            $this->assertGreaterThanOrEqual(0, $relation->id);

            $relation->delete();
        });
    }

    public function testAttachingModelInstance()
    {
        $user = User::create(['uuid' => '19583', 'name' => 'Creepy Dude']);
        $role = Role::create(['title' => 'Master']);

        $relation = $user->roles()->attach($role);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $relation);
        $this->assertTrue($relation->exists());
        $this->assertGreaterThanOrEqual(0, $relation->id);

        $retrieved = $user->roles()->edge($role);
        $this->assertEquals($retrieved->toArray(), $relation->toArray());

        $user->roles()->detach($role->id);
        $this->assertNull($user->roles()->edge($role));
    }

    public function testAttachingManyModelInstances()
    {
        $user = User::create(['uuid' => '5346', 'name' => 'Creepy Dude']);
        $master = new Role(['title' => 'Master']);
        $admin = new Role(['title' => 'Admin']);
        $editor = new Role(['title' => 'Editor']);

        $relations = $user->roles()->attach([$master, $admin, $editor]);

        $this->assertCount(3, $relations->all());

        $relations->each(function ($relation) {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $relation);
            $this->assertTrue($relation->exists());
            $this->assertGreaterThan(0, $relation->id);

            $relation->delete();
        });
    }

    public function testAttachingNonExistingModelId()
    {
        $user = User::create(['uuid' => '3242', 'name' => 'Creepy Dude']);
        $this->expectException(ModelNotFoundException::class);
        $user->roles()->attach(10);
    }

    public function testFindingBothEdges()
    {
        $user = User::create(['uuid' => '34525', 'name' => 'Creepy Dude']);
        $role = Role::create(['title' => 'Master']);
        $relation = $user->roles()->attach($role->id);

        $edgeOut = $user->roles()->edge($role);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $edgeOut);
        $this->assertTrue($edgeOut->exists());
        $this->assertGreaterThan(0, $edgeOut->id);

        $edgeIn = $role->users()->edge($user);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn', $edgeIn);
        $this->assertTrue($edgeIn->exists());
        $this->assertGreaterThan(0, $edgeIn->id);

        $relation->delete();
    }

    public function testDetachingModelById()
    {
        $user = User::create(['uuid' => '943543', 'name' => 'Creepy Dude']);
        $role = Role::create(['title' => 'Master']);

        $relation = $user->roles()->attach($role->id);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $relation);
        $this->assertTrue($relation->exists());
        $this->assertGreaterThan(0, $relation->id);

        $retrieved = $user->roles()->edge($role);
        $this->assertEquals($retrieved->toArray(), $relation->toArray());

        $user->roles()->detach($role->id);
        $this->assertNull($user->roles()->edge($role));
    }

    public function testDetachingManyModelIds()
    {
        $user = User::create(['uuid' => '8363', 'name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $relations = $user->roles()->attach([$master->id, $admin->id, $editor->id]);

        $this->assertCount(3, $relations->all());

        // make sure they were successfully saved
        $relations->each(function ($relation) {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $relation);
            $this->assertTrue($relation->exists());
            $this->assertGreaterThanOrEqual(0, $relation->id);
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

        $relation = $user->roles()->attach($master->id);

        $user->roles()->sync([$master->id, $admin->id, $editor->id]);

        $edges = $user->roles()->edges();

        $edgesIds = array_map(function ($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        $this->assertTrue(in_array($admin->id, $edgesIds));
        $this->assertTrue(in_array($editor->id, $edgesIds));
        $this->assertTrue(in_array($master->id, $edgesIds));

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

        $relation = $user->roles()->attach($master->id);

        $user->roles()->sync([
            $master->id => ['type' => 'Master'],
            $admin->id => ['type' => 'Admin'],
            $editor->id => ['type' => 'Editor'],
        ]);

        $edges = $user->roles()->edges();

        $edgesIds = array_map(function ($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        // count the times that $master->id exists, it it were more than 1 then the relationship hasn't been updated,
        // instead it was duplicated
        $count = array_count_values((array) $master->id);

        $this->assertEquals(1, $count[$master->id]);
        $this->assertTrue(in_array($admin->id, $edgesIds));
        $this->assertTrue(in_array($editor->id, $edgesIds));
        $this->assertTrue(in_array($master->id, $edgesIds));

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
            $this->assertGreaterThan(0, $role->id);
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
