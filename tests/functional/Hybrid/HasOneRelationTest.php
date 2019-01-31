<?php namespace Vinelab\NeoEloquent\Tests\Functional\Relations\Hybrid;

use Illuminate\Database\Schema\Blueprint;
use Mockery as M;
use Vinelab\NeoEloquent\Eloquent\Relations\Hybrid\HybridRelations;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model as NeoEloquent;
use Illuminate\Database\Eloquent\Model as Eloquent;

class User extends Eloquent
{
    use HybridRelations;

    protected $connection = "sqlite";
    protected $fillable = ['name', 'email'];

    public function profile()
    {
        return $this->hasOneHybrid(Profile::class, 'user_id');
    }
}

class Profile extends NeoEloquent
{
    use HybridRelations;

    protected $label = 'Profile';
    protected $connection = "neo4j";
    protected $fillable = ['guid', 'service', 'user_id'];

    public function user()
    {
        return $this->belongsToHybrid(User::class, 'user_id');
    }
}

class HasOneHybridRelationTest extends TestCase
{
    protected $db;

    protected $schema;

    public function tearDown()
    {
        M::close();
        $this->schema->dropIfExists('users');
        Profile::where("id", ">", -1)->delete();

        parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();
        $this->prepareDatabase();

        User::setConnectionResolver($this->resolver);
        Profile::setConnectionResolver($this->resolver);

        $this->schema->create('users', function (Blueprint $t) {
            $t->increments('id');
            $t->string('name');
            $t->string('email');
            $t->timestamps();
        });
    }

    public function testDynamicLoadingHasOne()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = Profile::create(['guid' => uniqid(), 'service' => 'twitter', 'user_id' => $user->id]);

        $this->assertNotNull($user->profile);
        $this->assertEquals($profile->toArray(), $user->profile->toArray());
    }

    public function testDynamicLoadingHasOneFromFoundRecord()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = Profile::create(['guid' => uniqid(), 'service' => 'twitter', 'user_id' => $user->id]);

        $found = User::find($user->id);

        $this->assertEquals($profile->toArray(), $found->profile->toArray());
    }

    public function testEagerLoadingHasOne()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = Profile::create(['guid' => uniqid(), 'service' => 'twitter', 'user_id' => $user->id]);

        $found = User::with('profile')->find($user->id);
        $relations = $found->getRelations();

        $this->assertArrayHasKey('profile', $relations);
        $this->assertEquals($profile->toArray(), $relations['profile']->toArray());
    }

    public function testCreateRelatedHasOneModel()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);

        $profile = $user->profile()->create(['guid' => uniqid(), 'service' => 'twitter']);

        $this->assertEquals($user->profile->toArray(), $profile->toArray());
        $saved = User::find($user->id);
        $this->assertEquals($profile->toArray(), $saved->profile->toArray());
    }


    public function testUpdateRelatedHasOneModel()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        $profile = $user->profile()->create(['guid' => uniqid(), 'service' => 'twitter', 'user_id' => $user->id]);

        $user->profile()->update(["service" => "fb"]);

        $this->assertNotEquals($user->profile->toArray(), $profile->toArray());
        $this->assertEquals($user->profile->toArray(), $profile->fresh()->toArray());
        $saved = User::find($user->id);
        $this->assertEquals($profile->fresh()->toArray(), $saved->profile->toArray());
    }

    public function testUpdatingModelWithRelatedModel()
    {
        $user = User::create(['name' => 'Tests', 'email' => 'B']);
        Profile::create(['guid' => uniqid(), 'service' => 'twitter', 'user_id' => $user->id]);

        $user->name = "test_name";
        $user->profile->service = "facebook";
        $user->push();

        $user = $user->fresh();

        $this->assertEquals("test_name", $user->name);
        $this->assertEquals("facebook", $user->profile->service);
    }
}
