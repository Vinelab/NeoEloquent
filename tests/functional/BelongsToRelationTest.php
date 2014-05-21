<?php namespace Vinelab\NeoEloquent\Tests\Functional\Relations;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;

class User extends Model {

    protected $label = 'Individual';
    protected $fillable = ['name', 'email'];
}

class Location extends Model {

    protected $label = 'Location';
    protected $fillable = ['lat', 'long'];

    public function user()
    {
        return $this->belongsTo('Vinelab\NeoEloquent\Tests\Functional\Relations\User', 'LOCATED_AT');
    }
}

class BelongsToRelationTest extends TestCase {

     public function tearDown()
    {
        M::close();

        $users = User::all();
        $users->each(function($u) { $u->delete(); });

        $locs = Location::all();
        $locs->each(function($l) { $l->delete(); });

        parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));

        User::setConnectionResolver($resolver);
        Location::setConnectionResolver($resolver);
    }

    public function testAssociatingBelongingModel()
    {
        $location = Location::create(['lat' => 89765, 'long' => -876521234, 'country' => 'The Netherlands', 'city' => 'Amsterdam']);
        $user = User::create(['name' => 'Daughter', 'alias' => 'daughter']);
        $relation = $location->user()->associate($user);

        $saved = $relation->save();

        $this->assertTrue($saved);
        $this->assertArrayHasKey('user', $location->getRelations(), 'make sure the user has been set as relation in the model');
        $this->assertArrayHasKey('user', $location->toArray(), 'make sure it is also returned when dealing with the model');
        $this->assertEquals($location->user, $user);

        // Let's retrieve it to make sure that NeoElquent is not lying about it.
        $saved = Location::find($location->id);
        $this->assertEquals($user, $saved->user);

        // delete the relation and make sure it was deleted
        // so that we can delete the nodes when cleaning up.
        $this->assertTrue($relation->delete());
    }
}
