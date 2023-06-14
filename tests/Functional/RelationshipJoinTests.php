<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinelab\NeoEloquent\Tests\TestCase;

class RelationshipJoinTests extends TestCase
{
    use RefreshDatabase;

    public function testRelationshipJoinInsert(): void
    {
        Builder::from('User')
            ->insert([
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
                ['id' => 4],
            ]);

        Builder::from('Alias')
            ->insert([
                ['name' => 'admin'],
                ['name' => 'teacher'],
                ['name' => 'student'],
            ]);

        Builder::from('User')
            ->whereIn('id', [2, 3])
            ->join('HAS_ALIAS>', function (JoinClause $clause) {
                $clause->joinWhere('Alias', 'name', '=', 'admin');
            })
            ->insert([
                'HAS_ALIAS' => ['id' => 1],
            ]);

        Builder::from('<HAS_ALIAS')
            ->leftJoinWhere('Alias', 'name', '=', 'teacher')
            ->rightJoinWhere('User', 'id', '=', 1)
            ->insert([
                ['id' => 2],
            ]);

        $paths = Builder::from('x')
            ->getConnection()
            ->select('MATCH (x:User) - [r:HAS_ALIAS] -> (y:Alias) RETURN x, r, y');

        $this->assertCount(3, $paths);
    }
}
