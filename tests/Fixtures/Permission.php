<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = 'Location';

    protected $primaryKey = 'alias';

    protected $fillable = ['title', 'alias'];
}