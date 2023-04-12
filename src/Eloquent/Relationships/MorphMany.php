<?php

namespace Vinelab\NeoEloquent\Eloquent\Relationships;

use Vinelab\NeoEloquent\Eloquent\HasHardRelationship;

class MorphMany extends \Illuminate\Database\Eloquent\Relations\MorphMany
{
    use HasHardRelationship;
}
