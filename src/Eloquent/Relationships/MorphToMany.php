<?php

namespace Vinelab\NeoEloquent\Eloquent\Relationships;

use Vinelab\NeoEloquent\Eloquent\HasHardRelationship;

class MorphToMany extends \Illuminate\Database\Eloquent\Relations\MorphToMany
{
    use HasHardRelationship;
}
