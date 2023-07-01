<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinelab\NeoEloquent\Tests\Fixtures\Comment;
use Vinelab\NeoEloquent\Tests\Fixtures\Post;
use Vinelab\NeoEloquent\Tests\Fixtures\Tag;
use Vinelab\NeoEloquent\Tests\Fixtures\User;
use Vinelab\NeoEloquent\Tests\Fixtures\Video;
use Vinelab\NeoEloquent\Tests\TestCase;

class PolymorphicHyperMorphToTest extends TestCase
{
    use RefreshDatabase;

    public function testCreatingUserCommentOnPostAndVideo()
    {
        $user = User::create(['name' => 'Hmm...']);
        User::create(['name' => 'I Comment On Posts']);
        User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);// Grab them back

        $post = $user->posts->first();

        $this->assertInstanceOf(Post::class, $post);
        $this->assertEquals('Another Place', $post->getKey());
    }

    public function testAttachingNonExistingModelIds()
    {
        $user = User::create(['name' => 'Hmm...']);
        $user->posts()->create(['title' => 'A little posty post.']);
        $post = $user->posts()->first();

        $this->expectException(ModelNotFoundException::class);
        $user->posts()->findOrFail(9999999999);
    }
}
