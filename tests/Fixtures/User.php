<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class User extends Model
{
    protected $table = 'Individual';

    protected $fillable = ['name', 'alias', 'logins', 'points', 'email', 'uuid', 'calls', 'dob'];

    protected $primaryKey = 'name';

    public $incrementing = false;

    protected $keyType = 'string';

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function firstInRole(): HasOneThrough
    {
        return $this->hasOneThrough(Role::class, User::class);
    }

    public function sameRoles(): HasManyThrough
    {
        return $this->hasManyThrough(Role::class, User::class);
    }

    public function posts(): MorphToMany
    {
        return $this->morphToMany(Post::class, 'postable');
    }


    public function morphToLocation(): MorphTo
    {
        return $this->morphTo(Location::class, 'locatable');
    }

    public function colleagues(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
