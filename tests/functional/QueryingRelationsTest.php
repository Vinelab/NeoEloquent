<?php

namespace Vinelab\NeoEloquent\Tests\Functional\QueryingRelations;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Vinelab\NeoEloquent\Tests\TestCase;

class QueryingRelationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        (new Post())->getConnection()->getPdo()->run('MATCH (x) DETACH DELETE x');
    }

    public function testQueryingHasCount()
    {
        Post::create(['title' => 'I have no comments =(', 'body' => 'None!']);
        $postWithComment     = Post::create(['title' => 'Nananana', 'body' => 'Commentmaaan']);
        $postWithTwoComments = Post::create(['title' => 'I got two']);
        $postWithTenComments = Post::create(['title' => 'Up yours posts, got 10 here']);

        $comment = new Comment(['text' => 'food']);
        $postWithComment->comments()->save($comment);

        // add two comments to $postWithTwoComments
        for ($i = 0; $i < 2; ++$i) {
            $postWithTwoComments->comments()->create(['text' => "Comment $i"]);
        }
        // add ten comments to $postWithTenComments
        for ($i = 0; $i < 10; ++$i) {
            $postWithTenComments->comments()->create(['text' => "Comment $i"]);
        }

        $allPosts = Post::get();
        $this->assertCount(4, $allPosts);

        $posts = Post::has('comments')->get();
        $this->assertCount(3, $posts);
        $expectedHasComments = [
            $postWithComment->getKey(),
            $postWithTwoComments->getKey(),
            $postWithTenComments->getKey(),
        ];
        foreach ($posts as $post) {
            $this->assertTrue(in_array($post->getKey(), $expectedHasComments));
        }

        $postsWithMoreThanOneComment = Post::has('comments', '>=', 2)->get();
        $this->assertCount(2, $postsWithMoreThanOneComment);
        $expectedWithMoreThanOne = [$postWithTwoComments->getKey(), $postWithTenComments->getKey()];
        foreach ($postsWithMoreThanOneComment as $post) {
            $this->assertTrue(in_array($post->getKey(), $expectedWithMoreThanOne));
        }

        $postWithTen = Post::has('comments', '=', 10)->get();
        $this->assertCount(1, $postWithTen);
        $this->assertEquals($postWithTenComments->toArray(), $postWithTen->first()->toArray());
    }

    public function testQueryingNestedHas()
    {
        // user with a role that has only one permission
        $user       = User::create(['name' => 'cappuccino']);
        $role       = Role::create(['alias' => 'pikachu']);
        $permission = Permission::create(['title' => 'Elephant', 'alias' => 'elephant']);
        $role->permissions()->save($permission);
        $user->roles()->save($role);

        // user with a role that has 2 permissions
        $userWithTwo   = User::create(['name' => 'frappe']);
        $roleWithTwo   = Role::create(['alias' => 'pikachuu']);
        $permissionOne = Permission::create(['title' => 'Goomba', 'alias' => 'goomba']);
        $permissionTwo = Permission::create(['title' => 'Boomba', 'alias' => 'boomba']);
        $roleWithTwo->permissions()->saveMany([$permissionOne, $permissionTwo]);
        $userWithTwo->roles()->save($roleWithTwo);


        // user with a role that has no permission
        $user2 = User::Create(['name' => 'u2']);
        $role2 = Role::create(['alias' => 'nosperm']);

        $user2->roles()->save($role2);

        // get the users where their roles have at least one permission.
        $found = User::has('roles.permissions')->get();

        $this->assertCount(2, $found);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $found[1]);
        $this->assertEquals($userWithTwo->toArray(), $found->where('name', 'frappe')->first()->toArray());
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $found[0]);
        $this->assertEquals($user->toArray(), $found->where('name', 'cappuccino')->first()->toArray());

        $moreThanOnePermission = User::has('roles.permissions', '>=', 2)->get();
        $this->assertCount(1, $moreThanOnePermission);
        $this->assertInstanceOf(
            'Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User',
            $moreThanOnePermission[0]
        );
        $this->assertEquals($userWithTwo->toArray(), $moreThanOnePermission[0]->toArray());
    }

    public function testQueryingWhereHasOne()
    {
        $mrAdmin        = User::create(['name' => 'Rundala']);
        $anotherAdmin   = User::create(['name' => 'Makhoul']);
        $mrsEditor      = User::create(['name' => 'Mr. Moonlight']);
        $mrsManager     = User::create(['name' => 'Batista']);
        $anotherManager = User::create(['name' => 'Quin Tukee']);

        $admin   = Role::create(['alias' => 'admin']);
        $editor  = Role::create(['alias' => 'editor']);
        $manager = Role::create(['alias' => 'manager']);

        $mrAdmin->roles()->save($admin);
        $anotherAdmin->roles()->save($admin);
        $mrsEditor->roles()->save($editor);
        $mrsManager->roles()->save($manager);
        $anotherManager->roles()->save($manager);

        // check admins
        $admins = User::whereHas('roles', function ($q) {
            $q->where('alias', 'admin');
        })->get();
        $this->assertCount(2, $admins);
        $expectedAdmins = [$mrAdmin->getKey(), $anotherAdmin->getKey()];
        $this->assertEqualsCanonicalizing($expectedAdmins, $admins->pluck($mrAdmin->getKeyName())->toArray());

        // check editors
        $editors = User::whereHas('roles', function ($q) {
            $q->where('alias', 'editor');
        })->get();
        $this->assertCount(1, $editors);
        $this->assertEquals($mrsEditor->toArray(), $editors->first()->toArray());

        // check managers
        $expectedManagers = [$mrsManager->getKey(), $anotherManager->getKey()];
        $managers         = User::whereHas('roles', function ($q) {
            $q->where('alias', 'manager');
        })->get();
        $this->assertCount(2, $managers);
        $this->assertEqualsCanonicalizing(
            $expectedManagers,
            $managers->pluck($anotherManager->getKeyName())->toArray()
        );
    }

    public function testQueryingWhereHasById()
    {
        $user = User::create(['name' => 'cappuccino']);
        $role = Role::create(['alias' => 'pikachu']);

        $user->roles()->save($role);

        $found = User::whereHas('roles', function ($q) use ($role) {
            $q->where('alias', $role->getKey());
        })->first();

        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($user->toArray(), $found->toArray());
    }

    public function testQueryingParentWithMultipleWhereHas()
    {
        $user    = User::create(['name' => 'cappuccino']);
        $role    = Role::create(['alias' => 'pikachu']);
        $account = Account::create(['guid' => uniqid()]);

        $user->roles()->save($role);
        $user->account()->save($account);

        $found = User::whereHas('roles', function ($q) use ($role) {
            $q->where('alias', $role->getKey());
        })->whereHas('account', function ($q) use ($account) {
            $q->where('guid', $account->getKey());
        })->where('name', $user->getKey())
                     ->first();

        $this->assertInstanceOf(User::class, $found);
        $this->assertEquals($user->toArray(), $found->toArray());
    }

    public function testQueryingNestedWhereHasUsingProperty()
    {
        // user with a role that has only one permission
        $user       = User::create(['name' => 'cappuccino']);
        $role       = Role::create(['alias' => 'pikachu']);
        $permission = Permission::create(['title' => 'Elephant', 'alias' => 'elephant']);
        $role->permissions()->save($permission);
        $user->roles()->save($role);

        // user with a role that has 2 permissions
        $userWithTwo   = User::create(['name' => 'cappuccino0']);
        $roleWithTwo   = Role::create(['alias' => 'pikachuU']);
        $permissionOne = Permission::create(['title' => 'Goomba', 'alias' => 'goomba']);
        $permissionTwo = Permission::create(['title' => 'Boomba', 'alias' => 'boomba']);
        $roleWithTwo->permissions()->saveMany([$permissionOne, $permissionTwo]);
        $userWithTwo->roles()->save($roleWithTwo);

        $found = User::whereHas('roles', function ($q) use ($role, $permission) {
            $q->where('alias', $role->alias);
            $q->whereHas('permissions', function ($q) use ($permission) {
                $q->where('alias', $permission->alias);
            });
        })->get();

        $this->assertCount(1, $found);
        $this->assertInstanceOf(User::class, $found->first());
        $this->assertEquals($user->toArray(), $found->first()->toArray());
    }

    public function testSavingRelationWithDateTimeAndCarbonInstances()
    {
        $user      = User::create(['name' => 'Andrew Hale']);
        $yesterday = Carbon::now();
        $brother   = new User(['name' => 'Simon Hale', 'dob' => $yesterday]);

        $dt      = new DateTime();
        $someone = User::create(['name' => 'Producer', 'dob' => $dt]);

        $user->colleagues()->save($someone);
        $user->colleagues()->save($brother);

        $andrew = User::first();

        $colleagues = $andrew->colleagues()->get();
        $this->assertEquals(
            $dt->format($andrew->getDateFormat()),
            $colleagues[0]->dob->format($andrew->getDateFormat())
        );
        $this->assertEquals(
            $yesterday->format($andrew->getDateFormat()),
            $colleagues[1]->dob->format($andrew->getDateFormat())
        );
    }
}

