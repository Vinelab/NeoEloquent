<?php

namespace Vinelab\NeoEloquent\Tests\Functional\QueryingRelations;

use DateTime;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Mockery as M;
use Carbon\Carbon;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;

class QueryingRelationsTest extends TestCase
{

    public function testQueryingHasCount()
    {
        $postNoComment = Post::create(['title' => 'I have no comments =(', 'body' => 'None!']);
        $postWithComment = Post::create(['title' => 'Nananana', 'body' => 'Commentmaaan']);
        $postWithTwoComments = Post::create(['title' => 'I got two']);
        $postWithTenComments = Post::create(['tite' => 'Up yours posts, got 10 here']);

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
        $this->assertEquals(4, count($allPosts));

        $posts = Post::has('comments')->get();
        $this->assertEquals(3, count($posts));
        $expectedHasComments = [$postWithComment->id, $postWithTwoComments->id, $postWithTenComments->id];
        foreach ($posts as $key => $post) {
            $this->assertTrue(in_array($post->id, $expectedHasComments));
        }

        $postsWithMoreThanOneComment = Post::has('comments', '>=', 2)->get();
        $this->assertEquals(2, count($postsWithMoreThanOneComment));
        $expectedWithMoreThanOne = [$postWithTwoComments->id, $postWithTenComments->id];
        foreach ($postsWithMoreThanOneComment as $post) {
            $this->assertTrue(in_array($post->id, $expectedWithMoreThanOne));
        }

        $postWithTen = Post::has('comments', '=', 10)->get();
        $this->assertEquals(1, count($postWithTen));
        $this->assertEquals($postWithTenComments->toArray(), $postWithTen->first()->toArray());
    }

