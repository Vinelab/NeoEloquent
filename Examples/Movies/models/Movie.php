<?php

use Vinelab\NeoEloquent\Eloquent\Model as NeoEloquent;

class Movie extends NeoEloquent
{
    // the attribute that are allowed on this model (for mass assignment)
    // @see: http://laravel.com/docs/4.2/eloquent#mass-assignment
    protected $fillable = ['title', 'year'];

    public function actors()
    {
        return $this->belongsToMany('Actor', 'ACTS_IN');
    }
}
