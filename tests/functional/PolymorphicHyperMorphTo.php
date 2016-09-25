<?php namespace Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;

class PolymorphicHyperMorphToTest extends TestCase {

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));

        User::setConnectionResolver($resolver);
        Post::setConnectionResolver($resolver);
        Video::setConnectionResolver($resolver);
        Comment::setConnectionResolver($resolver);
    }

    public function testCreatingUserCommentOnPostAndVideo()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        // Comment on post and video
        $postComment = $postCommentor->comments($post)->create(['text' => 'Please soooooon!']);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $postComment);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $postComment->left());
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $postComment->right());
        $this->assertTrue($postComment->exists());

        $videoComment = $videoCommentor->comments($video)->create(['text' => 'Haha, hilarious shit!']);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $videoComment);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $videoComment->left());
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $videoComment->right());
        $this->assertTrue($videoComment->exists());

        $this->assertNotEquals($postComment, $videoComment);
    }

    public function testSavingUserCommentOnPostAndVideo()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor  = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);

        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        $commentOnPost  = new Comment(['title' => 'Another Place', 'body' => 'To Go..']);
        $commentOnVideo = new Comment(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Comment on post and video
        $postComment = $postCommentor->comments($post)->save($commentOnPost);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $postComment);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $postComment->left());
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $postComment->right());
        $this->assertTrue($postComment->exists());

        $videoComment = $videoCommentor->comments($video)->save($commentOnVideo);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $videoComment);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $videoComment->left());
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $videoComment->right());
        $this->assertTrue($videoComment->exists());

        $this->assertNotEquals($postComment, $videoComment);
    }

    public function testAttachingById()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor  = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);

        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        $commentOnPost  = Comment::create(['text' => 'Another Place']);
        $commentOnVideo = Comment::create(['text' => 'When We Meet']);
        // Comment on post and video
        $postComment = $postCommentor->comments($post)->attach($commentOnPost->id);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $postComment);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $postComment->left());
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $postComment->right());
        $this->assertTrue($postComment->exists());

        $videoComment = $videoCommentor->comments($video)->attach($commentOnVideo->id);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $videoComment);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $videoComment->left());
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $videoComment->right());
        $this->assertTrue($videoComment->exists());

        $this->assertNotEquals($postComment, $videoComment);
    }

    public function testAttachingManyIds()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor  = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);

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

        // Comment on post and video
        $postComments = $postCommentor->comments($post)->attach([$commentOnPost->id, $anotherCommentOnPost->id]);
        foreach ($postComments as $comment)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $comment);
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $comment->left());
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $comment->right());
            $this->assertTrue($comment->exists());
        }

        $videoComments = $videoCommentor->comments($video)->attach([$commentOnVideo->id, $anotherCommentOnVideo->id]);
        foreach ($videoComments as $comment)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $comment);
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $comment->left());
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $comment->right());
            $this->assertTrue($comment->exists());
        }

        $this->assertNotEquals($postComments, $videoComments);
    }

    public function testAttachingModelInstance()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        // Comment on post and video
        $commentOnPost  = Comment::create(['text' => 'Please soooooon!']);
        $commentOnVideo = Comment::create(['text' => 'Balalaika Sings']);

        $postComment = $postCommentor->comments($post)->attach($commentOnPost);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $postComment);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $postComment->left());
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $postComment->right());
        $this->assertTrue($postComment->exists());

        $videoComment = $videoCommentor->comments($video)->attach($commentOnVideo);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $videoComment);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $videoComment->left());
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $videoComment->right());
        $this->assertTrue($videoComment->exists());

        $this->assertNotEquals($postComment, $videoComment);
    }

    public function testAttachingManyModelInstances()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor  = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);

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

        // Comment on post and video
        $postComments = $postCommentor->comments($post)->attach([$commentOnPost, $anotherCommentOnPost]);
        foreach ($postComments as $comment)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $comment);
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $comment->left());
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $comment->right());
            $this->assertTrue($comment->exists());
        }

        $videoComments = $videoCommentor->comments($video)->attach([$commentOnVideo, $anotherCommentOnVideo]);
        foreach ($videoComments as $comment)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $comment);
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $comment->left());
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $comment->right());
            $this->assertTrue($comment->exists());
        }

        $this->assertNotEquals($postComments, $videoComments);
    }

    /**
     * @expectedException Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function testAttachingNonExistingModelIds()
    {
        $user = User::create(['name' => 'Hmm...']);
        $user->posts()->create(['title' => 'A little posty post.']);
        $post = $user->posts()->first();

        $user->comments($post)->attach(9999999999);
    }

    public function testDetachingModelById()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        // Comment on post and video
        $commentOnPost  = Comment::create(['text' => 'Please soooooon!']);
        $commentOnVideo = Comment::create(['text' => 'Balalaika Sings']);

        $postComment = $postCommentor->comments($post)->attach($commentOnPost);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $postComment);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $postComment->left());
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $postComment->right());
        $this->assertTrue($postComment->exists());

        $videoComment = $videoCommentor->comments($video)->attach($commentOnVideo);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\HyperEdge', $videoComment);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $videoComment->left());
        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $videoComment->right());
        $this->assertTrue($videoComment->exists());

        $this->assertNotEquals($postComment, $videoComment);

        $edges = $postCommentor->comments($post)->edges();
        $this->assertNotEmpty($edges);

        $this->assertTrue($postCommentor->comments($post)->detach($commentOnPost));

        $edges = $postCommentor->comments($post)->edges();
        $this->assertEmpty($edges);
    }

    public function testSyncingModelIds()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        // Comment on post and video
        $commentOnPost  = Comment::create(['text' => 'Please soooooon!']);
        $anotherCommentOnPost = Comment::create(['text' => 'Balalaika Sings']);

        $postComment = $postCommentor->comments($post)->attach($commentOnPost);

        $user->comments($post)->sync([$anotherCommentOnPost->id]);

        $edges = $user->comments($post)->edges();

        $edgesIds = array_map(function($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());
        $this->assertTrue(in_array($anotherCommentOnPost->id, $edgesIds));
        $this->assertFalse(in_array($commentOnPost->id, $edgesIds));

        foreach ($edges as $edge) { $edge->delete(); }
    }

    public function testSyncingUpdatesModels()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        // Comment on post and video
        $commentOnPost  = Comment::create(['text' => 'Please soooooon!']);
        $anotherCommentOnPost = Comment::create(['text' => 'Balalaika Sings']);

        $postComment = $postCommentor->comments($post)->attach($commentOnPost);

        $user->comments($post)->sync([$commentOnPost->id, $anotherCommentOnPost->id]);

        $edges = $user->comments($post)->edges();

        $edgesIds = array_map(function($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());
        $this->assertTrue(in_array($anotherCommentOnPost->id, $edgesIds));
        $this->assertTrue(in_array($commentOnPost->id, $edgesIds));

        foreach ($edges as $edge) { $edge->delete(); }
    }

    public function testSyncingWithAttributes()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        // Comment on post and video
        $commentOnPost  = Comment::create(['text' => 'Please soooooon!']);
        $anotherCommentOnPost = Comment::create(['text' => 'Balalaika Sings']);

        $postComment = $postCommentor->comments($post)->attach($commentOnPost);

        $user->comments($post)->sync([
            $anotherCommentOnPost->id => ['feeling' => 'sad'],
            $commentOnPost->id => ['feeling' => 'happy'],
        ]);

        $edges = $user->comments($post)->edges();

        $edgesIds = array_map(function($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());
        $this->assertTrue(in_array($anotherCommentOnPost->id, $edgesIds));
        $this->assertTrue(in_array($commentOnPost->id, $edgesIds));

        $expectedEdgesTypes = ['happy', 'sad'];

        foreach ($edges as $key => $edge)
        {
            $attributes = $edge->toArray();
            $this->assertArrayHasKey('feeling', $attributes);
            $this->assertEquals($expectedEdgesTypes[$key], $edge->feeling);
            $edge->delete();
        }
    }

    public function testDynamicLoadingMorphedModel()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        $commentOnPost  = Comment::create(['text' => 'Please soooooon!']);
        $postComment = $postCommentor->comments($post)->attach($commentOnPost);

        $commentOnVideo = new Comment(['title' => 'When We Meet', 'url' => 'http://some.url']);
        $videoComment = $videoCommentor->comments($video)->attach($commentOnVideo);

        $post = Post::find($post->id);
        foreach ($post->comments as $comment)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Comment', $comment);
            $this->assertTrue($comment->exists);
            $this->assertGreaterThanOrEqual(0, $comment->id);
            $this->assertEquals($commentOnPost->toArray(), $comment->toArray());
        }

        $video = Video::find($video->id);
        foreach ($video->comments as $comment)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Comment', $comment);
            $this->assertTrue($comment->exists);
            $this->assertGreaterThanOrEqual(0, $comment->id);
            $this->assertEquals($commentOnVideo->toArray(), $comment->toArray());
        }
    }

    public function testEagerLoadingMorphedModel()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        $commentOnPost  = Comment::create(['text' => 'Please soooooon!']);
        $postComment = $postCommentor->comments($post)->attach($commentOnPost);

        $post = Post::with('comments')->find($post->id);
        $postRelations = $post->getRelations();
        $this->assertArrayHasKey('comments', $postRelations);
        $this->assertCount(1, $postRelations['comments']);
        foreach ($postRelations['comments'] as $comment)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Comment', $comment);
            $this->assertTrue($comment->exists);
            $this->assertGreaterThanOrEqual(0, $comment->id);
            $this->assertEquals($commentOnPost->toArray(), $comment->toArray());
        }

        $commentOnVideo = new Comment(['title' => 'When We Meet', 'url' => 'http://some.url']);
        $videoComment = $videoCommentor->comments($video)->attach($commentOnVideo);

        $video = Video::with('comments')->find($video->id);
        $videoRelations = $video->getRelations();
        $this->assertArrayHasKey('comments', $videoRelations);
        $this->assertCount(1, $videoRelations['comments']);
        foreach ($videoRelations['comments'] as $comment)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Comment', $comment);
            $this->assertTrue($comment->exists);
            $this->assertGreaterThanOrEqual(0, $comment->id);
            $this->assertEquals($commentOnVideo->toArray(), $comment->toArray());
        }
    }

    public function testDynamicLoadingMorphingModels()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        $commentOnPost  = Comment::create(['text' => 'Please soooooon!']);
        $postComment = $postCommentor->comments($post)->attach($commentOnPost);

        $commentOnVideo = new Comment(['title' => 'When We Meet', 'url' => 'http://some.url']);
        $videoComment = $videoCommentor->comments($video)->attach($commentOnVideo);

        $comments = $postCommentor->comments;
        $this->assertEquals($commentOnPost->toArray(), $comments->first()->toArray());

        $comments = $videoCommentor->comments;
        $this->assertEquals($commentOnVideo->toArray(), $comments->first()->toArray());
    }

    public function testEagerLoadingMorphingModels()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        // Attach and assert the comment on Post
        $commentOnPost  = Comment::create(['text' => 'Please soooooon!']);
        $postComment = $postCommentor->comments($post)->attach($commentOnPost);

        $userMorph = User::with('comments')->find($postCommentor->id);
        $userRelations = $userMorph->getRelations();
        $this->assertArrayHasKey('comments', $userRelations);
        $this->assertCount(1, $userRelations['comments']);
        $this->assertEquals($commentOnPost->toArray(), $userRelations['comments']->first()->toArray());

        // Attach and assert the comment on Video
        $commentOnVideo = new Comment(['title' => 'When We Meet', 'url' => 'http://some.url']);
        $videoComment = $videoCommentor->comments($video)->attach($commentOnVideo);

        $vUserMorph = User::with('comments')->find($videoCommentor->id);
        $vUserRelations = $vUserMorph->getRelations();
        $this->assertArrayHasKey('comments', $vUserRelations);
        $this->assertCount(1, $userRelations['comments']);
        $this->assertEquals($commentOnVideo->toArray(), $vUserRelations['comments']->first()->toArray());
    }

    public function testDynamicLoadingMorphedByModel()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        $commentOnPost  = Comment::create(['text' => 'Please soooooon!']);
        $postComment = $postCommentor->comments($post)->attach($commentOnPost);

        $postMorph = $commentOnPost->post;
        $this->assertTrue($postMorph->exists);
        $this->assertGreaterThanOrEqual(0, $postMorph->id);
        $this->assertEquals($post->toArray(), $postMorph->toArray());

        $commentOnVideo = new Comment(['title' => 'When We Meet', 'url' => 'http://some.url']);
        $videoComment = $videoCommentor->comments($video)->attach($commentOnVideo);

        $videoMorph = $commentOnVideo->video;
        $this->assertTrue($videoMorph->exists);
        $this->assertGreaterThanOrEqual(0, $videoMorph->id);
        $this->assertEquals($video->toArray(), $videoMorph->toArray());
    }

    public function testEagerLoadingMorphedByModel()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        // Check the post of this comment
        $commentOnPost  = Comment::create(['text' => 'Please soooooon!']);
        $postComment = $postCommentor->comments($post)->attach($commentOnPost);

        $morphedComment = Comment::with('post')->find($commentOnPost->id);
        $morphedCommentRelations = $morphedComment->getRelations();
        $this->assertArrayHasKey('post', $morphedCommentRelations);
        $this->assertEquals($post->toArray(), $morphedCommentRelations['post']->toArray());

        // Check the video of this comment
        $commentOnVideo = new Comment(['title' => 'When We Meet', 'url' => 'http://some.url']);
        $videoComment = $videoCommentor->comments($video)->attach($commentOnVideo);

        $vMorphedComment = Comment::with('video')->find($commentOnVideo->id);
        $vMorphedCommentRelations = $vMorphedComment->getRelations();
        $this->assertArrayHasKey('video', $vMorphedCommentRelations);
        $this->assertEquals($video->toArray(), $vMorphedCommentRelations['video']->toArray());

    }

    public function testDynamicLoadingMorphToModel()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        // Check the post of this comment
        $commentOnPost  = Comment::create(['text' => 'Please soooooon!']);
        $postComment = $postCommentor->comments($post)->attach($commentOnPost);

        $commentablePost = $commentOnPost->commentable;

        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Post', $commentablePost);
        $this->assertEquals($post->toArray(), $commentablePost->toArray());

        // Check the video of this comment
        $commentOnVideo = new Comment(['title' => 'When We Meet', 'url' => 'http://some.url']);
        $videoComment = $videoCommentor->comments($video)->attach($commentOnVideo);

        $commentableVideo = $commentOnVideo->commentable;
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Video', $commentableVideo);
        $this->assertEquals($video->toArray(), $commentableVideo->toArray());
    }

    public function testEagerLoadingMorphToModel()
    {
        $user = User::create(['name' => 'Hmm...']);
        $postCommentor = User::create(['name' => 'I Comment On Posts']);
        $videoCommentor = User::create(['name' => 'I Comment On Videos']);
        // create the user's post and video
        $user->posts()->create(['title' => 'Another Place', 'body' => 'To Go..']);
        $user->videos()->create(['title' => 'When We Meet', 'url' => 'http://some.url']);
        // Grab them back
        $post  = $user->posts->first();
        $video = $user->videos->first();

        // Check the post of this comment
        $commentOnPost  = Comment::create(['text' => 'Please soooooon!']);
        $postComment = $postCommentor->comments($post)->attach($commentOnPost);

        $morphedPostComment = Comment::with('commentable')->find($commentOnPost->id);
        $morphedCommentRelations = $morphedPostComment->getRelations();

        $this->assertArrayHasKey('commentable', $morphedCommentRelations);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Post', $morphedCommentRelations['commentable']);
        $this->assertEquals($post->toArray(), $morphedCommentRelations['commentable']->toArray());

        // // Check the video of this comment
        $commentOnVideo = new Comment(['title' => 'When We Meet', 'url' => 'http://some.url']);
        $videoComment = $videoCommentor->comments($video)->attach($commentOnVideo);

        $morphedVideoComment = Comment::with('commentable')->find($commentOnVideo->id);
        $morphedVideoCommentRelations = $morphedVideoComment->getRelations();

        $this->assertArrayHasKey('commentable', $morphedVideoCommentRelations);
        $this->assertInstanceOf('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Video', $morphedVideoCommentRelations['commentable']);
        $this->assertEquals($video->toArray(), $morphedVideoCommentRelations['commentable']->toArray());
    }

}

class User extends Model {

    protected $label = 'User';

    protected $fillable = ['name'];

    public function comments($model = null)
    {
        return $this->hyperMorph($model, 'Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Comment', 'COMMENTED', 'ON');
    }

    public function posts()
    {
        return $this->hasMany('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Post', 'POSTED');
    }

    public function videos()
    {
        return $this->hasMany('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Video', 'UPLOADED');
    }
}

class Post extends Model {

    protected $label = 'Post';

    protected $fillable = ['title', 'body'];

    public function comments()
    {
        return $this->morphMany('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Comment', 'ON');
    }
}

class Video extends Model {

    protected $label = 'Video';

    protected $fillable = ['title', 'url'];

    public function comments()
    {
        return $this->morphMany('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Comment', 'ON');
    }
}

class Comment extends Model {

    protected $label = 'Comment';

    protected $fillable = ['text'];

    public function commentable()
    {
        return $this->morphTo();
    }

    public function post()
    {
        return $this->morphTo('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Post', 'ON');
    }

    public function video()
    {
        return $this->morphTo('Vinelab\NeoEloquent\Tests\Functional\Relations\HyperMorphTo\Video', 'ON');
    }
}


