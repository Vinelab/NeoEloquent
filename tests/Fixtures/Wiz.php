<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Wiz extends Model
{
    protected $table = 'SOmet';

    protected $fillable = ['fiz', 'biz', 'triz'];

    protected $primaryKey = 'fiz';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = true;
}