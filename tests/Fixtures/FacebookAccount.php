<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class FacebookAccount extends Model
{
    protected $table = 'SocialAccount';
    protected $fillable = ['gender', 'age', 'interest'];
    public $incrementing = false;
    protected $primaryKey = 'id';
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::saving(function (Model $m) {
            $m->id = Uuid::getFactory()->uuid4()->toString();
        });
    }
}