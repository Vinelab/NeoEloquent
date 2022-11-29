<?php

namespace Vinelab\NeoEloquent\Tests\Functional\ParameterGrouping;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Mockery as M;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\Eloquent\Model;
use Vinelab\NeoEloquent\Query\Builder;
use Vinelab\NeoEloquent\Tests\TestCase;

class User extends Model
{
    protected $table = 'User';
    protected $fillable = ['name'];
    protected $primaryKey = 'name';
    protected $keyType = 'string';

    public function facebookAccount(): HasOne
    {
        return $this->hasOne(FacebookAccount::class);
    }
}

class FacebookAccount extends Model
{
    protected $table = 'SocialAccount';
    protected $fillable = ['gender', 'age', 'interest'];
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::saving(function (Model $m) {
            $m->id = Uuid::getFactory()->uuid4()->toString();
        });
    }
}

class ParameterGroupingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        (new FacebookAccount())->getConnection()->getPdo()->run('MATCH (x) DETACH DELETE x');
    }

    public function testNestedWhereClause()
    {
        $searchedUser = User::create(['name' => 'John Doe']);
        $searchedUser->facebookAccount()->save(FacebookAccount::create([
            'gender' => 'male',
            'age' => 20,
            'interest' => 'Dancing',
        ]));

        $anotherUser = User::create(['name' => 'John Smith']);
        $anotherUser->facebookAccount()->save(FacebookAccount::create([
            'gender' => 'male',
            'age' => 30,
            'interest' => 'Music',
        ]));

        $users = User::whereHas('facebookAccount', function ($query) {
            $query->where('gender', 'male')->where(function ($query) {
                $query->orWhere('age', '<', 24)->orWhere('interest', 'Entertainment');
            });
        })->get();

        $this->assertCount(1, $users);
        $this->assertEquals($searchedUser->name, $users->shift()->name);
    }
}
