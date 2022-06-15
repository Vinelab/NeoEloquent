[![Build Status](https://travis-ci.org/Vinelab/NeoEloquent.svg?branch=master)](https://travis-ci.org/Vinelab/NeoEloquent)

# NeoEloquent

Combine the world's most powerful graph database with the best web development framework available.

_The Laravel ecosystem is massive. This library aims to achieve feature parity with the database drivers provided by default in the framework. Advantages of this include, but are not limited to:_

- **Frictionless** migration between database paradigms
- **Extreme performance gains** when working with relational data
- **Increased functionality** (createWith, N-degree relations, Cypher, ...)
- **Easy onboarding** (Only learn Cypher, the graph database query language when you hit the limits of the query builder)
- **Worry free** configuration
- **Optional migrations**. Migrations are only needed for indexes, constraints and moving data around. Neo4J itself is schemaless.
- **Support for complex deployments** If you are using Neo4J aura, a cluster or a single instance, the driver will automatically connect to it.
- **Built-in integration** with laravel packages

Please refer to the [roadmap](#roadmap) for a list of available features and to the [usage](#usage) section for a list of out-of-the-box features that are available from Laravel.

> NOTE: you are looking at version 2.0. It is currently in alpha stage and contains drastic changes under the hood. Please refer to the [architecture](#architecture) to gain some more insight on what has changed, and why.

## Quick Reference

 - [Installation](#installation)
 - [Getting Started](#getting-started)
 - [Usage](#usage)
 - [Relationships](#relationships)
 - [Diving Deeper](#diving-deeper)
 - [Only in Neo](#only-in-neo)
 - [Things To Avoid](#avoid)
 - [Roadmap](#roadmap)
 - [Architecture](#architecture)
 - [Special thanks](#special-thanks)

## Installation

Run `composer require vinelab/neoeloquent`

Or add the package to your `composer.json` and run `composer update`.

```json
{
    "require": {
        "vinelab/neoeloquent": "1.8.*"
    }
}
```

The post install script will automatically add the service provider in `app/config/app.php`:

```php
Vinelab\NeoEloquent\NeoEloquentServiceProvider::class
```

## Getting started

### Configuration

If you plan on making Neo4J your main database you can make it your default connection:

```php
'default' => 'neo4j',
```

Add the connection defaults:

```php
'connections' => [
    'neo4j' => [
        'driver' => 'neo4j',
        'scheme' => env('DB_SCHEME', 'bolt'),
        'host' => env('DB_HOST', 'localhost'),
        'port' => env('DB_PORT', 7687),
        'database' => env('DB_DATABASE', 'neo4j'),
        'username' => env('DB_USERNAME'),
        'password' => env('DB_PASSWORD')
    ],
]
```

### Defining models

You can always extend from the basic Eloquent Model instead of a NeoEloquent model. The configured connection chooses the correct driver under the hood. But you will lose out of some quality of life methods and functionality, especially when defining relations.

```php
class Article extends \Vinelab\NeoEloquent\Eloquent\Model {
}
```
    
You can now use Laravel as normal. All database functionality can now be used interchangeably with other connections and drivers.

## Usage

For general usage, you can simpy refer to the laravel docs. Things only get a little different when working with [relationships](#relationships).

We have compiled a list of all database-related features in Laravel, so you can refer to their docs from here:

- [Certain validation rules](https://laravel.com/docs/validation#available-validation-rules)
- [Broadcasting](https://laravel.com/docs/broadcasting)
- [Route model binding](https://laravel.com/docs/routing#route-model-binding)
- [Notifications](https://laravel.com/docs/notifications)
- [Queues](https://laravel.com/docs/queues)
- [Authentication](https://laravel.com/docs/authentication)
- [Authorization](https://laravel.com/docs/authorization)
- [Database](https://laravel.com/docs/database)
- [Query builder](https://laravel.com/docs/queries)
- [Pagination](https://laravel.com/docs/pagination)
- [Migrations](https://laravel.com/docs/migrations)
- [Seeding](https://laravel.com/docs/seeding)
- [Eloquent](https://laravel.com/docs/eloquent)
- All packages building on top of laravel!

Of course, all other laravel features will continue to work as well.

## Relationships

Relationships work out of the box. Basic methods work with foreign key assumptions to maintain backwards compatibility. This means JOINS in disguise!

All relationships provided by Eloquent have an equivalent in neo4j. They can be accessed by adding `Relationship` after the method name. (eg. `belongsTo` becomes `belongsToRelationship`)

- [One-To-One](#one-to-one)
- [One-To-Many](#one-to-many)
- [Many-To-Many](#many-to-many)

This documentation only explains how it uses Neo4J relations instead of foreign keys. We have placed links to the original relationship documentation where needed.

### One-To-One

Please refer to https://laravel.com/docs/eloquent-relationships#one-to-one for a more in depth explanation of the relationship itself.

```php
class User extends NeoEloquent {

    public function phone()
    {
        return $this->hasOneRelationship('Phone', 'HAS_PHONE');
    }
```

This represents an `OUTGOING` relationship direction from the `:User` node to a `:Phone`. `(:User) - [:HAS_PHONE] -> (:Phone)`

##### Defining The Inverse Of This Relation

```php
class Phone extends \Vinelab\NeoEloquent\Eloquent\Model {

    public function user()
    {
        return $this->belongsToRelation('User', 'HAS_PHONE');
    }
}
```

This represents an `INCOMING` relationship direction from
the `:User` node to this `:Phone` node. `(:Phone) <- [:HAS_PHONE] - (:USER)`

### One-To-Many

Please refer to https://laravel.com/docs/eloquent-relationships#one-to-many for a more in-depth explanation of the relationship.

```php
class User extends \Vinelab\NeoEloquent\Eloquent\Model {

    public function posts()
    {
        return $this->hasManyRelation('Post', 'POSTED');
    }
}
```

> NOTE: The attentive reader might figure out that there is no difference between the relationships one-to-one and one-to-many in Neo4J. This is because the way foreign-keys are set up in sql. The distinction between one-to-one and one-to-many is purely application based in NeoEloquent. A one-to-one relation boils down to a one-to-many relationship with a result limit of 1. 

This represents an `OUTGOING` relationship direction
from the `:User` node to the `:Post` node. `(:User) - [:POSTED] -> (:Post)`

#### Defining The Inverse Of This Relation

```php
class Post extends \Vinelab\NeoEloquent\Eloquent\Model {

    public function author()
    {
        return $this->belongsToRelation('User', 'POSTED');
    }
}
```

This represents an `INCOMING` relationship direction from
the `:User` node to this `:Post` node. `(:Post) <- [:POSTED] - (:User)`

### Many-To-Many

Please refer to https://laravel.com/docs/eloquent-relationships#many-to-many for a more in depth explanation of the relationship.

```php
class User extends \Vinelab\NeoEloquent\Eloquent\Model {

    public function followers()
    {
        return $this->belongsToManyRelation('User', 'FOLLOWS>');
    }
}
```
This represents an `Outgoing` relationship between a `:User` node and another `:User`. `(:User) - [:FOLLOWS] -> (:User)`

Belongs to many uses a relationship as a table. In other words, the pivot table is a relationship in Neo4J. When you define properties on the pivot table, you define them on the relationship.

Since a relationship must always have a direction when creating it, you need to annotate the direction with an arrow like `<FOLLOWS` or `FOLLOWS>`.

### Polymorphic relationships

Polymorphic relationships are completely superfluous in Neo4J. A relationship does not care about the label of the start or end node. Because of this, all morphing relationships can be reduced to their normal equivalent.

You can refer to the morphing relationships [here](https://laravel.com/docs/eloquent-relationships#polymorphic-relationships) and convert them to their non-morphing relationship equivalent based on the table below:

| Morphing relationship | NeoEloquent equivalent |
|-----------------------|------------------------|
| morphTo               | belongsToRelation      |
| morphOne              | hasOneRelation         |
| morphTo               | belongsToRelation      |
| morphMany             | hasManyRelation        |
| morphToMany           | belongsToManyRelation  |
| morphedByMany         | belongsToManyRelation  |


## Only in Neo

- [CreateWith](#createwith)

Here you will find NeoEloquent-specific methods and implementations that with the
wonderful Eloquent methods would make working with Graph and Neo4j a blast!

### CreateWith

- [Creating Relations](#creating-new-records-and-relations)
- [Attaching Relations](#attaching-existing-records-as-relations)

This method will "kind of" fill the gap between relational and document databases,
it allows the creation of multiple related models with one database hit.

#### Creating New Records and Relations

Here's an example of creating a post with attached photos and videos:

```php
class Post extends NeoEloquent {

    public function photos()
    {
        return $this->hasMany('Photo', 'PHOTO');
    }

    public function videos()
    {
        return $this->hasMany('Video', 'VIDEO');
    }
}
```

```php

Post::createWith(['title' => 'the title', 'body' => 'the body'], [
    'photos' => [
        [
            'url'      => 'http://url',
            'caption'  => '...',
            'metadata' => '...'
        ],
        [
            'url' => 'http://other.url',
            'caption' => 'the bay',
            'metadata' => '...'
        ]
    ],

    'videos' => [
        'title' => 'Boats passing us by',
        'description' => '...'
    ]
]);
```

> The keys `photos` and `videos` must be the same as the relation method names in the
`Post` model.

The Cypher query performed by the example above is:

```
CREATE (post:`Post` {title: 'the title', body: 'the body'}),
(post)-[:PHOTO]->(:`Photo` {url: 'http://url', caption: '...', metadata: '...'}),
(post)-[:PHOTO]->(:`Photo` {url: 'http://other', caption: 'the bay', metadata: '...'}),
(post)-[:VIDEO]->(:`Video` {title: 'Boats passing us by', description: '...'});
```

We will get the nodes created with their relations as such:

![CreateWith](https://googledrive.com/host/0BznzZ2lBbT0cLW9YcjNldlJkcXc/createWith-preview.db.png "CreateWith")

You may also mix models and attributes as relation values but it is not necessary
since NeoEloquent will pass the provided attributes through the `$fillable`
filter pipeline:

```php
$videos = new Video(['title' => 'foo', 'description' => 'bar']);
Post::createWith($info, compact('videos'));
```

You may also use a single array of attributes as such:

```php
class User extends NeoEloquent {

    public function account()
    {
        return $this->hasOne('Account');
    }
}

User::createWith(['name' => 'foo'], ['account' => ['guid' => 'bar', 'email' => 'some@mail.net']]);
```

#### Attaching Existing Records as Relations

`createWith` is intelligent enough to know the difference when you pass an existing model,
a model Id or new records that you need to create which allows mixing new records with existing ones.

```php
class Post extends NeoEloquent {

    public function tags()
    {
        return $this->hasMany('Tag', 'TAG');
    }
}
```

```php
$tag1 = Tag::create(['title' => 'php']);
$tag2 = Tag::create(['title' => 'dev']);

$post = Post::createWith(['title' => 'foo', 'body' => 'bar'], ['tags' => [$tag1, $tag2]]);
```

And we will get the `Post` related to the existing `Tag` nodes.

Or using the `id` of the model:

```php
Post::createWith(['title' => 'foo', 'body' => 'bar'], ['tags' => 1, 'privacy' => 2]);
```

The Cypher for the query that attaches records would be:

```
CREATE (post:`Post` {title: 'foo', 'body' => 'bar'})
WITH post
MATCH (tag:`Tag`)
WHERE id(tag) IN [1, 2]
CREATE (post)-[:TAG]->(tag);
```

## Avoid

Beware of these common pitfalls.

### JOINS :confounded:

_You were so preoccupied with whether you could, you did not stop to consider if you should._

Joins make no sense for Graph, we have relationships!

They are available to achieve feature parity, but Neo4J will issue warnings if you do use them. Please refer to the [relationship](#relationships) section find better ways for defining relations.

### Eloquent relationships

If you are using the same methods found in the laravel documentation for defining relationships between models, you will be using the foreign-key assumptions, which are joins in disguise!

All model relationship have a neo4j relationship equivalent, using neo4j relationships instead of joins. Please refer to [Relationships](#relationships) for more information.

### Nested Arrays

Nested arrays are not supported in Neo4J. If you ever find yourself creating them, you are probably confronting an anti-pattern:

```php
User::create(['name' => 'Some Name', 'location' => ['lat' => 123, 'lng'=> -123 ] ]);
```

Check out the [createWith()](#createwith) method on how you can achieve this in a Graph way. The nested attributes should be encapsulated in another node.

## Diving deeper

### Juggling connections

If you are juggling multiple connections/databases you can always change the connections for any database related classes manually. Examples include, but are not limited to: Models, Query builders, Schema, Basic queries, etc.

_For Models_
```php
class Neo4JArticle extends \Vinelab\NeoEloquent\Eloquent\Model {
    protected $connection = 'neo4j';
}

class SqlArticle extends \Illuminate\Database\Eloquent\Model {
    protected $connection = 'mysql';
}
```

_For Query Builders and direct queries_

```php
use Illuminate\Support\Facades\DB;

$neo4jArticle = DB::connection('neo4j')
    ->table('Article')
    ->where('x', 'y')
    ->first();

$sqlArticle = DB::connection('mysql')
    ->table('articles')
    ->where('x', 'y')
    ->first();

DB::connection('neo4j')->insert(<<<'CYPHER'
CREATE (a:Article {title: $title})
CYPHER, ['title' => 'My awesome blog post']);

DB::connection('mysql')->insert(<<<'SQL'
INSERT INTO articles (title)
VALUES (?); 
SQL, ['My awesome blog post']);
```

_For Schema Builders / Migrations (Work in progress)_
```php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::connection('neo4j')->create('Article', function (Blueprint $node) {
    $node->increments('id');
    $node->index('createdAt');
    $node->index('updatedAt');
    $node->index('title');
});

Schema::connection('neo4j')->create('Article', function (Blueprint $node) {
    $node->increments('id');
    $node->index('createdAt');
    $node->index('updatedAt');
    $node->index('title');
});
```

### Tables, nodes and labels

#### Why we use tables instead of nodes and labels

In our never-ending quest of achieving feature-parity, we landed on the design decision to keep the word table in the initial stage of the library. This might be strange if you are a fellow graph-aficionado. Laravel is built with relational databases in mind, which only knows tables, while Neo4J only knows nodes and relationships. NeoEloquent treats relationship types and node labels as the equivalent of a table.

If you are creating a new query or model, you will have to use the table word while in reality you are either defining a label or relationship type, depending on the context. Please refer to [architecture](#architecture) for a more in-depth explanation.

The previous version used the word label, but that makes for some confusing instances in the rare case the label is actually a relationship type or when the end user is not aware of the label keyword and makes futile attempts when defining a table.

Please join the label discussion [here](), which is scheduled for release 2.1.

#### Implicit table naming

If you are using NeoEloquent and have not explicitly defined a table, the table name will be guessed based on the class name. The table will be the studly-case of the class basename `$this->table ?? Str::studly(class_basename($this))`.

## Roadmap

This version is currently in alpha. In order for it to be released there are a few more fixes that need to happen. The overview can be found here:

| Feature                        | Completed?                     |
|--------------------------------|--------------------------------|
| Automatic connection resolving | yes                            |
| Transactions                   | yes                            |
| Connection statement handling  | yes                            |
| Selects                        | yes                            |
| Columns                        | yes                            |
| Wheres                         | almost all                     |
| Nested wheres                  | yes                            |
| Exists                         | yes                            |
| Insert                         | all except pivot relationships |
| Update                         | yes                            |
| Delete                         | yes                            |
| Union                          | yes                            |
| Join                           | yes                            |
| Limit                          | yes                            |
| Offset                         | yes                            |
| Orders                         | yes                            |
| Having                         | testing                        |
| Groups                         | testing                        |
| Truncate                       | yes                            |
| Aggregate                      | yes                            |
| One-to-one relationships       | yes                            |
| One-to-many relationships      | yes                            |
| Many-to-many relationships     | work in progress               |
| Relationship preloading        | no                             |
| Schema                         | no                             |
| createWith                     | out-of-order                   |
| label variables and methods    | under discussion               |
| multiple labels                | under discussion               |
| N-degree relationships         | under discussion               |

## Architecture

There are two main classes doing the heavy lifting:

1. The `Connection` class, which delegates the queries and parameters to the underlying Neo4J driver.
2. The `DSLGrammar` class, which converts the Query Builder to their respective Cypher DSL. The `CypherGrammar` class then converts the DSL to cypher strings.

These two classes offer the deepest possible level of integration within the Laravel Framework. Other classes such as the relations, query and eloquent builder simply offer specific methods or constructors to help mitigate the few inconsistencies between SQL and Cypher that are impossible to solve otherwise.

## Special Thanks

This package is a huge undertaking built on top of the thriving Neo4J PHP ecosystem. Special thanks are in order:

- [Michal Štefaňák](https://github.com/stefanak-michal), maintainer of the [bolt library](https://github.com/neo4j-php/Bolt), without whom it wouldn't even be possible to connect to Neo4J in the first place
- [Marijn van Wezel](https://github.com/marijnvanwezel), maintainer of the [PHP cypher DSL](https://github.com/WikibaseSolutions/php-cypher-dsl), whose library provided useful abstractions making it possible to convert the SQL assumptions of Laravel to Cypher queries.
- [Abed Halawi](https://github.com/Mulkave), maintainer and pioneer of the NeoEloquent library
- [Ghlen Nagels](https://github.com/transistive), maintainer of the [driver and client](https://github.com/neo4j-php/neo4j-php-client)
- [Neo4J](https://neo4j.com) for providing the resources and fertile soil to allow the community to grow. In particular to [Florent](https://github.com/fbiville) and [Michael](https://twitter.com/mesirii)

