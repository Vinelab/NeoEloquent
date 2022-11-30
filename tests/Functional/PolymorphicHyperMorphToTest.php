<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinelab\NeoEloquent\Tests\Fixtures\Comment;
use Vinelab\NeoEloquent\Tests\Fixtures\Post;
use Vinelab\NeoEloquent\Tests\Fixtures\Tag;
use Vinelab\NeoEloquent\Tests\Fixtures\Video;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Tests\Fixtures\User;

class PolymorphicHyperMorphToTest extends TestCase
{
    use RefreshDatabase;

    public function testCreatingUserCommentOnPostAndVideo()
    {
        $user = User::create(['name' => 'Hmm...']);
        User::create(['name' => 'I Comment On Posts']);
        User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back

        $post = $user->posts->first();
        $video = $user->videos->first();

        $this->assertInstanceOf(Post::class, $post);
        $this->assertInstanceOf(Video::class, $video);
        $this->assertEquals('Another Place', $post->getKey());
        $this->assertEquals('When We Meet', $video->getKey());

        $post->comments()->create(['text' => 'a']);
        $video->comments()->create(['text' => 'b']);

        $postComment = $post->comments->first();
        $videoComment = $video->comments->first();

        $this->assertInstanceOf(Comment::class, $postComment);
        $this->assertInstanceOf(Comment::class, $videoComment);
        $this->assertEquals('a', $postComment->getKey());
        $this->assertEquals('b', $videoComment->getKey());
    }

    public function testSavingUserCommentOnPostAndVideo()
    {
        $user = User::create(['name' => 'Hmm...']);
        User::create(['name' => 'I Comment On Posts']);
        User::create(['name' => 'I Comment On Videos']);

        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post = $user->posts->first();
        $video = $user->videos->first();

        $commentOnPost = new Comment(['text' => 'Another Place', 'body' => 'To Go..']);
        $commentOnVideo = new Comment(['text' => 'When We Meet', 'url' => 'http://some.url']);
        $post->comments()->save($commentOnPost);
        $video->comments()->save($commentOnVideo);

        $post->refresh();
        $video->refresh();

        $this->assertFalse($post->comments->first()->is($video->comments->first()));
    }

    public function testAttachingManyIds()
    {
        $user = User::create(['name' => 'Hmm...']);
        User::create(['name' => 'I Comment On Posts']);
        User::create(['name' => 'I Comment On Videos']);

        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        $commentOnPost         = Comment::create(['text' => 'Another Place']);
        $anotherCommentOnPost  = Comment::create(['text' => 'Here and there']);
        $commentOnVideo        = Comment::create(['text' => 'When We Meet']);
        $anotherCommentOnVideo = Comment::create(['text' => 'That is good']);

        $video->comments()->saveMany([$commentOnPost, $anotherCommentOnPost]);

        foreach ($video->comments as $comment) {
            $this->assertInstanceOf(Comment::class, $comment);
            $this->assertTrue($comment->exists());
        }

        $post->comments()->saveMany([$commentOnVideo, $anotherCommentOnVideo]);
        foreach ($post->comments as $comment) {
            $this->assertInstanceOf(Comment::class, $comment);
            $this->assertTrue($comment->exists());
        }
    }

    public function testAttachingNonExistingModelIds()
    {
        $user = User::create(['name' => 'Hmm...']);
        $user->posts()->create(['title' => 'A little posty post.']);
        $post = $user->posts()->first();

        $this->expectException(ModelNotFoundException::class);
        $post->comments()->findOrFail(9999999999);
    }

    public function testManyToManyMorphing(): void
    {
        $tagX = Tag::create(['title' => 'tag x']);
        $tagY = Tag::create(['title' => 'tag y']);
        $tagZ = Tag::create(['title' => 'tag z']);

        $postX = Post::create(['title' => 'a', 'body' => 'abc']);
        $postY = Post::create(['title' => 'b', 'body' => 'def']);
        $postZ = Post::create(['title' => 'c', 'body' => 'ghi']);

        $videoX = Video::create(['title' => 'ab']);
        $videoY = Video::create(['title' => 'cd']);
        $videoZ = Video::create(['title' => 'ef']);

        $tagX->posts()->sync([$postX->getKey(), $postY->getKey(), $postZ->getKey()]);
        $tagY->posts()->sync([$postY->getKey(), $postZ->getKey()]);
        $tagZ->posts()->sync([$postZ->getKey()]);

        $tagX->videos()->sync([$videoX->getKey(), $videoY->getKey(), $videoZ->getKey()]);
        $tagY->videos()->sync([$videoX->getKey(), $videoY->getKey()]);
        $tagZ->videos()->sync([$videoX->getKey()]);

        $this->assertEquals([$postX->getKey(), $postY->getKey(), $postZ->getKey()], $tagX->posts->pluck($postX->getKeyName())->toArray());
        $this->assertEquals([$postY->getKey(), $postZ->getKey()], $tagY->posts->pluck($postX->getKeyName())->toArray());
        $this->assertEquals([$postZ->getKey()], $tagZ->posts->pluck($postX->getKeyName())->toArray());

        $this->assertEquals([$videoX->getKey(), $videoY->getKey(), $videoZ->getKey()], $tagX->videos->pluck($videoX->getKeyName())->toArray());
        $this->assertEquals([$videoX->getKey(), $videoY->getKey()], $tagY->videos->pluck($videoX->getKeyName())->toArray());
        $this->assertEquals([$videoX->getKey()], $tagZ->videos->pluck($videoX->getKeyName())->toArray());
    }
}
