<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Vinelab\NeoEloquent\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;

use function usort;

class User extends Model
{
    protected $table = 'Individual';

    protected $fillable = ['name', 'email', 'alias', 'calls'];

    protected $primaryKey = 'name';

    protected $keyType = 'string';
}

class WheresTheTest extends TestCase
{
    private User $ab;
    private User $cd;
    private User $ef;
    private User $gh;
    private User $ij;

    public function setUp(): void
    {
        parent::setUp();

        (new User())->getConnection()->getPdo()->run('MATCH (x) DETACH DELETE x');

        // Setup the data in the database
        $this->ab = User::create([
            'name'  => 'Ey Bee',
            'alias' => 'ab',
            'email' => 'ab@alpha.bet',
            'calls' => 10,
        ]);

        $this->cd = User::create([
            'name'  => 'See Dee',
            'alias' => 'cd',
            'email' => 'cd@alpha.bet',
            'calls' => 20,
        ]);

        $this->ef = User::create([
            'name'  => 'Eee Eff',
            'alias' => 'ef',
            'email' => 'ef@alpha.bet',
            'calls' => 30,
        ]);

        $this->gh = User::create([
            'name'  => 'Gee Aych',
            'alias' => 'gh',
            'email' => 'gh@alpha.bet',
            'calls' => 40,
        ]);

        $this->ij = User::create([
            'name'  => 'Eye Jay',
            'alias' => 'ij',
            'email' => 'ij@alpha.bet',
            'calls' => 50,
        ]);
    }

    public function testWhereIdWithNoOperator()
    {
        $u = User::where('name', $this->ab->getKey())->first();

        $this->assertEquals($this->ab->toArray(), $u->toArray());
    }

    public function testWhereIdSelectingProperties()
    {
        $u = User::where('name', $this->ab->getKey())->first(['name', 'email']);

        $this->assertEquals($this->ab->getKey(), $u->getKey());
        $this->assertEquals($this->ab->name, $u->name);
        $this->assertEquals($this->ab->email, $u->email);
    }

    public function testWhereIdWithEqualsOperator()
    {
        $u = User::where('name', '=', $this->cd->getKey())->first();

        $this->assertEquals($this->cd->toArray(), $u->toArray());
    }

    public function testWherePropertyWithoutOperator()
    {
        $u = User::where('alias', 'ab')->first();

        $this->assertEquals($this->ab->toArray(), $u->toArray());
    }

    public function testWherePropertyEqualsOperator()
    {
        $u = User::where('alias', '=', 'ab')->first();

        $this->assertEquals($this->ab->toArray(), $u->toArray());
    }

    public function testWhereGreaterThanOperator()
    {
        $u = User::where('calls', '>', 10)->first();
        $this->assertEquals($this->cd->toArray(), $u->toArray());

        $others = User::where('calls', '>', 10)->get();
        $this->assertCount(4, $others);

        $brothers = [
            $this->cd->toArray(),
            $this->ef->toArray(),
            $this->gh->toArray(),
            $this->ij->toArray(),
        ];
        $this->assertEquals($brothers, $others->toArray());

        $lastTwo = User::where('calls', '>=', 40)->get();
        $this->assertCount(2, $lastTwo);

        $mothers = [$this->gh->toArray(), $this->ij->toArray()];
        $this->assertEquals($mothers, $lastTwo->toArray());

        $none = User::where('calls', '>', 9000)->get();
        $this->assertCount(0, $none);
    }

    public function testWhereLessThanOperator()
    {
        $u = User::where('calls', '<', 10)->get();
        $this->assertCount(0, $u);

        $ab = User::where('calls', '<', 20)->first();
        $this->assertEquals($this->ab->toArray(), $ab->toArray());

        $three = User::where('calls', '<=', 30)->get();
        $this->assertCount(3, $three);

        $cocoa = [
            $this->ab->toArray(),
            $this->cd->toArray(),
            $this->ef->toArray(),
        ];
        $this->assertEquals($cocoa, $three->toArray());

        $below = User::where('calls', '<', -100)->get();
        $this->assertCount(0, $below);

        $nil = User::where('calls', '<=', 0)->first();
        $this->assertNull($nil);
    }

    public function testWhereDifferentThanOperator()
    {
        $notab = User::where('alias', '<>', 'ab')->get();

        $dudes = [
            $this->cd->toArray(),
            $this->ef->toArray(),
            $this->gh->toArray(),
            $this->ij->toArray(),
        ];

        $this->assertCount(4, $notab);
        $this->assertEquals($dudes, $notab->toArray());
    }

