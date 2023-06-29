<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Location extends Model
{
    protected $table = 'Location';

    protected $primaryKey = 'lat';

    protected $fillable = ['lat', 'long', 'country', 'city'];

    public function locatable(): MorphTo
    {
        return $this->morphTo();
    }
}
