<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    protected $table = 'Photo';
    protected $fillable = ['url', 'caption', 'metadata'];
    protected $primaryKey = 'url';
    public $incrementing = false;
    protected $keyType = 'string';
}