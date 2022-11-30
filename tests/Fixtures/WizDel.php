<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WizDel extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $table = 'Wiz';

    protected $fillable = ['fiz', 'biz', 'triz'];

    protected $primaryKey = 'fiz';

    protected $keyType = 'string';

    public $incrementing = false;
}