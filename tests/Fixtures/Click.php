<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    protected $table = 'Click';

    protected $fillable = ['num'];

    protected $keyType = 'string';

    public $incrementing = false;

    protected $primaryKey = 'num';
}