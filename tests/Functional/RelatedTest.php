<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Laudis\Neo4j\Databags\SummarizedResult;
use Vinelab\NeoEloquent\Tests\Fixtures\User;
use Vinelab\NeoEloquent\Tests\TestCase;

class RelatedTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->statement('MATCH (n) DETACH DELETE n');
    }

    public function testSimple(): void
    {
        $user = User::create(['name' => 'User']);

        $user->relatedRoles()->create([
            'title' => 'Role'
        ]);

        $results = $this->getConnection()->select('RETURN COUNT { MATCH (u:Individual) - [:HAS_ROLE] -> (r:Role) } AS count');

        $this->assertEquals(1, $results[0]['count']);
    }
}