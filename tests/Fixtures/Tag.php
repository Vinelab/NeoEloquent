<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Vinelab\NeoEloquent\Tests\Fixtures\Post;
use Vinelab\NeoEloquent\Tests\Fixtures\Video;

class Tag extends Model
{
    protected $table = 'Tag';
    protected $fillable = ['title'];
    protected $primaryKey = 'title';
    public $incrementing = false;
    protected $keyType = 'string';

    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }

    public function videos(): MorphToMany
    {
        return $this->morphedByMany(Video::class, 'taggable');
    }
}