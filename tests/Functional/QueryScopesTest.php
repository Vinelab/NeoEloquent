<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinelab\NeoEloquent\Tests\Fixtures\Misfit;
use Vinelab\NeoEloquent\Tests\TestCase;

class QueryScopesTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

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