class User extends Model
{
    protected $table = 'User';
    protected $fillable = ['name', 'dob'];
    protected $primaryKey = 'name';
    protected $keyType = 'string';
    public $incrementing = false;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function account(): HasOne
    {
        return $this->hasOne(Account::class);
    }

    public function colleagues(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

class Account extends Model
{
    protected $table = 'Account';
    protected $fillable = ['guid'];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'guid';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

class Organization extends Model
{
    protected $table = 'Organization';
    protected $fillable = ['name'];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'name';

    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }
}

class Role extends Model
{
    protected $table = 'Role';
    protected $fillable = ['title', 'alias'];
    protected $primaryKey = 'alias';
    protected $keyType = 'string';
    public $incrementing = false;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}

class Permission extends Model
{
    protected $table = 'Permission';
    protected $fillable = ['title', 'alias'];
    protected $primaryKey = 'title';
    protected $keyType = 'string';
    public $incrementing = false;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}

class Post extends Model
{
    protected $table = 'Post';
    protected $fillable = ['title', 'body', 'summary'];
    protected $primaryKey = 'title';
    public $incrementing = false;
    protected $keyType = 'string';


    public function photos(): HasMany
    {
        return $this->hasMany(HasMany::class);
    }

    public function cover(): HasOne
    {
        return $this->hasOne(Photo::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}

class Tag extends Model
{
    protected $table = 'Tag';
    protected $fillable = ['title'];
    protected $primaryKey = 'title';
    public $incrementing = false;
    protected $keyType = 'string';
}

class Photo extends Model
{
    protected $table = 'Photo';
    protected $fillable = ['url', 'caption', 'metadata'];
    protected $primaryKey = 'url';
    public $incrementing = false;
    protected $keyType = 'string';
}

class Video extends Model
{
    protected $table = 'Video';
    protected $fillable = ['title', 'description', 'stream_url', 'thumbnail'];
    protected $primaryKey = 'title';
    public $incrementing = false;
    protected $keyType = 'string';

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}

class Comment extends Model
{
    protected $table = 'Comment';
    protected $fillable = ['text'];
    protected $primaryKey = 'text';
    public $incrementing = false;
    protected $keyType = 'string';

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
