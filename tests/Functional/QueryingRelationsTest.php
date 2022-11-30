<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use DateTime;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinelab\NeoEloquent\Tests\Fixtures\Comment;
use Vinelab\NeoEloquent\Tests\Fixtures\Post;
use Vinelab\NeoEloquent\Tests\Fixtures\Role;
use Vinelab\NeoEloquent\Tests\Fixtures\User;
use Vinelab\NeoEloquent\Tests\TestCase;

class QueryingRelationsTest extends TestCase
{
    use RefreshDatabase;

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
        $permission = \Vinelab\NeoEloquent\Tests\Fixtures\Permission::create(['title' => 'Elephant', 'alias' => 'elephant']);
        $role->permissions()->save($permission);
        $user->roles()->save($role);

        // user with a role that has 2 permissions
        $userWithTwo   = User::create(['name' => 'frappe']);
        $roleWithTwo   = Role::create(['alias' => 'pikachuu']);
        $permissionOne = \Vinelab\NeoEloquent\Tests\Fixtures\Permission::create(['title' => 'Goomba', 'alias' => 'goomba']);
        $permissionTwo = \Vinelab\NeoEloquent\Tests\Fixtures\Permission::create(['title' => 'Boomba', 'alias' => 'boomba']);
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
        $account = \Vinelab\NeoEloquent\Tests\Fixtures\Account::create(['guid' => uniqid()]);

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
        $permission = \Vinelab\NeoEloquent\Tests\Fixtures\Permission::create(['title' => 'Elephant', 'alias' => 'elephant']);
        $role->permissions()->save($permission);
        $user->roles()->save($role);

        // user with a role that has 2 permissions
        $userWithTwo   = User::create(['name' => 'cappuccino0']);
        $roleWithTwo   = Role::create(['alias' => 'pikachuU']);
        $permissionOne = \Vinelab\NeoEloquent\Tests\Fixtures\Permission::create(['title' => 'Goomba', 'alias' => 'goomba']);
        $permissionTwo = \Vinelab\NeoEloquent\Tests\Fixtures\Permission::create(['title' => 'Boomba', 'alias' => 'boomba']);
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
