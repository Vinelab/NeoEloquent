<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinelab\NeoEloquent\Tests\Fixtures\Permission;
use Vinelab\NeoEloquent\Tests\Fixtures\Role;
use Vinelab\NeoEloquent\Tests\TestCase;

class HasManyRelationTest extends TestCase
{
    use RefreshDatabase;

    public function testSavingSingleAndDynamicLoading(): void
    {
        $role = Role::create(['title' => 'George R. R. Martin']);

        $got = new Permission(['title' => 'A Game of Thrones', 'alias' => '704']);
        $cok = new Permission(['title' => 'A Clash of Kings', 'alias' => '768']);

        $role->permissions()->save($got);
        $role->permissions()->save($cok);

        $role = Role::first();
        $books = $role->permissions;

        $expectedBooks = [
            'A Game of Thrones' => $got->getAttributes(),
            'A Clash of Kings' => $cok->getAttributes(),
        ];

        $this->assertCount(2, $books->toArray());

        foreach ($books as $book) {
            $this->assertEquals($expectedBooks[$book->title], $book->getAttributes());
        }
    }

    public function testSavingManyAndDynamicLoading()
    {
        $author = Role::create(['title' => 'George R. R. Martin']);

        $novel = [
            new Permission([
                'title' => 'A Game of Thrones',
                'alias' => '704'
            ]),
            new Permission([
                'title' => 'A Clash of Kings',
                'alias' => '768'
            ]),
            new Permission([
                'title' => 'A Storm of Swords',
                'alias' => '992'
            ]),
            new Permission([
                'title' => 'A Feast for Crows',
                'alias' => '753'
            ]),
        ];

        $edges = $author->permissions()->saveMany($novel);
        $this->assertCount(count($novel), $edges);

        $books = $author->permissions->toArray();
        $this->assertCount(count($novel), $books);
    }

    public function testCreatingSingleRelatedModels()
    {
        $author = Role::create(['title' => 'George R. R. Martin']);

        $novel = [
            [
                'title' => 'A Game of Thrones',
                'alias' => '704'
            ],
            [
                'title' => 'A Clash of Kings',
                'alias' => '768'
            ],
            [
                'title' => 'A Storm of Swords',
                'alias' => '992'
            ],
            [
                'title' => 'A Feast for Crows',
                'alias' => '753'
            ],
        ];

        foreach ($novel as $book) {
            $edge = $author->permissions()->create($book);

            $this->assertInstanceOf(Permission::class, $edge);
            $this->assertNotNull($edge->created_at);
            $this->assertNotNull($edge->updated_at);
        }
    }

    public function testEagerLoadingHasMany()
    {
        $author = Role::create(['title' => 'George R. R. Martin']);

        $novel = [
            new Permission([
                'title' => 'A Game of Thrones',
                'alias' => '704'
            ]),
            new Permission([
                'title' => 'A Clash of Kings',
                'alias' => '768'
            ]),
            new Permission([
                'title' => 'A Storm of Swords',
                'alias' => '992'
            ]),
            new Permission([
                'title' => 'A Feast for Crows',
                'alias' => '753'
            ]),
        ];

        $edges = $author->permissions()->saveMany($novel);
        $this->assertCount(count($novel), $edges);

        $author = Role::with('permissions')->find($author->getKey());
        $relations = $author->getRelations();

        $this->assertArrayHasKey('permissions', $relations);
        $this->assertCount(count($novel), $relations['permissions']);

        $booksIds = array_map(function ($book) {
            return $book->getKey();
        }, $novel);

        $this->assertEquals(['704', '768', '992', '753'], $booksIds);
    }
}
