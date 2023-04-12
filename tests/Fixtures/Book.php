<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'Book';

    protected $primaryKey = 'title';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['title', 'pages', 'release_date'];
}
