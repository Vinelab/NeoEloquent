<?php namespace Vinelab\NeoEloquent\Tests\Functional\Relations\BelongsToMany;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;

class User extends Model {

    protected $label = 'Individual';

    protected $fillable = ['name'];

    public function roles()
    {
        return $this->belongsToMany('Vinelab\NeoEloquent\Tests\Functional\Relations\BelongsToMany\Role', 'HAS_ROLE');
    }
}

class Role extends Model {

    protected $label = 'Role';

    protected $fillable = ['title'];

    public function users()
    {
        return $this->belongsToMany('Vinelab\NeoEloquent\Tests\Functional\Relations\BelongsToMany\User', 'HAS_ROLE');
    }
}

class BelongsToManyRelationTest extends TestCase {

    public function tearDown()
    {
        M::close();

        $users = User::all();
        $users->each(function($u) { $u->delete(); });

        $roles = Role::all();
        $roles->each(function($r) { $r->delete(); });

        parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));

        User::setConnectionResolver($resolver);
        Role::setConnectionResolver($resolver);
    }

    public function testSavingRelatedBelongsToMany()
    {
        $user = User::create(['name' => 'Creepy Dude']);
        $role = new Role(['title' => 'Master']);
        $relation = $user->roles()->save($role);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn', $relation);
        $this->assertTrue($relation->exists());
        $this->assertGreaterThanOrEqual(0, $relation->id);

        $relation->delete();
    }

    public function testAttachingModelId()
    {
        $user = User::create(['name' => 'Creepy Dude']);
        $role = Role::create(['title' => 'Master']);
        $relation = $user->roles()->attach($role->id);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn', $relation);
        $this->assertTrue($relation->exists());
        $this->assertGreaterThan(0, $relation->id);

        $relation->delete();
    }

    public function testAttachingManyModelIds()
    {
        $user   = User::create(['name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin  = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $relations = $user->roles()->attach([$master->id, $admin->id, $editor->id]);

        $this->assertCount(3, $relations->all());

        $relations->each(function($relation)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn', $relation);
            $this->assertTrue($relation->exists());
            $this->assertGreaterThan(0, $relation->id);

            $relation->delete();
        });
    }

    public function testAttachingModelInstance()
    {
        $user   = User::create(['name' => 'Creepy Dude']);
        $role = Role::create(['title' => 'Master']);

        $relation = $user->roles()->attach($role);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn', $relation);
        $this->assertTrue($relation->exists());
        $this->assertGreaterThan(0, $relation->id);

        $retrieved = $user->roles()->edge($role);
        $this->assertEquals($retrieved->toArray(), $relation->toArray());

        $user->roles()->detach($role->id);
        $this->assertNull($user->roles()->edge($role));
    }

    public function testAttachingManyModelInstances()
    {
        $user   = User::create(['name' => 'Creepy Dude']);
        $master = new Role(['title' => 'Master']);
        $admin  = new Role(['title' => 'Admin']);
        $editor = new Role(['title' => 'Editor']);

        $relations = $user->roles()->attach([$master, $admin, $editor]);

        $this->assertCount(3, $relations->all());

        $relations->each(function($relation)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn', $relation);
            $this->assertTrue($relation->exists());
            $this->assertGreaterThan(0, $relation->id);

            $relation->delete();
        });
    }

    /**
     * @expectedException Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testAttachingNonExistingModelId()
    {
        $user   = User::create(['name' => 'Creepy Dude']);
        $user->roles()->attach(10);
    }

    public function testFindingBothEdges()
    {
        $user     = User::create(['name' => 'Creepy Dude']);
        $role     = Role::create(['title' => 'Master']);
        $relation = $user->roles()->attach($role->id);

        $edgeIn = $user->roles()->edge($role);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn', $edgeIn);
        $this->assertTrue($edgeIn->exists());
        $this->assertGreaterThan(0, $edgeIn->id);

        $edgeIn = $role->users()->edge($user);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $edgeIn);
        $this->assertTrue($edgeIn->exists());
        $this->assertGreaterThan(0, $edgeIn->id);

        $relation->delete();
    }

    public function testDetachingModelById()
    {
        $user = User::create(['name' => 'Creepy Dude']);
        $role = Role::create(['title' => 'Master']);

        $relation = $user->roles()->attach($role->id);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn', $relation);
        $this->assertTrue($relation->exists());
        $this->assertGreaterThan(0, $relation->id);

        $retrieved = $user->roles()->edge($role);
        $this->assertEquals($retrieved->toArray(), $relation->toArray());

        $user->roles()->detach($role->id);
        $this->assertNull($user->roles()->edge($role));
    }

    public function testDetachingManyModelIds()
    {
        $user   = User::create(['name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin  = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $relations = $user->roles()->attach([$master->id, $admin->id, $editor->id]);

        $this->assertCount(3, $relations->all());

        // make sure they were successfully saved
        $relations->each(function($relation)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn', $relation);
            $this->assertTrue($relation->exists());
            $this->assertGreaterThan(0, $relation->id);
        });

        // Try retrieving them before detaching
        $edges = $user->roles()->edges();
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $edges);
        $this->assertCount(3, $edges->toArray());

        $edges->each(function($edge) { $edge->delete(); });
    }

    public function testSyncingModelIds()
    {
        $user   = User::create(['name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin  = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $relation = $user->roles()->attach($master->id);

        $user->roles()->sync([$admin->id, $editor->id]);

        $edges = $user->roles()->edges();

        $edgesIds = array_map(function($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        $this->assertTrue(in_array($admin->id, $edgesIds));
        $this->assertTrue(in_array($editor->id, $edgesIds));
        $this->assertFalse(in_array($master->id, $edgesIds));

        foreach ($edges as $edge)
        {
            $edge->delete();
        }
    }

    public function testSyncingUpdatesModels()
    {
        $user   = User::create(['name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin  = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $relation = $user->roles()->attach($master->id);

        $user->roles()->sync([$master->id, $admin->id, $editor->id]);

        $edges = $user->roles()->edges();

        $edgesIds = array_map(function($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        $this->assertTrue(in_array($admin->id, $edgesIds));
        $this->assertTrue(in_array($editor->id, $edgesIds));
        $this->assertTrue(in_array($master->id, $edgesIds));

        foreach ($edges as $edge)
        {
            $edge->delete();
        }
    }

    public function testSyncingWithAttributes()
    {
        $user   = User::create(['name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin  = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $relation = $user->roles()->attach($master->id);

        $user->roles()->sync([
            $master->id => ['type' => 'Master'],
            $admin->id  => ['type' => 'Admin'],
            $editor->id => ['type' => 'Editor']
        ]);

        $edges = $user->roles()->edges();

        $edgesIds = array_map(function($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        // count the times that $master->id exists, it it were more than 1 then the relationship hasn't been updated,
        // instead it was duplicated
        $count = array_count_values((array) $master->id);

        $this->assertEquals(1, $count[$master->id]);
        $this->assertTrue(in_array($admin->id, $edgesIds));
        $this->assertTrue(in_array($editor->id, $edgesIds));
        $this->assertTrue(in_array($master->id, $edgesIds));

        $expectedEdgesTypes = array('Editor', 'Admin', 'Master');

        foreach ($edges as $key => $edge)
        {
            $attributes = $edge->toArray();
            $this->assertArrayHasKey('type', $attributes);
            $this->assertEquals($expectedEdgesTypes[$key], $edge->type);
            $edge->delete();
        }
    }

    public function testDynamicLoadingBelongsToManyRelatedModels()
    {
        $user   = User::create(['name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin  = Role::create(['title' => 'Admin']);

        $user->roles()->attach([$master, $admin]);

        foreach ($user->roles as $role)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Relations\BelongsToMany\Role', $role);
            $this->assertTrue($role->exists);
            $this->assertGreaterThan(0, $role->id);
        }

        $user->roles()->edges()->each(function($edge){ $edge->delete(); });
    }

    public function testEagerLoadingBelongsToMany()
    {
        $user = User::create(['name' => 'Creepy Dude']);
        $master = Role::create(['title' => 'Master']);
        $admin  = Role::create(['title' => 'Admin']);
        $editor = Role::create(['title' => 'Editor']);

        $edges = $user->roles()->attach([$master, $admin, $editor]);
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $edges);

        $creep = User::with('roles')->find($user->id);
        $relations = $creep->getRelations();

        $this->assertArrayHasKey('roles', $relations);
        $this->assertCount(3, $relations['roles']);

        $edges->each(function($relation) { $relation->delete(); });
    }

}
