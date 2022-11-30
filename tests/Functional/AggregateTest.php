<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Tests\Fixtures\User;

class AggregateTest extends TestCase
{
    use RefreshDatabase;

    public function testCount(): void
    {
        User::query()->create([]);
        $this->assertEquals(1, User::query()->count());
        User::query()->create([]);
        $this->assertEquals(2, User::query()->count());
        User::query()->create([]);
        $this->assertEquals(3, User::query()->count());

        User::query()->create(['logins' => 10]);
        $this->assertEquals(1, User::query()->count('logins'));

        User::query()->create(['logins' => 10]);
        $this->assertEquals(2, User::query()->count('logins'));

        User::query()->create(['points' => 200]);
        $this->assertEquals(1, User::query()->count('points'));
    }

    public function testCountWithQuery(): void
    {
        User::query()->create(['email' => 'foo@mail.net', 'points' => 2]);
        User::query()->create(['email' => 'bar@mail.net', 'points' => 2]);

        $count = User::query()->where('email', 'foo@mail.net')->count();
        $this->assertEquals(1, $count);

        $count = User::query()->where('email', 'bar@mail.net')->count();
        $this->assertEquals(1, $count);

        $count = User::query()->where('points', 2)->count();
        $this->assertEquals(2, $count);
    }

    public function testCountDistinct(): void
    {
        User::query()->create(['logins' => 1]);
        User::query()->create(['logins' => 2]);
        User::query()->create(['logins' => 2]);
        User::query()->create(['logins' => 3]);
        User::query()->create(['logins' => 3]);
        User::query()->create(['logins' => 4]);
        User::query()->create(['logins' => 4]);

        $this->assertEquals(
            4,
            User::query()->distinct()->count('logins')
        );
    }

    public function testCountDistinctWithQuery(): void
    {
        User::query()->create(['logins' => 1]);
        User::query()->create(['logins' => 2]);
        User::query()->create(['logins' => 2]);
        User::query()->create(['logins' => 3]);
        User::query()->create(['logins' => 3]);
        User::query()->create(['logins' => 4]);
        User::query()->create(['logins' => 4]);

        $count = User::query()->where('logins', '>', 2)->distinct()->count('logins');
        $this->assertEquals(2, $count);
    }

    public function testMax(): void
    {
        User::query()->create(['logins' => 10, 'points' => 1]);
        User::query()->create(['logins' => 11, 'points' => 4]);
        User::query()->create(['logins' => 12, 'points' => 2]);

        $this->assertEquals(12, User::query()->max('logins'));
        $this->assertEquals(4, User::query()->max('points'));
    }

    public function testMaxWithQuery(): void
    {
        User::query()->create(['logins' => 10, 'points' => 1]);
        User::query()->create(['logins' => 11, 'points' => 2]);
        User::query()->create(['logins' => 12, 'points' => 4]);

        $count = User::query()->where('points', '<', 4)->max('logins');
        $this->assertEquals(11, $count);
        $this->assertEquals(2,User::query()->where('points', '<', 4)->max('points'));
    }

    public function testMin(): void
    {
        User::query()->create(['logins' => 10, 'points' => 1]);
        User::query()->create(['logins' => 11, 'points' => 4]);
        User::query()->create(['logins' => 12, 'points' => 2]);

        $this->assertEquals(10, User::query()->min('logins'));
        $this->assertEquals(1, User::query()->min('points'));
    }

    public function testMinWithQuery(): void
    {
        User::query()->create(['logins' => 10, 'points' => 1]);
        User::query()->create(['logins' => 11, 'points' => 4]);
        User::query()->create(['logins' => 12, 'points' => 2]);

        $query = User::query()->where('points', '>', 1);
        $this->assertEquals(11, $query->min('logins'));
        $this->assertEquals(2, $query->min('points'));
    }

    public function testAvg(): void
    {
        User::query()->create(['logins' => 10, 'points' => 1]);
        User::query()->create(['logins' => 11, 'points' => 4]);
        User::query()->create(['logins' => 12, 'points' => 2]);

        $this->assertEquals(11, User::query()->avg('logins'));
        $this->assertEquals(2.3333333333333335, User::query()->avg('points'));
    }

    public function testAvgWithQuery(): void
    {
        User::query()->create(['logins' => 10, 'points' => 1]);
        User::query()->create(['logins' => 11, 'points' => 4]);
        User::query()->create(['logins' => 12, 'points' => 2]);

        $query = User::query()->where('points', '>', 1);

        $this->assertEquals(11.5, $query->avg('logins'));
        $this->assertEquals(3, $query->avg('points'));
    }

    public function testSum(): void
    {
        User::query()->create(['logins' => 10, 'points' => 1]);
        User::query()->create(['logins' => 11, 'points' => 4]);
        User::query()->create(['logins' => 12, 'points' => 2]);

        $this->assertEquals(33, User::query()->sum('logins'));
        $this->assertEquals(7, User::query()->sum('points'));
    }

    public function testSumWithQuery(): void
    {
        User::query()->create(['logins' => 10, 'points' => 1]);
        User::query()->create(['logins' => 11, 'points' => 4]);
        User::query()->create(['logins' => 12, 'points' => 2]);

        $query = User::query()->where('points', '>', 1);
        $this->assertEquals(23, $query->sum('logins'));
        $this->assertEquals(6, $query->sum('points'));
    }

    public function testPercentileDisc(): void
    {
        User::query()->create(['logins' => 10, 'points' => 1]);
        User::query()->create(['logins' => 11, 'points' => 4]);
        User::query()->create(['logins' => 12, 'points' => 2]);

        $this->assertEquals(11, User::query()->percentileDisc('logins', 0.5));
        $this->assertEquals(12, User::query()->percentileDisc('logins', 1));

        $this->assertEquals(1, User::query()->percentileDisc('points'));
        $this->assertEquals(2, (Int)User::query()->percentileDisc('points', 0.6));
        $this->assertEquals(4, User::query()->percentileDisc('points', 0.9));
    }

