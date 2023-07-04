<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'Post';

    protected $fillable = ['title', 'body'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'title';
}
