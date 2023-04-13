<?php

namespace Vinelab\NeoEloquent\Relations;

use Vinelab\NeoEloquent\Eloquent\HasHardRelationship;

class BelongsToMany extends \Illuminate\Database\Eloquent\Relations\BelongsToMany
{
    use HasHardRelationship;
}
