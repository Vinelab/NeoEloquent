<?php

namespace Vinelab\NeoEloquent\Eloquent\Relationships;

use Vinelab\NeoEloquent\Eloquent\HasHardRelationship;

class BelongsTo extends \Illuminate\Database\Eloquent\Relations\BelongsTo
{
    use HasHardRelationship;
}