    public function testWhereIn()
    {
        $alpha = User::whereIn('alias', ['ab', 'cd', 'ef', 'gh', 'ij'])->get();

        $crocodile = [
            $this->ab->toArray(),
            $this->cd->toArray(),
            $this->ef->toArray(),
            $this->gh->toArray(),
            $this->ij->toArray(),
        ];

        $this->assertEquals($crocodile, $alpha->toArray());
    }

    public function testWhereNotNull()
    {
        $alpha = User::whereNotNull('alias')->get();

        $crocodile = [
            $this->ab->toArray(),
            $this->cd->toArray(),
            $this->ef->toArray(),
            $this->gh->toArray(),
            $this->ij->toArray(),
        ];

        $this->assertEquals($alpha->toArray(), $crocodile);
    }

    public function testWhereNull()
    {
        $u = User::whereNull('calls')->get();
        $this->assertCount(0, $u);
    }

    public function testWhereNotIn()
    {
        /*
         * There is no WHERE NOT IN [ids] in Neo4j, it should be something like this:
         *
         * MATCH (actor:Actor {name:"Tom Hanks"} )-[:ACTED_IN]->(movies)<-[:ACTED_IN]-(coactor)
         * WITH collect(distinct coactor) as coactors
         * MATCH (actor:Actor)
         * WHERE actor NOT IN coactors
         * RETURN actor
         */
        $u     = User::whereNotIn('alias', ['ab', 'cd', 'ef'])->get();
        $still = [$this->gh->toArray(), $this->ij->toArray()];

        $this->assertCount(2, $u);
        $this->assertEquals($still, $u->toArray());
    }

    public function testWhereBetween()
    {
        $u = User::whereBetween('name', [$this->ab->getKey(), $this->ij->getKey()])->get();

        $mwahaha = [
            $this->ab->toArray(),
            $this->ij->toArray(),
        ];
        $this->assertCount(2, $u);
        $this->assertEquals($mwahaha, $u->toArray());
    }

    public function testOrWhere()
    {
        $buddies = User::where('name', 'Ey Bee')
                       ->orWhere('alias', 'cd')
                       ->orWhere('email', 'ef@alpha.bet')
                       ->orWhere('name', $this->gh->getKey())
                       ->orWhere('calls', '>', 40)
                       ->get();

        $this->assertCount(5, $buddies);
        $bigBrothers = [
            $this->ab->toArray(),
            $this->cd->toArray(),
            $this->ef->toArray(),
            $this->gh->toArray(),
            $this->ij->toArray(),
        ];

        $this->assertEquals($bigBrothers, $buddies->toArray());
    }

    public function testOrWhereIn()
    {
        $all = User::whereIn('name', [$this->ab->getKey(), $this->cd->getKey()])
                   ->orWhereIn('alias', ['ef', 'gh', 'ij'])->get();

        $padrougas = new Collection(array(
            $this->ab,
            $this->cd,
            $this->ef,
            $this->gh,
            $this->ij,
        ));
        $array     = $all->toArray();
        usort($array, static fn(array $x, array $y) => $x['name'] <=> $y['name']);
        $padrougasArray = $padrougas->toArray();
        usort($padrougasArray, static fn(array $x, array $y) => $x['name'] <=> $y['name']);
        $this->assertEquals($array, $padrougasArray);
    }

    public function testWhereNotFound()
    {
        $u = User::where('name', '<', 1)->get();
        $this->assertCount(0, $u);

        $u2 = User::where('glasses', 'always on')->first();
        $this->assertNull($u2);
    }

    /**
     * Regression test for issue #19.
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

    /**
     * Regression test for issue #41.
     *
     * @see https://github.com/Vinelab/NeoEloquent/issues/41
     */
    public function testWhereWithIn()
    {
        $ab = User::where('alias', 'IN', ['ab'])->first();

        $this->assertEquals($this->ab->toArray(), $ab->toArray());

        $users = User::where('alias', 'IN', ['cd', 'ef'])->get();

        $l = (new User())->getConnection()->getQueryLog();

        $this->assertEquals($this->cd->toArray(), $users[0]->toArray());
        $this->assertEquals($this->ef->toArray(), $users[1]->toArray());
    }
}
