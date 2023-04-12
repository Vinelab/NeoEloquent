<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    protected $table = 'Comment';

    protected $fillable = ['text'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'text';

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function post(): MorphOne
    {
        return $this->morphOne(Post::class, 'postable');
    }

    public function video(): MorphOne
    {
        return $this->morphOne(Video::class, 'videoable');
    }
}
