<?php /** @noinspection PhpPossiblePolymorphicInvocationInspection */

namespace Vinelab\NeoEloquent\Tests\Functional\Relations\BelongsTo;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Vinelab\NeoEloquent\Tests\TestCase;
use Carbon\Carbon;

class User extends Model
{
    protected $table = 'Individual';
    protected $fillable = ['name', 'alias'];
    protected $primaryKey = 'name';
    public $incrementing = false;

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}

class Location extends Model
{
    protected $table = 'Location';
    protected $primaryKey = 'lat';
    protected $fillable = ['lat', 'long', 'country', 'city'];
}

class BelongsToRelationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        (new Location())->getConnection()->getPdo()->run('MATCH (x) DETACH DELETE x');
    }

    public function testDynamicLoadingBelongsTo(): void
    {
        $location = Location::create([
            'lat' => 89765,
            'long' => -876521234,
            'country' => 'The Netherlands',
            'city' => 'Amsterdam'
        ]);
        $user = User::create([
            'name' => 'Daughter',
            'alias' => 'daughter'
        ]);

        $user->location()->associate($location);
        $user->save();

        $fetched = User::first();
        $this->assertEquals($location->toArray(), $fetched->location->toArray());

        $fetched->location()->disassociate();
        $fetched->save();

        $fetched = User::first();

        $this->assertNull($fetched->location);
    }

    public function testDynamicLoadingBelongsToFromFoundRecord(): void
    {
        $location = Location::create(['lat' => 89765, 'long' => -876521234, 'country' => 'The Netherlands', 'city' => 'Amsterdam']);
        /** @var User $user */
        $user = User::create(['name' => 'Daughter', 'alias' => 'daughter']);
        $user->location()->associate($location);
        $user->save();

        $found = User::query()->find($user->getKey());

        $this->assertEquals($location->toArray(), $found->location->toArray());
    }

    public function testEagerLoadingBelongsTo(): void
    {
        /** @var Location $location */
        $location = Location::query()->create(['lat' => 89765, 'long' => -876521234, 'country' => 'The Netherlands', 'city' => 'Amsterdam']);
        /** @var User $user */
        $user = User::query()->create(['name' => 'Daughter', 'alias' => 'daughter']);
        $user->location()->associate($location);
        $user->save();

        $relations = User::with('location')->find($user->getKey())->getRelations();

        $this->assertArrayHasKey('location', $relations);
        $this->assertEquals($location->toArray(), $relations['location']->toArray());
    }
}
