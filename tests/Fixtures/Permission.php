<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $table = 'Location';

    protected $primaryKey = 'alias';

    protected $fillable = ['title', 'alias'];

    public function relatedRoles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, '<HAS_PERMISSION');
    }
}