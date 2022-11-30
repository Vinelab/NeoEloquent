<?php

namespace Vinelab\NeoEloquent\Eloquent\Relationships;

use Vinelab\NeoEloquent\Eloquent\HasHardRelationship;

class HasManyThrough extends \Illuminate\Database\Eloquent\Relations\HasManyThrough
{
    use HasHardRelationship;
}