<?php

namespace Vinelab\NeoEloquent\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model
{
    protected $table = 'Author';

    protected $fillable = ['name'];

    public $incrementing = false;

    protected $primaryKey = 'name';

    protected $keyType = 'string';

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'WROTE');
    }
}