<?php

namespace Vinelab\NeoEloquent\Eloquent\Relationships;

use Vinelab\NeoEloquent\Eloquent\HasHardRelationship;

class HasOneThrough extends \Illuminate\Database\Eloquent\Relations\HasOneThrough
{
    use HasHardRelationship;
}