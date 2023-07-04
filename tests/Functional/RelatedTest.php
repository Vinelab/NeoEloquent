<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Laudis\Neo4j\Databags\SummarizedResult;
use Vinelab\NeoEloquent\Tests\Fixtures\Role;
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

    public function testMultiple(): void
    {
        $user = User::create(['name' => 'User']);

        $user->relatedRoles()->createMany([
            ['title' => 'Role'],
            ['title' => 'Bowling'],
            ['title' => 'Test'],
        ]);

        $results = $this->getConnection()->select('RETURN COUNT { MATCH (u:Individual) - [:HAS_ROLE] -> (r:Role) } AS count');
        $this->assertEquals(3, $results[0]['count']);

        $results = $this->getConnection()->select('RETURN COUNT { MATCH (u:Individual) } AS count');
        $this->assertEquals(1, $results[0]['count']);

        $this->assertEquals(3, $user->relatedRoles->count());
    }

    public function testSync(): void
    {
        $user = User::create(['name' => 'User']);

        $role1 = Role::create(['title' => 'Role']);
        Role::create(['title' => 'Bowling']);
        $role3 = Role::create(['title' => 'Test']);

        $user->relatedRoles()->sync([$role1->getKey(), $role3->getKey()]);

        $results = $this->getConnection()->select('RETURN COUNT { MATCH (u:Individual) - [:HAS_ROLE] -> (r:Role) } AS count');
        $this->assertEquals(2, $results[0]['count']);

        $results = $this->getConnection()->select('RETURN COUNT { MATCH (u:Individual) } AS count');
        $this->assertEquals(1, $results[0]['count']);
    }
}