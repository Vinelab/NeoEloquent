<?php

namespace Vinelab\NeoEloquent\Tests\Functional\Relations\HasMany;

use Vinelab\NeoEloquent\Eloquent\Relations\HasMany;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;

class Book extends Model
{
    protected $table = 'Book';

    protected $fillable = ['title', 'pages', 'release_date'];
}

class Author extends Model
{
    protected $table = 'Author';

    protected $fillable = ['name'];

    public function books(): HasMany
    {
        return $this->hasManyRelationship(Book::class, 'WROTE');
    }
}

class HasManyRelationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        (new Author())->getConnection()->getPdo()->run('MATCH (x) DETACH DELETE x');
    }

    public function testSavingSingleAndDynamicLoading(): void
    {
        /** @var Author $author */
        $author = Author::query()->create(['name' => 'George R. R. Martin']);
        $got = new Book(['title' => 'A Game of Thrones', 'pages' => '704', 'release_date' => 'August 1996']);
        $cok = new Book(['title' => 'A Clash of Kings', 'pages' => '768', 'release_date' => 'February 1999']);
        $author->books()->save($got);
        $author->books()->save($cok);

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
        $this->assertCount(count($novel), $edges->toArray());

        $books = $author->books->toArray();
        $this->assertCount(count($novel), $books);

        foreach ($edges as $key => $edge) {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $edge);
            $this->assertTrue($edge->exists());
            $this->assertGreaterThanOrEqual(0, $edge->id);
            $this->assertNotNull($edge->created_at);
            $this->assertNotNull($edge->updated_at);
            $edge->delete();
        }
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
            $edge = $author->books()->create($book, ['on' => $book['release_date']]);

            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $edge);
            $this->assertTrue($edge->exists());
            $this->assertGreaterThan(0, $edge->id);
            $this->assertNotNull($edge->created_at);
            $this->assertNotNull($edge->updated_at);
            $this->assertEquals($edge->on, $book['release_date']);
            $edge->delete();
        }
    }

    public function testCreatingManyRelatedModels()
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

        $edges = $author->books()->createMany($novel);

        foreach ($edges as $edge) {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $edge);
            $this->assertTrue($edge->exists());
            $this->assertGreaterThanOrEqual(0, $edge->id);
            $this->assertNotNull($edge->created_at);
            $this->assertNotNull($edge->updated_at);

            $edge->delete();
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
        $this->assertCount(count($novel), $edges->toArray());

        $author = Author::with('books')->find($author->id);
        $relations = $author->getRelations();

        $this->assertArrayHasKey('books', $relations);
        $this->assertCount(count($novel), $relations['books']->toArray());

        $booksIds = array_map(function ($book) { return $book->getKey(); }, $novel);

        foreach ($relations['books'] as $key => $book) {
            $this->assertTrue(in_array($book->getKey(), $booksIds));
            $edge = $author->books()->edge($book);
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $edge);
        }
    }

    public function testSavingManyRelationsWithRelationProperties()
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

        $edges = $author->books()->saveMany($novel, ['novel' => true]);
        $this->assertCount(count($novel), $edges->toArray());

        foreach ($edges as $edge) {
            $this->assertTrue($edge->novel);
            $edge->delete();
        }
    }

    public function testSyncingModelIds()
    {
        $author = Author::create(['name' => 'George R.R. Martin']);
        $bk = Book::create(['title' => 'foo']);
        $got = Book::create(['title' => 'A Game of Thrones', 'pages' => '704', 'release_date' => 'August 1996']);
        $cok = Book::create(['title' => 'A Clash of Kings', 'pages' => '768', 'release_date' => 'February 1999']);

        $author->books()->attach($bk);

        $author->books()->sync([$got->id, $cok->id]);

        $edges = $author->books()->edges();

        $edgesIds = array_map(function ($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        $this->assertTrue(in_array($got->id, $edgesIds));
        $this->assertTrue(in_array($cok->id, $edgesIds));
        $this->assertFalse(in_array($bk->id, $edgesIds));
    }

    public function testSyncingWithIdsUpdatesModels()
    {
        $author = Author::create(['name' => 'George R.R. Martin']);
        $got = Book::create(['title' => 'A Game of Thrones', 'pages' => '704', 'release_date' => 'August 1996']);
        $cok = Book::create(['title' => 'A Clash of Kings', 'pages' => '768', 'release_date' => 'February 1999']);
        $sos = Book::create(['title' => 'A Storm of Swords', 'pages' => 992, 'release_date' => 'November 2000']);

        $author->books()->attach($got);

        $author->books()->sync([$got->id, $cok->id, $sos->id]);

        $edges = $author->books()->edges();

        $edgesIds = array_map(function ($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        $this->assertTrue(in_array($got->id, $edgesIds));
        $this->assertTrue(in_array($cok->id, $edgesIds));
        $this->assertTrue(in_array($sos->id, $edgesIds));
    }

    public function testSyncingWithAttributes()
    {
        $author = Author::create(['name' => 'George R.R. Martin']);
        $got = Book::create(['title' => 'A Game of Thrones', 'pages' => '704', 'release_date' => 'August 1996']);
        $cok = Book::create(['title' => 'A Clash of Kings', 'pages' => '768', 'release_date' => 'February 1999']);
        $sos = Book::create(['title' => 'A Storm of Swords', 'pages' => 992, 'release_date' => 'November 2000']);

        $author->books()->attach($got);

        $author->books()->sync([
            $got->id => ['series' => 'Game'],
            $cok->id => ['series' => 'Clash'],
            $sos->id => ['series' => 'Storm'],
        ]);

        $edges = $author->books()->edges();

        $edgesIds = array_map(function ($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        $count = array_count_values((array) $got->id);

        $this->assertEquals(1, $count[$got->id]);
        $this->assertTrue(in_array($cok->id, $edgesIds));
        $this->assertTrue(in_array($sos->id, $edgesIds));
        $this->assertTrue(in_array($got->id, $edgesIds));

        $expectedEdgesTypes = array('Storm', 'Clash', 'Game');

        foreach ($edges as $key => $edge) {
            $attributes = $edge->toArray();
            $this->assertArrayHasKey('series', $attributes);
            $this->assertTrue(in_array($edge->series, $expectedEdgesTypes));
            $index = array_search($edge->series, $expectedEdgesTypes);
            unset($expectedEdgesTypes[$index]);
            $edge->delete();
        }
    }
}
