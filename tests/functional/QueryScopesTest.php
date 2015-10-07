<?php namespace Vinelab\NeoEloquent\Tests\Functional;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;

class Misfit extends Model {

    protected $label = 'Misfit';

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

class QueryScopesTest extends TestCase {

    public function tearDown()
    {
        M::close();

        $all = Misfit::all();
        $all->each(function($u) { $u->delete(); });

        parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));
        Misfit::setConnectionResolver($resolver);

        $this->t = Misfit::create([
            'name'  => 'Nikola Tesla',
            'alias' => 'tesla'
        ]);

        $this->e = misfit::create([
            'name'  => 'Thomas Edison',
            'alias' => 'edison'
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
