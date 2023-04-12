<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    protected $table = 'Organization';
    protected $fillable = ['name'];
    public $incrementing = false;
    protected $keyType = 'string';
    protected $primaryKey = 'name';

    public function members(): HasMany
    {
        return $this->hasMany(User::class);
    }
}