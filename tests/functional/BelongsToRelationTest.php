<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace Vinelab\NeoEloquent\Tests\Functional\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;
use Carbon\Carbon;

class User extends Model
{
    protected $label = 'Individual';
    protected $fillable = ['name', 'email'];
}

class Location extends Model
{
    protected $label = 'Location';
    protected $fillable = ['lat', 'long'];

    public function user(): BelongsTo
    {
        return $this->belongsToRelation(User::class, 'LOCATED_AT');
    }
}

class BelongsToRelationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $users = User::all();
        $users->each(function ($u) { $u->delete(); });

        $locs = Location::all();
        $locs->each(function ($l) { $l->delete(); });
    }

    public function testDynamicLoadingBelongsTo(): void
    {
        $location = Location::query()->create(['lat' => 89765, 'long' => -876521234, 'country' => 'The Netherlands', 'city' => 'Amsterdam']);
        $user = User::query()->create(['name' => 'Daughter', 'alias' => 'daughter']);
        $relation = $location->user()->associate($user);
        $location->save();

        $fetched = Location::query()->first();
        $this->assertEquals($user->toArray(), $fetched->user->toArray());
        $relation->delete();
    }

    public function testDynamicLoadingBelongsToFromFoundRecord(): void
    {
        $location = Location::query()->create(['lat' => 89765, 'long' => -876521234, 'country' => 'The Netherlands', 'city' => 'Amsterdam']);
        $user = User::query()->create(['name' => 'Daughter', 'alias' => 'daughter']);
        $relation = $location->user()->associate($user);

        $found = Location::find($location->id);

        $this->assertEquals($user->toArray(), $found->user->toArray());
        $this->assertTrue($relation->delete());
    }

    public function testEagerLoadingBelongsTo(): void
    {
        $location = Location::query()->create(['lat' => 89765, 'long' => -876521234, 'country' => 'The Netherlands', 'city' => 'Amsterdam']);
        $user = User::query()->create(['name' => 'Daughter', 'alias' => 'daughter']);
        $relation = $location->user()->associate($user);

        $found = Location::with('user')->find($location->id);
        $relations = $found->getRelations();

        $this->assertArrayHasKey('user', $relations);
        $this->assertEquals($user->toArray(), $relations['user']->toArray());
        $this->assertTrue($relation->delete());
    }

    public function testAssociatingBelongingModel(): void
    {
        $location = Location::query()->create(['lat' => 89765, 'long' => -876521234, 'country' => 'The Netherlands', 'city' => 'Amsterdam']);
        $user = User::query()->create(['name' => 'Daughter', 'alias' => 'daughter']);
        $relation = $location->user()->associate($user);

        $this->assertInstanceOf(Carbon::class, $relation->created_at, 'make sure we set the created_at timestamp');
        $this->assertInstanceOf(Carbon::class, $relation->updated_at, 'make sure we set the updated_at timestamp');
        $this->assertArrayHasKey('user', $location->getRelations(), 'make sure the user has been set as relation in the model');
        $this->assertArrayHasKey('user', $location->toArray(), 'make sure it is also returned when dealing with the model');
        $this->assertEquals($location->user->toArray(), $user->toArray());

        // Let's retrieve it to make sure that NeoEloquent is not lying about it.
        $saved = Location::find($location->id);
        $this->assertEquals($user->toArray(), $saved->user->toArray());

        // delete the relation and make sure it was deleted
        // so that we can delete the nodes when cleaning up.
        $this->assertTrue($relation->delete());
    }

    public function testRetrievingAssociationFromParentModel(): void
    {
        $location = Location::query()->create(['lat' => 52.3735291, 'long' => 4.886257, 'country' => 'The Netherlands', 'city' => 'Amsterdam']);
        $user = User::query()->create(['name' => 'Daughter', 'alias' => 'daughter']);

        $relation = $location->user()->associate($user);
        $relation->since = 1966;
        $this->assertTrue($relation->save());

        $retrieved = $location->user()->edge($location->user);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn', $retrieved);
        $this->assertEquals($retrieved->since, 1966);

        $this->assertTrue($retrieved->delete());
    }

    public function testSavingMultipleAssociationsKeepsOnlyTheLastOne(): void
    {
        $location = Location::query()->create(['lat' => 52.3735291, 'long' => 4.886257, 'country' => 'The Netherlands']);
        $van = User::query()->create(['name' => 'Van Gogh', 'alias' => 'vangogh']);

        $relation = $location->user()->associate($van);
        $relation->since = 1890;
        $this->assertTrue($relation->save());

        $jan = User::query()->create(['name' => 'Jan Steen', 'alias' => 'jansteen']);
        $cheating = $location->user()->associate($jan);

        $withVan = $location->user()->edge($van);
        $this->assertNull($withVan);

        $withJan = $location->user()->edge($jan);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn', $withJan);
        $this->assertTrue($withJan->delete());
    }

    public function testFindingEdgeWithNoSpecifiedModel(): void
    {
        $location = Location::query()->create(['lat' => 52.3735291, 'long' => 4.886257, 'country' => 'The Netherlands', 'city' => 'Amsterdam']);
        $user = User::query()->create(['name' => 'Daughter', 'alias' => 'daughter']);

        $relation = $location->user()->associate($user);
        $relation->since = 1966;
        $this->assertTrue($relation->save());

        $retrieved = $location->user()->edge();

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn', $retrieved);
        $this->assertEquals($relation->id, $retrieved->id);
        $this->assertEquals($relation->toArray(), $retrieved->toArray());
        $this->assertTrue($relation->delete());
    }
}