    public function testPercentileDiscWithQuery(): void
    {
        User::query()->create(['logins' => 10, 'points' => 1]);
        User::query()->create(['logins' => 11, 'points' => 4]);
        User::query()->create(['logins' => 12, 'points' => 2]);

        $builder = User::query()->where('points', '>', 1);
        $this->assertEquals(11, $builder->percentileDisc('logins'));
        $this->assertEquals(11, $builder->percentileDisc('logins', 0.5));
        $this->assertEquals(12, $builder->percentileDisc('logins', 1));
        $this->assertEquals(2, $builder->percentileDisc('points'));
        $this->assertEquals(4.0, $builder->percentileDisc('points', 0.6));
        $this->assertEquals(4, $builder->percentileDisc('points', 0.9));
    }

    public function testPercentileCont(): void
    {
        User::query()->create(['logins' => 10, 'points' => 1]);
        User::query()->create(['logins' => 11, 'points' => 4]);
        User::query()->create(['logins' => 12, 'points' => 2]);

        $this->assertEquals(10, User::query()->percentileCont('logins'), 0.2);
        $this->assertEquals(10.800000000000001, User::query()->percentileCont('logins', 0.4));
        $this->assertEquals(11.800000000000001, User::query()->percentileCont('logins', 0.9));

        $this->assertEquals(1, User::query()->percentileCont('points'), 0.3);
        $this->assertEquals(2.3999999999999999, User::query()->percentileCont('points', 0.6));
        $this->assertEquals(3.6000000000000001, User::query()->percentileCont('points', 0.9));
    }

    public function testPercentileContWithQuery(): void
    {
        User::query()->create(['logins' => 10, 'points' => 1]);
        User::query()->create(['logins' => 11, 'points' => 4]);
        User::query()->create(['logins' => 12, 'points' => 2]);

        $builder = User::query();
        $builder->where('points', '<', 4);
        $this->assertEquals(10.4, $builder->percentileCont('logins', 0.2));
        $this->assertEquals(10.8, $builder->percentileCont('logins', 0.4));
        $this->assertEquals(11.8, $builder->percentileCont('logins', 0.9));

        $this->assertEquals(1.2999999999999998, $builder->percentileCont('points', 0.3));
        $this->assertEquals(1.6, $builder->percentileCont('points', 0.6));
        $this->assertEquals(1.8999999999999999, $builder->percentileCont('points', 0.9));
    }

    public function testStdev(): void
    {
        User::query()->create(['logins' => 33, 'points' => 1]);
        User::query()->create(['logins' => 44, 'points' => 4]);
        User::query()->create(['logins' => 55, 'points' => 2]);

        $this->assertEquals(11, User::query()->stdev('logins'));
        $this->assertEqualsWithDelta(1.52, User::query()->stdev('points'), 0.01);
    }

    public function testStdevWithQuery(): void
    {
        User::query()->create(['logins' => 33, 'points' => 1]);
        User::query()->create(['logins' => 44, 'points' => 4]);
        User::query()->create(['logins' => 55, 'points' => 2]);

        $query = User::query()->where('points', '>', 1);
        $this->assertEqualsWithDelta(7.78, $query->stdev('logins'), 0.01);
        $this->assertEqualsWithDelta(1.41, $query->stdev('points'), 0.01);
    }

    public function testStdevp(): void
    {
        User::query()->create(['logins' => 33, 'points' => 1]);
        User::query()->create(['logins' => 44, 'points' => 4]);
        User::query()->create(['logins' => 55, 'points' => 2]);

        $this->assertEqualsWithDelta(8.98, User::query()->stdevp('logins'), 0.01);
        $this->assertEqualsWithDelta(1.25, User::query()->stdevp('points'), 0.01);
    }

    public function testStdevpWithQuery(): void
    {
        User::query()->create(['logins' => 33, 'points' => 1]);
        User::query()->create(['logins' => 44, 'points' => 4]);
        User::query()->create(['logins' => 55, 'points' => 2]);

        $query = User::query();
        $query->where('points', '>', 1);
        $this->assertEqualsWithDelta(5.5, $query->stdevp('logins'), 0.01);
        $this->assertEqualsWithDelta(1, $query->stdevp('points'), 0.01);
    }

    public function testCollect(): void
    {
        User::query()->create(['logins' => 33, 'points' => 1]);
        User::query()->create(['logins' => 44, 'points' => 4]);
        User::query()->create(['logins' => 55, 'points' => 2]);

        $logins = User::query()->collect('logins');
        $this->assertInstanceOf(Collection::class, $logins);
        $this->assertCount(3, $logins);
        $this->assertContains(33, $logins);
        $this->assertContains(44, $logins);
        $this->assertContains(55, $logins);

        $points = User::query()->collect('points');
        $this->assertInstanceOf(Collection::class, $points);
        $this->assertCount(3, $points);
        $this->assertContains(1, $points);
        $this->assertContains(4, $points);
        $this->assertContains(2, $points);
    }

    public function testCollectWithQuery(): void
    {
        User::query()->create(['logins' => 33, 'points' => 1]);
        User::query()->create(['logins' => 44, 'points' => 4]);
        User::query()->create(['logins' => 55, 'points' => 2]);

        $logins = User::query()->where('points', '>', 1)->collect('logins');
        $this->assertInstanceOf(Collection::class, $logins);

        $this->assertCount(2, $logins);
        $this->assertContains(44, $logins);
        $this->assertContains(55, $logins);
    }
}