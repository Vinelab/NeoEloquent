<?php

namespace Vinelab\NeoEloquent\Eloquent\Relationships;

use Vinelab\NeoEloquent\Eloquent\HasHardRelationship;

class HasMany extends \Illuminate\Database\Eloquent\Relations\HasMany
{
    use HasHardRelationship;
}