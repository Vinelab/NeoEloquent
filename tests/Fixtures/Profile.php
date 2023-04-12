<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'Profile';

    protected $fillable = ['guid', 'service'];

    protected $primaryKey = 'guid';

    protected $keyType = 'string';
}
