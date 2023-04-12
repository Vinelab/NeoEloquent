<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $table = 'Permission';
    protected $fillable = ['title', 'alias'];
    protected $primaryKey = 'title';
    protected $keyType = 'string';
    public $incrementing = false;

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
}