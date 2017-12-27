<?php namespace Vinelab\NeoEloquent\Tests\Functional\Aggregate;

use Illuminate\Database\Query\Processors\Processor;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Query\Builder;
use Vinelab\NeoEloquent\Query\Grammars\CypherGrammar;
use Vinelab\NeoEloquent\Tests\TestCase;


class AggregateTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->query = new Builder((new User)->getConnection(), new CypherGrammar, new Processor);
        $this->query->from = 'User';
    }

    public function testCount()
    {

        User::create([]);
        $this->assertEquals(1, $this->query->count());
        User::create([]);
        $this->assertEquals(2, $this->query->count());
        User::create([]);
        $this->assertEquals(3, $this->query->count());

        User::create(['logins' => 10]);
        $this->assertEquals(1, $this->query->count('logins'));

        User::create(['points' => 200]);
        $this->assertEquals(1, $this->query->count('points'));
    }

    public function testCountWithQuery()
    {
        User::create(['email' => 'foo@mail.net', 'points' => 2]);
        User::create(['email' => 'bar@mail.net', 'points' => 2]);
        // we need a fresh query every time so that we make sure we're not reusing the same
        // one over and over which ends up with irreliable results.
        $query = new Builder((new User)->getConnection(), new CypherGrammar, new Processor);
        $query->from = 'User';
        $query->where('email', 'foo@mail.net');
        $this->assertEquals(1, $query->count());

        $query = new Builder((new User)->getConnection(), new CypherGrammar, new Processor);
        $query->from = 'User';
        $query->where('email', 'bar@mail.net');
        $this->assertEquals(1, $query->count());

        $query = new Builder((new User)->getConnection(), new CypherGrammar, new Processor);
        $query->from = 'User';
        $query->where('points', 2);
        $this->assertEquals(2, $query->count());
    }

    public function testCountDistinct()
    {
        User::create(['logins' => 1]);
        User::create(['logins' => 2]);
        User::create(['logins' => 2]);
        User::create(['logins' => 3]);
        User::create(['logins' => 3]);
        User::create(['logins' => 4]);
        User::create(['logins' => 4]);

        $this->assertEquals(4, $this->query->countDistinct('logins'));
    }

    public function testCountDistinctWithQuery()
    {
        User::create(['logins' => 1]);
        User::create(['logins' => 2]);
        User::create(['logins' => 2]);
        User::create(['logins' => 3]);
        User::create(['logins' => 3]);
        User::create(['logins' => 4]);
        User::create(['logins' => 4]);

        $this->query->where('logins', '>', 2);
        $this->assertEquals(2, $this->query->countDistinct('logins'));
    }

    public function testMax()
    {
        User::create(['logins' => 10, 'points' => 1]);
        User::create(['logins' => 11, 'points' => 4]);
        User::create(['logins' => 12, 'points' => 2]);

        $this->assertEquals(12, $this->query->max('logins'));
        $this->assertEquals(4, $this->query->max('points'));
    }

    public function testMaxWithQuery()
    {
        User::create(['logins' => 10, 'points' => 1]);
        User::create(['logins' => 11, 'points' => 2]);
        User::create(['logins' => 12, 'points' => 4]);

        $this->query->where('points', '<', 4);
        $this->assertEquals(11, $this->query->max('logins'));

        $query = new Builder((new User)->getConnection(), new CypherGrammar, new Processor);
        $query->from = 'User';
        $query->where('points', '<', 4);
        $this->assertEquals(2, $query->max('points'));
    }

    public function testMin()
    {
        User::create(['logins' => 10, 'points' => 1]);
        User::create(['logins' => 11, 'points' => 4]);
        User::create(['logins' => 12, 'points' => 2]);

        $this->assertEquals(10, $this->query->min('logins'));
        $this->assertEquals(1, $this->query->min('points'));
    }

    public function testMinWithQuery()
    {
        User::create(['logins' => 10, 'points' => 1]);
        User::create(['logins' => 11, 'points' => 4]);
        User::create(['logins' => 12, 'points' => 2]);

        $this->query->where('points', '>', 1);
        $this->assertEquals(11, $this->query->min('logins'));
        $this->assertEquals(2, $this->query->min('points'));
    }

    public function testAvg()
    {
        User::create(['logins' => 10, 'points' => 1]);
        User::create(['logins' => 11, 'points' => 4]);
        User::create(['logins' => 12, 'points' => 2]);

        $this->assertEquals(11, $this->query->avg('logins'));
        $this->assertEquals(2.3333333333333335, $this->query->avg('points'));
    }

    public function testAvgWithQuery()
    {
        User::create(['logins' => 10, 'points' => 1]);
        User::create(['logins' => 11, 'points' => 4]);
        User::create(['logins' => 12, 'points' => 2]);

        $this->query->where('points', '>', 1);

        $this->assertEquals(11.5, $this->query->avg('logins'));
        $this->assertEquals(3, $this->query->avg('points'));
    }

    public function testSum()
    {
        User::create(['logins' => 10, 'points' => 1]);
        User::create(['logins' => 11, 'points' => 4]);
        User::create(['logins' => 12, 'points' => 2]);

        $this->assertEquals(33, $this->query->sum('logins'));
        $this->assertEquals(7, $this->query->sum('points'));
    }

    public function testSumWithQuery()
    {
        User::create(['logins' => 10, 'points' => 1]);
        User::create(['logins' => 11, 'points' => 4]);
        User::create(['logins' => 12, 'points' => 2]);

        $this->query->where('points', '>', 1);
        $this->assertEquals(23, $this->query->sum('logins'));
        $this->assertEquals(6, $this->query->sum('points'));
    }

    public function testPercentileDisc()
    {
        User::create(['logins' => 10, 'points' => 1]);
        User::create(['logins' => 11, 'points' => 4]);
        User::create(['logins' => 12, 'points' => 2]);

        $this->assertEquals(10, $this->query->percentileDisc('logins'));
        $this->assertEquals(11, $this->query->percentileDisc('logins', 0.5));
        $this->assertEquals(12, $this->query->percentileDisc('logins', 1));

        $this->assertEquals(1, $this->query->percentileDisc('points'));
        $this->assertEquals(2, $this->query->percentileDisc('points', 0.6));
        $this->assertEquals(4, $this->query->percentileDisc('points', 0.9));
    }

    public function testPercentileDiscWithQuery()
    {
        User::create(['logins' => 10, 'points' => 1]);
        User::create(['logins' => 11, 'points' => 4]);
        User::create(['logins' => 12, 'points' => 2]);

        $this->query->where('points', '>', 1);
        $this->assertEquals(11, $this->query->percentileDisc('logins'));
        $this->assertEquals(11, $this->query->percentileDisc('logins', 0.5));
        $this->assertEquals(12, $this->query->percentileDisc('logins', 1));

        $this->assertEquals(2, $this->query->percentileDisc('points'));
        $this->assertEquals(4, $this->query->percentileDisc('points', 0.6));
        $this->assertEquals(4, $this->query->percentileDisc('points', 0.9));
    }

    public function testPercentileCont()
    {
        User::create(['logins' => 10, 'points' => 1]);
        User::create(['logins' => 11, 'points' => 4]);
        User::create(['logins' => 12, 'points' => 2]);

        $this->assertEquals(10, $this->query->percentileCont('logins'), 0.2);
        $this->assertEquals(10.800000000000001, $this->query->percentileCont('logins', 0.4));
        $this->assertEquals(11.800000000000001, $this->query->percentileCont('logins', 0.9));

        $this->assertEquals(1, $this->query->percentileCont('points'), 0.3);
        $this->assertEquals(2.3999999999999999, $this->query->percentileCont('points', 0.6));
        $this->assertEquals(3.6000000000000001, $this->query->percentileCont('points', 0.9));
    }

    public function testPercentileContWithQuery()
    {
        User::create(['logins' => 10, 'points' => 1]);
        User::create(['logins' => 11, 'points' => 4]);
        User::create(['logins' => 12, 'points' => 2]);

        $this->query->where('points', '<', 4);
        $this->assertEquals(10.4, $this->query->percentileCont('logins', 0.2));
        $this->assertEquals(10.8, $this->query->percentileCont('logins', 0.4));
        $this->assertEquals(11.8, $this->query->percentileCont('logins', 0.9));

        $this->assertEquals(1.2999999999999998, $this->query->percentileCont('points', 0.3));
        $this->assertEquals(1.6, $this->query->percentileCont('points', 0.6));
        $this->assertEquals(1.8999999999999999, $this->query->percentileCont('points', 0.9));
    }

    public function testStdev()
    {
        User::create(['logins' => 33, 'points' => 1]);
        User::create(['logins' => 44, 'points' => 4]);
        User::create(['logins' => 55, 'points' => 2]);

        $this->assertEquals(11, $this->query->stdev('logins'));
        $this->assertEquals(1.5275252316519, $this->query->stdev('points'));
    }

    public function testStdevWithQuery()
    {
        User::create(['logins' => 33, 'points' => 1]);
        User::create(['logins' => 44, 'points' => 4]);
        User::create(['logins' => 55, 'points' => 2]);

        $this->query->where('points', '>', 1);
        $this->assertEquals(7.778174593052, $this->query->stdev('logins'));
        $this->assertEquals(1.4142135623731, $this->query->stdev('points'));
    }

    public function testStdevp()
    {
        User::create(['logins' => 33, 'points' => 1]);
        User::create(['logins' => 44, 'points' => 4]);
        User::create(['logins' => 55, 'points' => 2]);

        $this->assertEquals(8.981462390205, $this->query->stdevp('logins'));
        $this->assertEquals(1.2472191289246, $this->query->stdevp('points'));
    }

    public function testStdevpWithQuery()
    {
        User::create(['logins' => 33, 'points' => 1]);
        User::create(['logins' => 44, 'points' => 4]);
        User::create(['logins' => 55, 'points' => 2]);

        $this->query->where('points', '>', 1);
        $this->assertEquals(5.5, $this->query->stdevp('logins'));
        $this->assertEquals(1, $this->query->stdevp('points'));
    }

    public function testCollect()
    {
        User::create(['logins' => 33, 'points' => 1]);
        User::create(['logins' => 44, 'points' => 4]);
        User::create(['logins' => 55, 'points' => 2]);

        $logins = $this->query->collect('logins');
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $logins);
        $this->assertEquals(3, count($logins));
        $this->assertContains(33, $logins);
        $this->assertContains(44, $logins);
        $this->assertContains(55, $logins);

        $points = $this->query->collect('points');
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $points);
        $this->assertEquals(3, count($points));
        $this->assertContains(1, $points);
        $this->assertContains(4, $points);
        $this->assertContains(2, $points);
    }

    public function testCollectWithQuery()
    {
        User::create(['logins' => 33, 'points' => 1]);
        User::create(['logins' => 44, 'points' => 4]);
        User::create(['logins' => 55, 'points' => 2]);

        $logins = $this->query->where('points', '>', 1)->collect('logins');
        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $logins);
        $this->assertEquals(2, count($logins));
        $this->assertContains(44, $logins);
        $this->assertContains(55, $logins);
    }
}

class User extends Model {
    protected $label = 'User';

    protected $fillable = ['logins', 'points', 'email'];
}
