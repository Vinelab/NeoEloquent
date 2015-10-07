<?php namespace Vinelab\NeoEloquent\Tests\Functional\Relations\HasOne;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;

class User extends Model {

    protected $label = 'Individual';
    protected $fillable = ['name', 'email'];

    public function profile()
    {
        return $this->hasOne('Vinelab\NeoEloquent\Tests\Functional\Relations\HasOne\Profile', 'PROFILE');
    }
}

class Profile extends Model {

    protected $label = 'Profile';

    protected $fillable = ['guid', 'service'];
}

class HasOneRelationTest extends TestCase {

    public function tearDown()
    {
        M::close();

        $users = User::all();
        $users->each(function($u) { $u->delete(); });

        $accs = Profile::all();
        $accs->each(function($a) { $a->delete(); });

        parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));

        User::setConnectionResolver($resolver);
        Profile::setConnectionResolver($resolver);
    }

    public function testDynamicLoadingHasOne()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = Profile::create(['guid' => uniqid(), 'service' => 'twitter']);

        $relation = $user->profile()->save($profile);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $relation);
        $this->assertEquals($profile->toArray(), $user->profile->toArray());
        $this->assertTrue($relation->delete());
    }

    public function testDynamicLoadingHasOneFromFoundRecord()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = Profile::create(['guid' => uniqid(), 'service' => 'twitter']);

        $relation = $user->profile()->save($profile);


        $found = User::find($user->id);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $relation);
        $this->assertEquals($profile->toArray(), $found->profile->toArray());
        $this->assertTrue($relation->delete());
    }

    public function testEagerLoadingHasOne()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = Profile::create(['guid' => uniqid(), 'service' => 'twitter']);

        $relation = $user->profile()->save($profile);

        $found = User::with('profile')->find($user->id);
        $relations = $found->getRelations();

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $relation);
        $this->assertArrayHasKey('profile', $relations);
        $this->assertEquals($profile->toArray(), $relations['profile']->toArray());
        $this->assertTrue($relation->delete());
    }

    public function testSavingRelatedHasOneModel()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = Profile::create(['guid' => uniqid(), 'service' => 'twitter']);

        $relation = $user->profile()->save($profile);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $relation);

        $this->assertInstanceOf('Carbon\Carbon', $relation->created_at, 'make sure we set the created_at timestamp');
        $this->assertInstanceOf('Carbon\Carbon', $relation->updated_at, 'make sure we set the updated_at timestamp');
        $this->assertEquals($user->profile->toArray(), $profile->toArray());

        // Let's retrieve it to make sure that NeoEloquent is not lying about it.
        $saved = User::find($user->id);
        $this->assertEquals($profile->toArray(), $saved->profile->toArray());

     // delete the relation and make sure it was deleted
        // so that we can delete the nodes when cleaning up.
        $this->assertTrue($relation->delete());
    }

    public function testRetrievingRelationWithAttributesSpecifyingEdgeModel()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = Profile::create(['guid' => uniqid(), 'service' => 'twitter']);

        $relation = $user->profile()->save($profile);
        $relation->active = true;
        $this->assertTrue($relation->save());

        $retrieved = $user->profile()->edge($profile);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $retrieved);
        $this->assertTrue($retrieved->active);
        $this->assertTrue($retrieved->delete());
    }

    public function testSavingMultipleRelationsKeepsOnlyTheLastOne()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = Profile::create(['guid' => uniqid(), 'service' => 'twitter']);

        $relation = $user->profile()->save($profile);
        $relation->use = 'casual';
        $this->assertTrue($relation->save());

        $cv = Profile::create(['guid' => uniqid(), 'service' => 'linkedin']);
        $linkedin = $user->profile()->save($cv);
        $linkedin->use = 'official';
        $this->assertTrue($linkedin->save());

        $withPr = $user->profile()->edge($profile);
        $this->assertNull($withPr);

        $withCv = $user->profile()->edge($cv);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $withCv);
        $this->assertEquals($withCv->use, 'official');
        $this->assertTrue($withCv->delete());
    }

    public function testFindingEdgeWithNoSpecifiedEdgeModel()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = Profile::create(['guid' => uniqid(), 'service' => 'twitter']);

        $relation = $user->profile()->save($profile);
        $relation->active = true;
        $this->assertTrue($relation->save());

        $retrieved = $user->profile()->edge();

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $retrieved);
        $this->assertEquals($relation->id, $retrieved->id);
        $this->assertEquals($relation->toArray(), $retrieved->toArray());
        $this->assertTrue($relation->delete());
    }

}
