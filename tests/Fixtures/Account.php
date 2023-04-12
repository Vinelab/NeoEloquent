<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    protected $table = 'Account';

    protected $fillable = ['guid'];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $primaryKey = 'guid';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
