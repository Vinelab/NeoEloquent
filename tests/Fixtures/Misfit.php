<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Misfit extends Model
{
    protected $table = 'Misfit';

    public $incrementing = false;

    protected $primaryKey = 'name';

    protected $keyType = 'string';

    protected $fillable = ['name', 'alias'];

    public function scopeKingOfScience($query)
    {
        return $query->where('alias', 'tesla');
    }

    public function scopeStupidDickhead($query)
    {
        return $query->where('alias', 'edison');
    }
}