<?php

use Vinelab\NeoEloquent\Eloquent\Model as NeoEloquent;

class Actor extends NeoEloquent
{
    // the attribute that are allowed on this model (for mass assignment)
    // @see: http://laravel.com/docs/4.2/eloquent#mass-assignment
    protected $fillable = ['name'];

    public function movies()
    {
        return $this->hasMany('Movie', 'ACTS_IN');
    }
}
