<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Post extends Model
{
    protected $table = 'Post';

    protected $fillable = ['title', 'body'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'title';

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function postable(): MorphTo
    {
        return $this->morphTo();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

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
}
