<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Database\Eloquent\Model;
use Vinelab\NeoEloquent\Tests\TestCase;

class Misfit extends Model
{
    protected $table = 'Misfit';

    public $incrementing = false;

    protected $primaryKey = 'name';

    protected $keyType = 'string';

    protected $fillable = ['name', 'alias'];

    public function scopeKingOfScience($query)
    {
        return $query->where('alias', 'tesla');
    }

    public function scopeStupidDickhead($query)
    {
        return $query->where('alias', 'edison');
    }
}

class QueryScopesTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        (new Misfit())->getConnection()->getPdo()->run('MATCH (x) DETACH DELETE x');

        $this->t = Misfit::create([
            'name' => 'Nikola Tesla',
            'alias' => 'tesla',
        ]);

        $this->e = Misfit::create([
            'name' => 'Thomas Edison',
            'alias' => 'edison',
        ]);
    }

    public function testQueryScopes()
    {
        $t = Misfit::kingOfScience()->first();
        $this->assertEquals($this->t->toArray(), $t->toArray());

        $e = Misfit::stupidDickhead()->first();
        $this->assertEquals($this->e->toArray(), $e->toArray());
    }
}
