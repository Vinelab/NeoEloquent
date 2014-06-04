<?php namespace Vinelab\NeoEloquent\Tests\Functional\QueryingRelations;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;

class QueryingRelationsTest extends TestCase {

    public function testQueryingHasCount()
    {
        $postNoComment   = Post::create(['title' => 'I have no comments =(', 'body' => 'None!']);
        $postWithComment = Post::create(['title' => 'Nananana', 'body' => 'Commentmaaan']);
        $postWithTwoComments = Post::create(['title' => 'I got two']);
        $postWithTenComments = Post::create(['tite' => 'Up yours posts, got 10 here']);

        $comment = new Comment(['text' => 'food']);
        $postWithComment->comments()->save($comment);

        // add two comments to $postWithTwoComments
        for($i = 0; $i < 2; $i++)
        {
            $postWithTwoComments->comments()->create(['text' => "Comment $i"]);
        }
        // add ten comments to $postWithTenComments
        for ($i = 0; $i < 10; $i++)
        {
            $postWithTenComments->comments()->create(['text' => "Comment $i"]);
        }

        $allPosts = Post::get();
        $this->assertEquals(4, count($allPosts));

        $posts = Post::has('comments')->get();
        $this->assertEquals(3, count($posts));
        $expectedHasComments = [$postWithComment->id, $postWithTwoComments->id, $postWithTenComments->id];
        foreach ($posts as $key => $post)
        {
            $this->assertTrue(in_array($post->id, $expectedHasComments));
        }

        $postsWithMoreThanOneComment = Post::has('comments', '>=', 2)->get();
        $this->assertEquals(2, count($postsWithMoreThanOneComment));
        $expectedWithMoreThanOne = [$postWithTwoComments->id, $postWithTenComments->id];
        foreach ($postsWithMoreThanOneComment as $post)
        {
            $this->assertTrue(in_array($post->id, $expectedWithMoreThanOne));
        }

        $postWithTen = Post::has('comments', '=', 10)->get();
        $this->assertEquals(1, count($postWithTen));
        $this->assertEquals($postWithTenComments->toArray(), $postWithTen->first()->toArray());
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
        $admins = User::whereHas('roles', function($q) { $q->where('alias', 'admin'); })->get();
        $this->assertEquals(2, count($admins));
        $expectedAdmins = [$mrAdmin, $anotherAdmin];
        foreach ($admins as $key => $admin)
        {
            $this->assertEquals($admin->toArray(), $expectedAdmins[$key]->toArray());
        }
        // check editors
        $editors = User::whereHas('roles', function($q) { $q->where('alias', 'editor'); })->get();
        $this->assertEquals(1, count($editors));
        $this->assertEquals($mrsEditor->toArray(), $editors->first()->toArray());
        // check managers
        $expectedManagers = [$mrsManager, $anotherManager];
        $managers = User::whereHas('roles', function($q) { $q->where('alias', 'manager'); })->get();
        $this->assertEquals(2, count($managers));
        foreach ($managers as $key => $manager)
        {
            $this->assertEquals($manager->toArray(), $expectedManagers[$key]->toArray());
        }
    }

    public function testQueryingWhereHasById()
    {
        $user = User::create(['name' => 'cappuccino']);
        $role = Role::create(['alias' => 'pikachu']);

        $user->roles()->save($role);

        $found = User::whereHas('roles', function($q) use ($role)
        {
            $q->where('id', $role->getKey());
        })->first();

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', $found);
        $this->assertEquals($user->toArray(), $found->toArray());
    }

}

class User extends Model {

    protected $label = 'User';

    protected $fillable = ['name'];

    public function roles()
    {
        return $this->hasMany('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Role', 'PERMITTED');
    }
}

class Role extends Model {

    protected $label = 'Role';

    protected $fillable = ['alias'];

    public function user()
    {
        return $this->belongsTo('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\User', 'PERMITTED');
    }
}

class Post extends Model {

    protected $label = 'Post';

    protected $fillable = ['title', 'body'];

    public function comments()
    {
        return $this->hasMany('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Comment', 'COMMENT');
    }
}

class Comment extends Model {

    protected $label = 'Comment';

    protected $fillable = ['text'];

    public function post()
    {
        return $this->belongsTo('Vinelab\NeoEloquent\Tests\Functional\QueryingRelations\Post', 'COMMENT');
    }
}