    public function testQueryingNestedHas()
    {
        // user with a role that has only one permission
        $user = User::create(['name' => 'cappuccino']);
        $role = Role::create(['alias' => 'pikachu']);
        $permission = Permission::create(['title' => 'Elephant', 'alias' => 'elephant']);
        $role->permissions()->save($permission);
        $user->roles()->save($role);

        // user with a role that has 2 permissions
        $userWithTwo = User::create(['name' => 'frappe']);
        $roleWithTwo = Role::create(['alias' => 'pikachu']);
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

        $this->assertEquals(2, count($found));
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $found[1]);
        $this->assertEquals($userWithTwo->toArray(), $found->where('name', 'frappe')->first()->toArray());
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $found[0]);
        $this->assertEquals($user->toArray(), $found->where('name', 'cappuccino')->first()->toArray());

        $moreThanOnePermission = User::has('roles.permissions', '>=', 2)->get();
        $this->assertEquals(1, count($moreThanOnePermission));
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $moreThanOnePermission[0]);
        $this->assertEquals($userWithTwo->toArray(), $moreThanOnePermission[0]->toArray());
    }

    public function testQueryingWhereHasOne()
    {
        $mrAdmin = User::create(['name' => 'Rundala']);
        $anotherAdmin = User::create(['name' => 'Makhoul']);
        $mrsEditor = User::create(['name' => 'Mr. Moonlight']);
        $mrsManager = User::create(['name' => 'Batista']);
        $anotherManager = User::create(['name' => 'Quin Tukee']);

        $admin = Role::create(['alias' => 'admin']);
        $editor = Role::create(['alias' => 'editor']);
        $manager = Role::create(['alias' => 'manager']);

        $mrAdmin->roles()->save($admin);
        $anotherAdmin->roles()->save($admin);
        $mrsEditor->roles()->save($editor);
        $mrsManager->roles()->save($manager);
        $anotherManager->roles()->save($manager);

        // check admins
        $admins = User::whereHas('roles', function ($q) { $q->where('alias', 'admin'); })->get();
        $this->assertEquals(2, count($admins));
        $expectedAdmins = [$mrAdmin, $anotherAdmin];
        $expectedAdmins = array_map(function ($admin) {
            return $admin->toArray();
        }, $expectedAdmins);
        foreach ($admins as $key => $admin) {
            $this->assertContains($admin->toArray()['id'], array_map(static fn(array $admin) => $admin['id'], $expectedAdmins));
        }
        // check editors
        $editors = User::whereHas('roles', function ($q) { $q->where('alias', 'editor'); })->get();
        $this->assertEquals(1, count($editors));
        $this->assertEquals($mrsEditor->toArray(), $editors->first()->toArray());
        // check managers
        $expectedManagers = [$mrsManager, $anotherManager];
        $managers = User::whereHas('roles', function ($q) { $q->where('alias', 'manager'); })->get();
        $this->assertEquals(2, count($managers));
        $expectedManagers = array_map(function ($manager) {
            return $manager->toArray();
        }, $expectedManagers);
        foreach ($managers as $key => $manager) {
            $this->assertContains($manager->toArray()['id'], array_map(static fn(array $manager) => $manager['id'], $expectedManagers));
        }
    }

    public function testQueryingWhereHasById()
    {
        $user = User::create(['name' => 'cappuccino']);
        $role = Role::create(['alias' => 'pikachu']);

        $user->roles()->save($role);

        $found = User::whereHas('roles', function ($q) use ($role) {
            $q->where('id', $role->getKey());
        })->first();

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $found);
        $this->assertEquals($user->toArray(), $found->toArray());
    }

    public function testQueryingParentWithWhereHas()
    {
        $user = User::create(['name' => 'cappuccino']);
        $role = Role::create(['alias' => 'pikachu']);

        $user->roles()->save($role);

        $found = User::whereHas('roles', function ($q) use ($role) {
            $q->where('id', $role->id);
        })->where('id', $user->id)->first();

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $found);
        $this->assertEquals($user->toArray(), $found->toArray());
    }

    public function testQueryingParentWithMultipleWhereHas()
    {
        $user = User::create(['name' => 'cappuccino']);
        $role = Role::create(['alias' => 'pikachu']);
        $account = Account::create(['guid' => uniqid()]);

        $user->roles()->save($role);
        $user->account()->save($account);

        $found = User::whereHas('roles', function ($q) use ($role) { $q->where('id', $role->id); })
            ->whereHas('account', function ($q) use ($account) { $q->where('id', $account->id); })
            ->where('id', $user->id)->first();

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $found);
        $this->assertEquals($user->toArray(), $found->toArray());
    }

    public function testQueryingNestedWhereHasUsingId()
    {
        // user with a role that has only one permission
        $user = User::create(['name' => 'cappuccino']);
        $role = Role::create(['alias' => 'pikachu']);
        $permission = Permission::create(['title' => 'Elephant', 'alias' => 'elephant']);
        $role->permissions()->save($permission);
        $user->roles()->save($role);

        // user with a role that has 2 permissions
        $userWithTwo = User::create(['name' => 'cappuccino']);
        $roleWithTwo = Role::create(['alias' => 'pikachu']);
        $permissionOne = Permission::create(['title' => 'Goomba', 'alias' => 'goomba']);
        $permissionTwo = Permission::create(['title' => 'Boomba', 'alias' => 'boomba']);
        $roleWithTwo->permissions()->saveMany([$permissionOne, $permissionTwo]);
        $userWithTwo->roles()->save($roleWithTwo);

        $found = User::whereHas('roles', function($q) use($role, $permission) {
            $q->where($role->getKeyName(), $role->getKey());
            $q->whereHas('permissions', function($q) use($permission) {
                $q->where($permission->getKeyName(), $permission->getKey());
            });
        })->get();

        $this->assertEquals(1, count($found));
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $found->first());
        $this->assertEquals($user->toArray(), $found->first()->toArray());
    }

    public function testQueryingNestedWhereHasUsingProperty()
    {
        // user with a role that has only one permission
        $user = User::create(['name' => 'cappuccino']);
        $role = Role::create(['alias' => 'pikachu']);
        $permission = Permission::create(['title' => 'Elephant', 'alias' => 'elephant']);
        $role->permissions()->save($permission);
        $user->roles()->save($role);

        // user with a role that has 2 permissions
        $userWithTwo = User::create(['name' => 'cappuccino']);
        $roleWithTwo = Role::create(['alias' => 'pikachu']);
        $permissionOne = Permission::create(['title' => 'Goomba', 'alias' => 'goomba']);
        $permissionTwo = Permission::create(['title' => 'Boomba', 'alias' => 'boomba']);
        $roleWithTwo->permissions()->saveMany([$permissionOne, $permissionTwo]);
        $userWithTwo->roles()->save($roleWithTwo);

        $found = User::whereHas('roles', function($q) use($role, $permission) {
            $q->where('alias', $role->alias);
            $q->whereHas('permissions', function($q) use($permission) {
                $q->where('alias', $permission->alias);
            });
        })->get();

        $this->assertEquals(1, count($found));
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $found->first());
        $this->assertEquals($user->toArray(), $found->first()->toArray());
    }

    public function testCreatingModelWithSingleRelation()
    {
        $account = ['guid' => uniqid()];
        $user = User::createWith(['name' => 'Misteek'], compact('account'));

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $user);
        $this->assertTrue($user->exists);
        $this->assertGreaterThanOrEqual(0, $user->id);

        $related = $user->account;
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Account', $related);
        $this->assertNotNull($related->created_at);
        $this->assertNotNull($related->updated_at);

        $attrs = $related->toArray();
        unset($attrs['id']);
        unset($attrs['created_at']);
        unset($attrs['updated_at']);
        $this->assertEquals($account, $attrs);
    }

    public function testCreatingModelWithRelations()
    {
        // Creating a role with its permissions.
        $role = ['title' => 'Admin', 'alias' => 'admin'];

        $permissions = [
            new Permission(['title' => 'Create Records', 'alias' => 'create', 'dodid' => 'done']),
            new Permission(['title' => 'Read Records', 'alias' => 'read', 'dont be so' => 'down']),
            ['title' => 'Update Records', 'alias' => 'update'],
            ['title' => 'Delete Records', 'alias' => 'delete'],
        ];

        $role = Role::createWith($role, compact('permissions'));

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Role', $role);
        $this->assertTrue($role->exists);
        $this->assertGreaterThanOrEqual(0, $role->id);

        foreach ($role->permissions as $key => $permission) {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Permission', $permission);
            $this->assertGreaterThan(0, $permission->id);
            $this->assertNotNull($permission->created_at);
            $this->assertNotNull($permission->updated_at);
            $attrs = $permission->toArray();
            unset($attrs['id']);
            unset($attrs['created_at']);
            unset($attrs['updated_at']);
            if ($permissions[$key] instanceof Permission) {
                $permission = $permissions[$key];
                $permission = $permission->toArray();
                unset($permission['id']);
                unset($permission['created_at']);
                unset($permission['updated_at']);
                $this->assertEquals($permission, $attrs);
            } else {
                $this->assertEquals($permissions[$key], $attrs);
            }
        }
    }

    public function testCreatingModelWithMultipleRelationTypes()
    {
        $post = ['title' => 'Trip to Bedlam', 'body' => 'It was wonderful! Check the embedded media'];

        $photos = [
            [
                'url' => 'http://somewere.in.bedlam.net',
                'caption' => 'Gunatanamo',
                'metadata' => '...',
            ],
            [
                'url' => 'http://another-place.in.bedlam.net',
                'caption' => 'Gunatanamo',
                'metadata' => '...',
            ],
        ];

        $videos = [
            [
                'title' => 'Fun at the borders',
                'description' => 'Once upon a time...',
                'stream_url' => 'http://stream.that.shit.io',
                'thumbnail' => 'http://sneak.peek.io',
            ],
        ];

        $post = Post::createWith($post, compact('photos', 'videos'));

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Post', $post);
        $this->assertTrue($post->exists);
        $this->assertGreaterThanOrEqual(0, $post->id);

        foreach ($post->photos as $key => $photo) {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Photo', $photo);
            $this->assertGreaterThan(0, $photo->id);
            $this->assertNotNull($photo->created_at);
            $this->assertNotNull($photo->updated_at);
            $attrs = $photo->toArray();
            unset($attrs['id']);
            unset($attrs['created_at']);
            unset($attrs['updated_at']);
            $this->assertEquals($photos[$key], $attrs);
        }

        $video = $post->videos->first();
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Video', $video);
        $this->assertNotNull($video->created_at);
        $this->assertNotNull($video->updated_at);
        $attrs = $video->toArray();
        unset($attrs['id']);
        unset($attrs['created_at']);
        unset($attrs['updated_at']);
        $this->assertEquals($videos[0], $attrs);
    }

    public function testCreatingModelWithSingleInverseRelation()
    {
        $user = ['name' => 'Some Name'];
        $account = Account::createWith(['guid' => 'globalid'], compact('user'));

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Account', $account);
        $this->assertTrue($account->exists);
        $this->assertGreaterThanOrEqual(0, $account->id);

        $related = $account->user;
        $this->assertNotNull($related->created_at);
        $this->assertNotNull($related->updated_at);
        $attrs = $related->toArray();
        unset($attrs['id']);
        unset($attrs['created_at']);
        unset($attrs['updated_at']);
        $this->assertEquals($attrs, $user);
    }

    public function testCreatingModelWithMultiInverseRelations()
    {
        $users = new User(['name' => 'safastak']);
        $role = Role::createWith(['alias' => 'admin'], compact('users'));

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Role', $role);
        $this->assertTrue($role->exists);
        $this->assertGreaterThanOrEqual(0, $role->id);

        $related = $role->users->first();
        $this->assertNotNull($related->created_at);
        $this->assertNotNull($related->updated_at);
        $attrs = $related->toArray();
        unset($attrs['id']);
        unset($attrs['created_at']);
        unset($attrs['updated_at']);
        $usersArray = $users->toArray();
        unset($usersArray['id']);
        unset($usersArray['created_at']);
        unset($usersArray['updated_at']);
        $this->assertEquals($attrs, $usersArray);
    }

    public function testCreatingModelWithAttachedRelatedModels()
    {
        $tag1 = Tag::create(['title' => 'php']);
        $tag2 = Tag::create(['title' => 'development']);

        $tags = [$tag1, $tag2];
        $post = Post::createWith(['title' => '...', 'body' => '...'], compact('tags'));

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Post', $post);

        $related = $post->tags;
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Collection', $related);
        $this->assertEquals(2, count($related));

        foreach ($related as $key => $tag) {
            $this->assertEquals($tags[$key]->toArray(), $tag->toArray());
        }
    }

    /**
     * Regression test for issue where createWith ignores creating timestamps for record.
     *
     * @see  https://github.com/Vinelab/NeoEloquent/issues/17
     */
    public function testCreateWithAddsTimestamps()
    {
        $tag1 = Tag::create(['title' => 'php']);
        $tag2 = Tag::create(['title' => 'development']);
        $tags = [$tag1->getKey(), $tag2->getKey()];

        $post = Post::createWith(['title' => '...', 'body' => '...'], compact('tags'));

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Post', $post);

        $this->assertNotNull($post->created_at);
        $this->assertNotNull($post->updated_at);

        $related = $post->tags;
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Collection', $related);
        $this->assertEquals(2, count($related));

        foreach ($related as $key => $tag) {
            $expected = 'tag'.($key + 1);
            $this->assertEquals($$expected->toArray(), $tag->toArray());
        }
    }

    public function testCreatWithPassesThroughFillables()
    {
        $tag1 = Tag::create(['title' => 'php']);
        $tag2 = Tag::create(['title' => 'development']);
        $tags = [$tag1->getKey(), $tag2->getKey()];

        $post = Post::createWith(['title' => '...', 'body' => '...', 'mother' => 'something', 'father' => 'wanted'], compact('tags'));

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Post', $post);

        $this->assertNull($post->mother);
        $this->assertNull($post->father);
        $this->assertNotNull($post->created_at);
        $this->assertNotNull($post->updated_at);

        $related = $post->tags;
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Collection', $related);
        $this->assertEquals(2, count($related));

        foreach ($related as $key => $tag) {
            $expected = 'tag'.($key + 1);
            $this->assertEquals($$expected->toArray(), $tag->toArray());
        }
    }

    public function testCreatingModelWithNullAndBooleanValues()
    {
        $tag1 = Tag::create(['title' => 'php']);
        $tag2 = Tag::create(['title' => 'development']);
        $tags = [$tag1->getKey(), $tag2->getKey()];

        $post = Post::createWith(['title' => false, 'body' => true, 'summary' => null], compact('tags'));

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Post', $post);

        $this->assertFalse($post->title);
        $this->assertTrue($post->body);
        $this->assertNull($post->summary);
        $this->assertNotNull($post->created_at);
        $this->assertNotNull($post->updated_at);

        $related = $post->tags;
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Collection', $related);
        $this->assertEquals(2, count($related));

        foreach ($related as $key => $tag) {
            $expected = 'tag'.($key + 1);
            $this->assertEquals($$expected->toArray(), $tag->toArray());
        }
    }

    public function testCreatingModeWithAttachedModelIds()
    {
        $tag1 = Tag::create(['title' => 'php']);
        $tag2 = Tag::create(['title' => 'development']);

        $tags = [$tag1->getKey(), $tag2->getKey()];
        $post = Post::createWith(['title' => '...', 'body' => '...'], compact('tags'));

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Post', $post);

        $related = $post->tags;
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Collection', $related);
        $this->assertEquals(2, count($related));

        foreach ($related as $key => $tag) {
            $expected = 'tag'.($key + 1);
            $this->assertEquals($$expected->toArray(), $tag->toArray());
        }
    }

    public function testCreatingModelWithAttachedSingleId()
    {
        $tag = Tag::create(['title' => 'php']);
        $post = Post::createWith(['title' => '...', 'body' => '...'], ['tags' => $tag->getKey()]);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Post', $post);

        $related = $post->tags;
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Collection', $related);
        $this->assertEquals(1, count($related));
        $this->assertEquals($tag->toArray(), $related->first()->toArray());
    }

    public function testCreatingModelWithAttachedSingleModel()
    {
        $tag = Tag::create(['title' => 'php']);
        $post = Post::createWith(['title' => '...', 'body' => '...'], ['tags' => $tag]);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Post', $post);

        $related = $post->tags;
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Collection', $related);
        $this->assertEquals(1, count($related));
        $this->assertEquals($tag->toArray(), $related->first()->toArray());
    }

    public function testCreatingModelWithMixedRelationsAndPassingCollection()
    {
        $tag = Tag::create(['title' => 'php']);
        $tags = [
                $tag,
                ['title' => 'developer'],
                new Tag(['title' => 'laravel']),
        ];

        $post = Post::createWith(['title' => 'foo', 'body' => 'bar'], compact('tags'));

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Post', $post);
        $related = $post->tags;
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Collection', $related);
        $this->assertEquals(3, count($related));

        $tags = Tag::all();

        $another = Post::createWith(['title' => 'foo', 'body' => 'bar'], compact('tags'));
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Post', $another);
        $this->assertEquals(3, count($related));
    }

    /**
     * Regression for issue #9.
     *
     * @see https://github.com/Vinelab/NeoEloquent/issues/9
     */
    public function testCreateModelWithMultiRelationOfSameRelatedModel()
    {
        $post = Post::createWith(['title' => 'tayta', 'body' => 'one hot bowy'], [
            'photos' => ['url' => 'my.photo.url'],
            'cover' => ['url' => 'my.cover.url'],
        ]);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Post', $post);

        $this->assertEquals('my.photo.url', $post->photos->first()->url);
        $this->assertEquals('my.cover.url', $post->cover->url);
    }

    /**
     * Regression test for creating recursively connected models.
     *
     * @see https://github.com/Vinelab/NeoEloquent/issues/7
     */
    public function testCreatingModelWithExistingRecursivelyRelatedModel()
    {
        $jon = User::create(['name' => 'Jon Ronson']);
        $morgan = User::create(['name' => 'Morgan Spurlock']);

        $user = User::createWith(['name' => 'Ken Robinson'], [
            'colleagues' => [$morgan, $jon],
        ]);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $user);
    }

    public function testEagerLoadingNestedRelationship()
    {
        $user = User::create(['name' => 'cappuccino']);
        $role = Role::createWith(['alias' => 'pikachu'], ['permissions' => ['title' => 'Perr', 'alias' => 'perr']]);

        $user->roles()->save($role);
        // Eager load so that when we assert we make sure they're there
        $user->roles->first()->permissions;

        $found = User::with('roles.permissions')
            ->whereHas('roles', function ($q) use ($role) { $q->where('id', $role->id); })
            ->first();

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $found);
        $this->assertArrayHasKey('roles', $found->getRelations());
        $this->assertArrayHasKey('permissions', $found->roles->first()->getRelations());
        $this->assertEquals($user->toArray(), $found->toArray());
    }

    public function testInverseEagerLoadingOneNestedRelationship()
    {
        $user = User::createWith(['name' => 'cappuccino'], ['account' => ['guid' => 'anID']]);
        $role = Role::create(['alias' => 'pikachu']);

        $user->roles()->save($role);
        // Eager load so that when we assert we make sure they're there
        $acc = $role->users->first()->account;

        $roleFound = Role::with('users.account')
            ->whereHas('users', function ($q) use ($user) { $q->where('id', $user->getKey()); })
            ->first();

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Role', $roleFound);
        $this->assertArrayHasKey('users', $roleFound->getRelations());
        $this->assertArrayHasKey('account', $roleFound->users->first()->getRelations());
        $this->assertEquals('anID', $roleFound->users->first()->account->guid);
        $this->assertEquals($role->toArray(), $roleFound->toArray());
    }

    public function testDoubleInverseEagerLoadingBelongsToRelationship()
    {
        $user = User::createWith(['name' => 'cappuccino'], ['organization' => ['name' => 'Pokemon']]);
        // Eager load so that when we assert we make sure they're there
        $role = Role::create(['alias' => 'pikachu']);

        $user->roles()->save($role);
        // Eager load so that when we assert we make sure they're there
        $org = $role->users->first()->organization;

        $roleFound = Role::with('users.organization')
            ->whereHas('users', function ($q) use ($user) { $q->where('id', $user->getKey()); })
            ->first();

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Role', $roleFound);
        $this->assertArrayHasKey('users', $roleFound->getRelations());
        $this->assertArrayHasKey('organization', $roleFound->users->first()->getRelations());
        $this->assertEquals('Pokemon', $roleFound->users->first()->organization->name);
        $this->assertEquals($role->toArray(), $roleFound->toArray());
    }

    public function testQueryingRelatedModel()
    {
        $user = User::createWith(['name' => 'Beluga'], [
            'roles' => [
                ['title' => 'Read Things', 'alias' => 'read'],
                ['title' => 'Write Things', 'alias' => 'write'],
            ],
        ]);

        $read = Role::where('alias', 'read')->first();
        $this->assertEquals('read', $read->alias);
        $readFound = $user->roles()->where('alias', 'read')->first();
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Role', $readFound);
        $this->assertEquals($read, $readFound);

        $write = Role::where('alias', 'write')->first();
        $this->assertEquals('write', $write->alias);
        $writeFound = $user->roles()->where('alias', 'write')->first();
        $this->assertEquals($write, $writeFound);
    }

    public function testDirectRecursiveRelationQuery()
    {
        $user = User::createWith(['name' => 'captain'], ['colleagues' => ['name' => 'acme']]);
        $acme = User::where('name', 'acme')->first();
        $found = $user->colleagues()->where('name', 'acme')->first();
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $found);

        $this->assertEquals($acme, $found);
    }

    public function testSavingCreateWithRelationWithDateTimeAndCarbonInstances()
    {
        $yesterday = Carbon::now()->subDay();
        $dt = new DateTime();

        $user = User::createWith(['name' => 'Some Name', 'dob' => $yesterday],
            ['colleagues' => ['name' => 'Protectron', 'dob' => $dt],
        ]);

        $houwe = User::first();
        $colleague = $houwe->colleagues()->first();

        $this->assertEquals($yesterday->format(User::getDateFormat()), $houwe->dob);
        $this->assertEquals($dt->format(User::getDateFormat()), $colleague->dob);
    }

    public function testSavingRelationWithDateTimeAndCarbonInstances()
    {
        $user = User::create(['name' => 'Andrew Hale']);
        $yesterday = Carbon::now();
        $brother = new User(['name' => 'Simon Hale', 'dob' => $yesterday]);

        $dt = new DateTime();
        $someone = User::create(['name' => 'Producer', 'dob' => $dt]);

        $user->colleagues()->save($someone);
        $user->colleagues()->save($brother);

        $andrew = User::first();

        $colleagues = $andrew->colleagues()->get();
        $this->assertEquals($dt->format(User::getDateFormat()), $colleagues[0]->dob);
        $this->assertEquals($yesterday->format(User::getDateFormat()), $colleagues[1]->dob);
    }

    public function testCreateWithReturnsRelatedModelsAsRelations()
    {
        $user = Post::createWith(
            ['title' => 'foo tit', 'body' => 'some body'],
            [
                'cover' => ['url' => 'http://url'],
                'tags' => ['title' => 'theTag'],
            ]
        );

        $relations = $user->getRelations();

        $this->assertArrayHasKey('cover', $relations);
        $cover = $user->toArray()['cover'];
        $this->assertArrayHasKey('id', $cover);
        $this->assertEquals('http://url', $cover['url']);

        $this->assertArrayHasKey('tags', $relations);
        $tags = $user->toArray()['tags'];
        $this->assertCount(1, $tags);

        $this->assertNotEmpty($tags[0]['id']);
        $this->assertEquals('theTag', $tags[0]['title']);
    }

    public function testEagerloadingRelationships()
    {
        $fooPost = Post::createWith(
            ['title' => 'foo tit', 'body' => 'some body'],
            [
                'cover' => ['url' => 'http://url'],
                'tags' => ['title' => 'theTag'],
            ]
        );

        $anotherPost = Post::createWith(
            ['title' => 'another tit', 'body' => 'another body'],
            [
                'cover' => ['url' => 'http://another.url'],
                'tags' => ['title' => 'anotherTag'],
            ]
        );

        $posts = Post::with(['cover', 'tags'])->get();

        $this->assertEquals(2, count($posts));

        foreach ($posts as $post) {
            $this->assertNotNull($post->cover);
            $this->assertEquals(1, count($post->tags));
        }

        $this->assertEquals('http://url', $posts[0]->cover->url);
        $this->assertEquals('theTag', $posts[0]->tags->first()->title);

        $this->assertEquals('http://another.url', $posts[1]->cover->url);
        $this->assertEquals('anotherTag', $posts[1]->tags->first()->title);
    }

    public function testBulkDeletingOutgoingRelation()
    {
        $fooPost = Post::createWith(
            ['title' => 'foo tit', 'body' => 'some body'],
            [
                'cover' => ['url' => 'http://url'],
                'tags' => [
                    ['title' => 'theTag'],
                    ['title' => 'anotherTag'],
                ],
            ]
        );

        $fooPost->tags()->delete();

        $this->assertEquals(0, count(Post::first()->tags));
    }

    public function testBulkDeletingIncomingRelation()
    {
        $users = [new User(['name' => 'safastak']), new User(['name' => 'boukharest'])];
        $role = Role::createWith(['alias' => 'admin'], compact('users'));

        $role->users()->delete();

        $this->assertEquals(0, count(Role::first()->users));
    }
}

class User extends Model
{
    protected $table = 'User';
    protected $fillable = ['name', 'dob'];
    protected $primaryKey = 'name';
    protected $keyType = 'string';
    public $incrementing = false;

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
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

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
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
