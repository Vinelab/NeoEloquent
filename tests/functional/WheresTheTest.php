<?php namespace Vinelab\NeoEloquent\Tests\Functional;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;

class User extends Model {

    protected $label = 'Individual';

    protected $fillable = ['name', 'email', 'alias', 'calls'];
}

class WheresTheTest extends TestCase {

    public function tearDown()
    {
        M::close();

        $all = User::all();
        $all->each(function($u) { $u->delete(); });

        parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));
        User::setConnectionResolver($resolver);

        // Setup the data in the database
        $this->ab = User::create([
            'name'  => 'Ey Bee',
            'alias' => 'ab',
            'email' => 'ab@alpha.bet',
            'calls' => 10
        ]);

        $this->cd = User::create([
            'name'  => 'See Dee',
            'alias' => 'cd',
            'email' => 'cd@alpha.bet',
            'calls' => 20
        ]);

        $this->ef = User::create([
            'name'  => 'Eee Eff',
            'alias' => 'ef',
            'email' => 'ef@alpha.bet',
            'calls' => 30
        ]);

        $this->gh = User::create([
            'name'  => 'Gee Aych',
            'alias' => 'gh',
            'email' => 'gh@alpha.bet',
            'calls' => 40
        ]);

        $this->ij = User::create([
            'name' => 'Eye Jay',
            'alias' => 'ij',
            'email' => 'ij@alpha.bet',
            'calls' => 50
        ]);

    }

    public function testWhereIdWithNoOperator()
    {
        $u = User::where('id', $this->ab->id)->first();

        $this->assertEquals($this->ab, $u);
    }

    public function testWhereIdSelectingProperties()
    {
        $u = User::where('id', $this->ab->id)->first(['id', 'name', 'email']);

        $this->assertEquals($this->ab->id, $u->id);
        $this->assertEquals($this->ab->name, $u->name);
        $this->assertEquals($this->ab->email, $u->email);
    }

    public function testWhereIdWithEqualsOperator()
    {
        $u = User::where('id', '=', $this->cd->id)->first();

        $this->assertEquals($this->cd, $u);
    }

    public function testWherePropertyWithoutOperator()
    {
        $u = User::where('alias', 'ab')->first();

        $this->assertEquals($this->ab, $u);
    }

    public function testWherePropertyEqualsOperator()
    {
        $u = User::where('alias', '=', 'ab')->first();

        $this->assertEquals($this->ab, $u);
    }

    public function testWhereGreaterThanOperator()
    {
        $u = User::where('calls', '>', 10)->first();
        $this->assertEquals($this->cd, $u);

        $others = User::where('calls', '>', 10)->get();
        $this->assertCount(4, $others);

        $brothers = new \Illuminate\Database\Eloquent\Collection(array(
                                                            $this->cd,
                                                            $this->ef,
                                                            $this->gh,
                                                            $this->ij));
        $this->assertEquals($others, $brothers);

        $lastTwo = User::where('calls', '>=', 40)->get();
        $this->assertCount(2, $lastTwo);

        $mothers = new \Illuminate\Database\Eloquent\Collection(array($this->gh, $this->ij));
        $this->assertEquals($lastTwo, $mothers);

        $none = User::where('calls', '>', 9000)->get();
        $this->assertCount(0, $none);
    }

    public function testWhereLessThanOperator()
    {
        $u = User::where('calls', '<', 10)->get();
        $this->assertCount(0, $u);

        $ab = User::where('calls', '<', 20)->first();
        $this->assertEquals($this->ab, $ab);

        $three = User::where('calls', '<=', 30)->get();
        $this->assertCount(3, $three);

        $cocoa = new \Illuminate\Database\Eloquent\Collection(array($this->ab,
                                                            $this->cd,
                                                            $this->ef));
        $this->assertEquals($cocoa, $three);

        $below = User::where('calls', '<', -100)->get();
        $this->assertCount(0, $below);

        $nil = User::where('calls', '<=', 0)->first();
        $this->assertNull($nil);
    }

    public function testWhereDifferentThanOperator()
    {
        $notab = User::where('alias', '<>', 'ab')->get();

        $dudes = new \Illuminate\Database\Eloquent\Collection(array(
                                                            $this->cd,
                                                            $this->ef,
                                                            $this->gh,
                                                            $this->ij));

        $this->assertCount(4, $notab);
        $this->assertEquals($notab, $dudes);
    }

    public function testWhereIn()
    {
        $alpha = User::whereIn('alias', ['ab', 'cd', 'ef', 'gh', 'ij'])->get();

        $crocodile = new \Illuminate\Database\Eloquent\Collection(array($this->ab,
                                                            $this->cd,
                                                            $this->ef,
                                                            $this->gh,
                                                            $this->ij));

        $this->assertEquals($alpha, $crocodile);
    }

    public function testWhereNotNull()
    {
        $alpha = User::whereNotNull('alias')->get();

        $crocodile = new \Illuminate\Database\Eloquent\Collection(array($this->ab,
                                                            $this->cd,
                                                            $this->ef,
                                                            $this->gh,
                                                            $this->ij));

        $this->assertEquals($alpha, $crocodile);
    }

    public function testWhereNull()
    {
        $u = User::whereNull('calls')->get();
        $this->assertCount(0, $u);
    }

    public function testWhereNotIn()
    {
        $this->markTestIncomplete();

        $u = User::whereNotIn('alias', ['ab', 'cd', 'eg'])->get();
        $still = new \Illuminate\Database\Eloquent\Collection(array($this->gh, $this->ij));

        $this->assertCount(2, $u);
        $this->assertEquals($buddies, $still);
    }

    public function testWhereBetween()
    {
        $this->markTestIncomplete();

        $u = User::whereBetween('id', [$this->ab->id, $this->ij->id])->get();

        $mwahaha = new \Illuminate\Database\Eloquent\Collection(array($this->ab,
                                                            $this->cd,
                                                            $this->ef,
                                                            $this->gh,
                                                            $this->ij));
        $this->assertCount(5, $u);
        $this->assertEquals($buddies, $mwahaha);
    }

    public function testOrWhere()
    {
        $buddies = User::where('name', 'Ey Bee')
            ->orWhere('alias', 'cd')
            ->orWhere('email', 'ef@alpha.bet')
            ->orWhere('id', $this->gh->id)
            ->orWhere('calls', '>', 40)
            ->get();

        $this->assertCount(5, $buddies);
        $bigBrothers = new \Illuminate\Database\Eloquent\Collection(array($this->ab,
                                                            $this->cd,
                                                            $this->ef,
                                                            $this->gh,
                                                            $this->ij));

        $this->assertEquals($buddies, $bigBrothers);
    }

    public function testOrWhereIn()
    {
        $all = User::whereIn('id', [$this->ab->id, $this->cd->id])
            ->orWhereIn('alias', ['ef', 'gh', 'ij'])->get();

        $padrougas = new \Illuminate\Database\Eloquent\Collection(array($this->ab,
                                                            $this->cd,
                                                            $this->ef,
                                                            $this->gh,
                                                            $this->ij));
        $this->assertEquals($all, $padrougas);
    }

    public function testWhereNotFound()
    {
        $u = User::where('id', '<', 1)->get();
        $this->assertCount(0, $u);

        $u2 = User::where('glasses', 'always on')->first();
        $this->assertNull($u2);
    }

    /**
     * Regression test for issue #19
     *
     * @see  https://github.com/Vinelab/NeoEloquent/issues/19
     */
    public function testWhereMultipleValuesForSameColumn()
    {
        $u = User::where('alias', '=', 'ab')->orWhere('alias', '=', 'cd')->get();
        $this->assertCount(2, $u);

        $this->assertEquals('ab', $u[0]->alias);
        $this->assertEquals('cd', $u[1]->alias);
    }
}
