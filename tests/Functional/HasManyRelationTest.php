<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinelab\NeoEloquent\Tests\Fixtures\Author;
use Vinelab\NeoEloquent\Tests\Fixtures\Book;
use Vinelab\NeoEloquent\Tests\TestCase;

class HasManyRelationTest extends TestCase
{
    use RefreshDatabase;

    public function testSavingSingleAndDynamicLoading(): void
    {
        $author = Author::create(['name' => 'George R. R. Martin']);

        $got = new Book(['title' => 'A Game of Thrones', 'pages' => '704', 'release_date' => 'August 1996']);
        $cok = new Book(['title' => 'A Clash of Kings', 'pages' => '768', 'release_date' => 'February 1999']);

        $author->books()->save($got);
        $author->books()->save($cok);

        $author = Author::first();
        $books = $author->books;

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
        $author = Author::create(['name' => 'George R. R. Martin']);

        $novel = [
            new Book([
                'title' => 'A Game of Thrones',
                'pages' => 704,
                'release_date' => 'August 1996',
            ]),
            new Book([
                'title' => 'A Clash of Kings',
                'pages' => 768,
                'release_date' => 'February 1999',
            ]),
            new Book([
                'title' => 'A Storm of Swords',
                'pages' => 992,
                'release_date' => 'November 2000',
            ]),
            new Book([
                'title' => 'A Feast for Crows',
                'pages' => 753,
                'release_date' => 'November 2005',
            ]),
        ];

        $edges = $author->books()->saveMany($novel);
        $this->assertCount(count($novel), $edges);

        $books = $author->books->toArray();
        $this->assertCount(count($novel), $books);
    }

    public function testCreatingSingleRelatedModels()
    {
        $author = Author::create(['name' => 'George R. R. Martin']);

        $novel = [
            [
                'title' => 'A Game of Thrones',
                'pages' => 704,
                'release_date' => 'August 1996',
            ],
            [
                'title' => 'A Clash of Kings',
                'pages' => 768,
                'release_date' => 'February 1999',
            ],
            [
                'title' => 'A Storm of Swords',
                'pages' => 992,
                'release_date' => 'November 2000',
            ],
            [
                'title' => 'A Feast for Crows',
                'pages' => 753,
                'release_date' => 'November 2005',
            ],
        ];

        foreach ($novel as $book) {
            $edge = $author->books()->create($book);

            $this->assertInstanceOf(Book::class, $edge);
            $this->assertTrue($edge->exists());
            $this->assertNotNull($edge->created_at);
            $this->assertNotNull($edge->updated_at);
        }
    }

    public function testEagerLoadingHasMany()
    {
        $author = Author::create(['name' => 'George R. R. Martin']);

        $novel = [
            new Book([
                'title' => 'A Game of Thrones',
                'pages' => 704,
                'release_date' => 'August 1996',
            ]),
            new Book([
                'title' => 'A Clash of Kings',
                'pages' => 768,
                'release_date' => 'February 1999',
            ]),
            new Book([
                'title' => 'A Storm of Swords',
                'pages' => 992,
                'release_date' => 'November 2000',
            ]),
            new Book([
                'title' => 'A Feast for Crows',
                'pages' => 753,
                'release_date' => 'November 2005',
            ]),
        ];

        $edges = $author->books()->saveMany($novel);
        $this->assertCount(count($novel), $edges);

        $author = Author::with('books')->find($author->getKey());
        $relations = $author->getRelations();

        $this->assertArrayHasKey('books', $relations);
        $this->assertCount(count($novel), $relations['books']);

        $booksIds = array_map(function ($book) {
            return $book->getKey();
        }, $novel);

        $this->assertEquals(['A Game of Thrones', 'A Clash of Kings', 'A Storm of Swords', 'A Feast for Crows'], $booksIds);
    }
}
