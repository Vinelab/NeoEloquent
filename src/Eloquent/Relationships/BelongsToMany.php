<?php

namespace Vinelab\NeoEloquent\Eloquent\Relationships;

use Vinelab\NeoEloquent\Eloquent\HasHardRelationship;

class BelongsToMany extends \Illuminate\Database\Eloquent\Relations\BelongsToMany
{
    use HasHardRelationship;
}