<?php namespace Vinelab\NeoEloquent\Tests\Functional\Relations\HasMany;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;

class Book extends Model {

    protected $label = 'Book';

    protected $fillable = ['title', 'pages', 'release_date'];
}

class Author extends Model {

    protected $label = 'Author';

    protected $fillable = ['name'];

    public function books()
    {
        return $this->hasMany('Vinelab\NeoEloquent\Tests\Functional\Relations\HasMany\Book', 'WROTE');
    }
}

class HasManyRelationTest extends TestCase {

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    public function setUp()
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));

        Author::setConnectionResolver($resolver);
        Book::setConnectionResolver($resolver);
    }

    public function testSavingSingleAndDynamicLoading()
    {
        $author = Author::create(['name' => 'George R. R. Martin']);
        $got = new Book(['title' => 'A Game of Thrones', 'pages' => '704', 'release_date' => 'August 1996']);
        $cok = new Book(['title' => 'A Clash of Kings', 'pages' => '768', 'release_date' => 'February 1999']);
        $writtenGot = $author->books()->save($got, ['ratings' => '123']);
        $writtenCok = $author->books()->save($cok, ['chapters' => 70]);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $writtenGot);
        $this->assertTrue($writtenGot->exists());
        $this->assertGreaterThan(0, $writtenGot->id);
        $this->assertNotNull($writtenGot->created_at);
        $this->assertNotNull($writtenGot->updated_at);
        $this->assertEquals($writtenGot->ratings, 123);

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $writtenCok);
        $this->assertTrue($writtenCok->exists());
        $this->assertGreaterThan(0, $writtenCok->id);
        $this->assertNotNull($writtenCok->created_at);
        $this->assertNotNull($writtenCok->updated_at);
        $this->assertEquals($writtenCok->chapters, 70);

        $books = $author->books;

        $this->assertCount(2, $books->toArray());
        $this->assertEquals($cok->toArray(), $author->books[0]->toArray());
        $this->assertEquals($got->toArray(), $author->books[1]->toArray());

        $writtenGot->delete();
        $writtenCok->delete();
    }

    public function testSavingManyAndDynamicLoading()
    {
        $author = Author::create(['name' => 'George R. R. Martin']);

        $novel = [
            new Book([
                'title'        => 'A Game of Thrones',
                'pages'        => 704,
                'release_date' => 'August 1996'
            ]),
            new Book([
                'title'        => 'A Clash of Kings',
                'pages'        => 768,
                'release_date' => 'February 1999'
            ]),
            new Book([
                'title'        => 'A Storm of Swords',
                'pages'        => 992,
                'release_date' => 'November 2000'
            ]),
            new Book([
                'title'        => 'A Feast for Crows',
                'pages'        => 753,
                'release_date' => 'November 2005'
            ])
        ];

        $edges = $author->books()->saveMany($novel);
        $this->assertCount(count($novel), $edges->toArray());

        $books = $author->books->toArray();
        $this->assertCount(count($novel), $books);

        foreach ($edges as $key => $edge)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $edge);
            $this->assertTrue($edge->exists());
            $this->assertGreaterThan(0, $edge->id);
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
                'title'        => 'A Game of Thrones',
                'pages'        => 704,
                'release_date' => 'August 1996'
            ],
            [
                'title'        => 'A Clash of Kings',
                'pages'        => 768,
                'release_date' => 'February 1999'
            ],
            [
                'title'        => 'A Storm of Swords',
                'pages'        => 992,
                'release_date' => 'November 2000'
            ],
            [
                'title'        => 'A Feast for Crows',
                'pages'        => 753,
                'release_date' => 'November 2005'
            ]
        ];

        foreach ($novel as $book)
        {
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
                'title'        => 'A Game of Thrones',
                'pages'        => 704,
                'release_date' => 'August 1996'
            ],
            [
                'title'        => 'A Clash of Kings',
                'pages'        => 768,
                'release_date' => 'February 1999'
            ],
            [
                'title'        => 'A Storm of Swords',
                'pages'        => 992,
                'release_date' => 'November 2000'
            ],
            [
                'title'        => 'A Feast for Crows',
                'pages'        => 753,
                'release_date' => 'November 2005'
            ]
        ];

        $edges = $author->books()->createMany($novel);

        foreach ($edges as $edge)
        {
            $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Edges\EdgeOut', $edge);
            $this->assertTrue($edge->exists());
            $this->assertGreaterThan(0, $edge->id);
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
                'title'        => 'A Game of Thrones',
                'pages'        => 704,
                'release_date' => 'August 1996'
            ]),
            new Book([
                'title'        => 'A Clash of Kings',
                'pages'        => 768,
                'release_date' => 'February 1999'
            ]),
            new Book([
                'title'        => 'A Storm of Swords',
                'pages'        => 992,
                'release_date' => 'November 2000'
            ]),
            new Book([
                'title'        => 'A Feast for Crows',
                'pages'        => 753,
                'release_date' => 'November 2005'
            ])
        ];

        $edges = $author->books()->saveMany($novel);
        $this->assertCount(count($novel), $edges->toArray());

        $author = Author::with('books')->find($author->id);
        $relations = $author->getRelations();

        $this->assertArrayHasKey('books', $relations);
        $this->assertCount(count($novel), $relations['books']->toArray());

        $booksById = [];
        foreach ($novel as $book) {
            $booksById[$book->getKey()] = $book->toArray();
        }

        foreach ($relations['books'] as $key => $book)
        {
            $this->assertEquals($booksById[$book->getKey()], $book->toArray());
            // make sure it only occurs once
            unset($booksById[$book->getKey()]);
            $edge = $author->books()->edge($book);
            $this->assertTrue($edge->delete());
        }
    }

    public function testSavingManyRelationsWithRelationProperties()
    {
        $author = Author::create(['name' => 'George R. R. Martin']);

        $novel = [
            new Book([
                'title'        => 'A Game of Thrones',
                'pages'        => 704,
                'release_date' => 'August 1996'
            ]),
            new Book([
                'title'        => 'A Clash of Kings',
                'pages'        => 768,
                'release_date' => 'February 1999'
            ]),
            new Book([
                'title'        => 'A Storm of Swords',
                'pages'        => 992,
                'release_date' => 'November 2000'
            ]),
            new Book([
                'title'        => 'A Feast for Crows',
                'pages'        => 753,
                'release_date' => 'November 2005'
            ])
        ];

        $edges = $author->books()->saveMany($novel, ['novel' => true]);
        $this->assertCount(count($novel), $edges->toArray());

        foreach ($edges as $edge)
        {
            $this->assertTrue($edge->novel);
            $edge->delete();
        }
    }

    public function testSyncingModelIds()
    {
        $author = Author::create(['name' => 'George R.R. Martin']);
        $bk     = Book::create(['title' => 'foo']);
        $got    = Book::create(['title' => 'A Game of Thrones', 'pages' => '704', 'release_date' => 'August 1996']);
        $cok    = Book::create(['title' => 'A Clash of Kings', 'pages' => '768', 'release_date' => 'February 1999']);

        $author->books()->attach($bk);

        $author->books()->sync([$got->id, $cok->id]);

        $edges = $author->books()->edges();

        $edgesIds = array_map(function($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        $this->assertTrue(in_array($got->id, $edgesIds));
        $this->assertTrue(in_array($cok->id, $edgesIds));
        $this->assertFalse(in_array($bk->id, $edgesIds));
    }

    public function testSyncingWithIdsUpdatesModels()
    {
        $author = Author::create(['name' => 'George R.R. Martin']);
        $got    = Book::create(['title' => 'A Game of Thrones', 'pages' => '704', 'release_date' => 'August 1996']);
        $cok    = Book::create(['title' => 'A Clash of Kings', 'pages' => '768', 'release_date' => 'February 1999']);
        $sos    = Book::create(['title' => 'A Storm of Swords', 'pages' => 992, 'release_date' => 'November 2000']);

        $author->books()->attach($got);

        $author->books()->sync([$got->id, $cok->id, $sos->id]);

        $edges = $author->books()->edges();

        $edgesIds = array_map(function($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        $this->assertTrue(in_array($got->id, $edgesIds));
        $this->assertTrue(in_array($cok->id, $edgesIds));
        $this->assertTrue(in_array($sos->id, $edgesIds));
    }

    public function testSyncingWithAttributes()
    {
        $author = Author::create(['name' => 'George R.R. Martin']);
        $got    = Book::create(['title' => 'A Game of Thrones', 'pages' => '704', 'release_date' => 'August 1996']);
        $cok    = Book::create(['title' => 'A Clash of Kings', 'pages' => '768', 'release_date' => 'February 1999']);
        $sos    = Book::create(['title' => 'A Storm of Swords', 'pages' => 992, 'release_date' => 'November 2000']);

        $author->books()->attach($got);

        $author->books()->sync([
            $got->id => ['series' => 'Game'],
            $cok->id => ['series' => 'Clash'],
            $sos->id => ['series' => 'Storm']
        ]);

        $edges = $author->books()->edges();

        $edgesIds = array_map(function($edge) { return $edge->getRelated()->getKey(); }, $edges->toArray());

        $count = array_count_values((array) $got->id);

        $this->assertEquals(1, $count[$got->id]);
        $this->assertTrue(in_array($cok->id, $edgesIds));
        $this->assertTrue(in_array($sos->id, $edgesIds));
        $this->assertTrue(in_array($got->id, $edgesIds));

        $expectedEdgesTypes = array('Storm', 'Clash', 'Game');

        foreach ($edges as $key => $edge)
        {
            $attributes = $edge->toArray();
            $this->assertArrayHasKey('series', $attributes);
            $this->assertEquals($expectedEdgesTypes[$key], $edge->series);
            $edge->delete();
        }
    }

}
