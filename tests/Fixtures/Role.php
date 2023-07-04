<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $table = 'Role';

    protected $fillable = ['title'];

    protected $primaryKey = 'title';

    protected $keyType = 'string';

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    public function relatedUser(): BelongsToMany
    {
        return $this->belongsToMany(User::class, '<HAS_ROLE');
    }

    public function relatedPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'HAS_PERMISSION>');
    }
}
