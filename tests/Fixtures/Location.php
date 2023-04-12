<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $table = 'Location';

    protected $primaryKey = 'lat';

    protected $fillable = ['lat', 'long', 'country', 'city'];
}
