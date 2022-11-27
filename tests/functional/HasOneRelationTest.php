<?php

namespace Vinelab\NeoEloquent\Tests\Functional\Relations\HasOne;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;

class User extends Model
{
    protected $table = 'Individual';
    protected $fillable = ['name', 'email'];

    // Todo - add this to gotchas in documentation
    protected $primaryKey = 'email';
    protected $keyType = 'string';

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }
}

class Profile extends Model
{
    protected $table = 'Profile';
    protected $fillable = ['guid', 'service'];

    protected $primaryKey = 'guid';
    protected $keyType = 'string';
}

class HasOneRelationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        (new Profile())->getConnection()->getPdo()->run('MATCH (x) DETACH DELETE x');
    }

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

        $this->assertEquals($profile->toArray(), $relations['profile']->toArray());
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
