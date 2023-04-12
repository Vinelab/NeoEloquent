<?php

namespace Vinelab\NeoEloquent\Eloquent\Relationships;

use Vinelab\NeoEloquent\Eloquent\HasHardRelationship;

class MorphTo extends \Illuminate\Database\Eloquent\Relations\MorphTo
{
    use HasHardRelationship;
}
