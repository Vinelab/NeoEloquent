[![SensioLabsInsight](https://insight.sensiolabs.com/projects/46d632f8-6b3c-4446-a2d4-c227ba4cf373/big.png)](https://insight.sensiolabs.com/projects/46d632f8-6b3c-4446-a2d4-c227ba4cf373)

[![Build Status](https://travis-ci.org/Vinelab/NeoEloquent.svg?branch=master)](https://travis-ci.org/Vinelab/NeoEloquent)

# NeoEloquent
Neo4j Graph Eloquent Driver for Laravel

## Chat & Support
Join the [Official Neo4j Slack Group](https://neo4j.com/blog/public-neo4j-users-slack-group/) and use the #neo4j-php channel.

## Quick Reference

 - [Installation](#installation)
 - [Configuration](#configuration)
 - [Models](#models)
 - [Relationships](#relationships)
 - [Edges](#edges)
 - [Migration](#migration)
 - [Schema](#schema)
 - [Aggregates](#aggregates)
 - [Only in Neo](#only-in-neo)
 - [Things To Avoid](#avoid)

## Installation

Add the package to your `composer.json` and run `composer update`.

### Laravel 5

#### 5.6

```json
{
    "require": {
        "vinelab/neoeloquent": "^1.4.6"
    }
}
```


#### 5.5

```json
{
    "require": {
        "vinelab/neoeloquent": "^1.4.5"
    }
}
```


#### 5.4

```json
{
    "require": {
        "vinelab/neoeloquent": "1.4.3"
    }
}
```

#### 5.3

```json
{
    "require": {
        "vinelab/neoeloquent": "1.4.2"
    }
}
```

#### 5.2

```json
{
    "require": {
        "vinelab/neoeloquent": "1.3.*"
    }
}
```

#### 5.1

```json
{
    "require": {
        "vinelab/neoeloquent": "1.2.*"
    }
}
```

#### 5.0

```json
{
    "require": {
        "vinelab/neoeloquent": "1.2.5"
    }
}
```

### Laravel 4

```json
{
    "require": {
        "vinelab/neoeloquent": "1.1.*"
    }
}
```

Add the service provider in `app/config/app.php`:

```php
'Vinelab\NeoEloquent\NeoEloquentServiceProvider',
```

The service provider will register all the required classes for this package and will also alias
the `Model` class to `NeoEloquent` so you can simply `extend NeoEloquent` in your models.

## Configuration

### Connection
in `app/config/database.php` or in case of an environment-based configuration `app/config/[env]/database.php`
make `neo4j` your default connection:

```php
'default' => 'neo4j',
```

Add the connection defaults:

```php
'connections' => [
    'neo4j' => [
        'driver' => 'neo4j',
        'host'   => env('DB_HOST', 'localhost'),
        'port'   => env('DB_PORT', '7474'),
        'username' => env('DB_USERNAME', null),
        'password' => env('DB_PASSWORD', null)
    ]
]
```
### Lumen

For Lumen you need to create a new folder called `config` in the application root and there add a file called `database.php`. There you will add the following code.

```php
<?php

return ['connections' => [
            'neo4j' => [
                'driver' => 'neo4j',
                'host'   => env('DB_HOST', 'localhost'),
                'port'   => env('DB_PORT', '7474'),
                'username' => env('DB_USERNAME', null),
                'password' => env('DB_PASSWORD', null)
            ]
        ]
    ];
```
And add the following line in `bootstrap/app.php`

```php
$app->configure('database');
```

This is to enable Lumen to read other configurations other than the provided default ones.

In the case of adding the Service Provider. You must add it in the Register Providers section of `bootstrap/app.php`. You can add it like so:

```php
$app->register('Vinelab\NeoEloquent\NeoEloquentServiceProvider');
```

### Migration Setup

If you're willing to have migrations:

- create the folder `app/database/labels`
- modify `composer.json` and add `app/database/labels` to the `classmap` array
- run `composer dump-autoload`


### Documentation

## Models

- [Node Labels](#namespaced-models)
- [Soft Deleting](#soft-deleting)

```php
class User extends NeoEloquent {}
```

As simple as it is, NeoEloquent will generate the default node label from the class name,
in this case it will be `:User`. Read about [node labels here](http://docs.neo4j.org/chunked/stable/rest-api-node-labels.html)

### Namespaced Models
When you use namespaces with your models the label will consider the full namespace.

```php
namespace Vinelab\Cms;

class Admin extends NeoEloquent { }
```

The generated label from that relationship will be `VinelabCmsAdmin`, this is necessary to make sure
that labels do not clash in cases where we introduce another  `Admin` instance like
`Vinelab\Blog\Admin` then things gets messy with `:Admin` in the database.

### Custom Node Labels

You may specify the label(s) you wish to be used instead of the default generated, they are also
case sensitive so they will be stored as put here.

```php
class User extends NeoEloquent {

    protected $label = 'User'; // or array('User', 'Fan')

    protected $fillable = ['name', 'email'];
}

$user = User::create(['name' => 'Some Name', 'email' => 'some@email.com']);
```

NeoEloquent has a fallback support for the `$table` variable that will be used if found and there was no `$label` defined on the model.

```php
class User extends NeoEloquent {

    protected $table = 'User';

}
```

Do not worry about the labels formatting, You may specify them as `array('Label1', 'Label2')` or separate them by a column `:` and prepending them with a `:` is optional.

### Soft Deleting

#### Laravel 5

To enable soft deleting you'll need to `use Vinelab\NeoEloquent\Eloquent\SoftDeletes`
instead of `Illuminate\Database\Eloquent\SoftDeletes` and just like Eloquent you'll need the `$dates` in your models as follows:

```php
use Vinelab\NeoEloquent\Eloquent\SoftDeletes;

class User extends NeoEloquent {

    use SoftDeletes;

    protected $dates = ['deleted_at'];

}
```

#### Laravel 4

To enable soft deleting you'll need to `use Vinelab\NeoEloquent\Eloquent\SoftDeletingTrait`
instead of `Illuminate\Database\Eloquent\SoftDeletingTrait` and just like Eloquent you'll need the `$dates` in your models as follows:

```php
use Vinelab\NeoEloquent\Eloquent\SoftDeletingTrait;

class User extends NeoEloquent {

    use SoftDeletingTrait;

    protected $dates = ['deleted_at'];

}
```

## Relationships

- [One-To-One](#one-to-one)
- [One-To-Many](#one-to-many)
- [Many-To-Many](#many-to-many)
- [Polymorphic](#polymorphic)

Let's go through some examples of relationships between Nodes.

### One-To-One

```php
class User extends NeoEloquent {

    public function phone()
    {
        return $this->hasOne('Phone');
    }
```
This represents an `OUTGOING` relationship direction from the `:User` node to a `:Phone`.

##### Saving

```php
$phone = new Phone(['code' => 961, 'number' => '98765432'])
$relation = $user->phone()->save($phone);
```

The Cypher performed by this statement will be as follows:

```
MATCH (user:`User`)
WHERE id(user) = 1
CREATE (user)-[:PHONE]->(phone:`Phone` {code: 961, number: '98765432', created_at: 7543788, updated_at: 7543788})
RETURN phone;
```

##### Defining The Inverse Of This Relation

```php
class Phone extends NeoEloquent {

    public function user()
    {
        return $this->belongsTo('User');
    }
}
```

This represents an `INCOMING` relationship direction from
the `:User` node to this `:Phone` node.

##### Associating Models

Due to the fact that we do not deal with **foreign keys**, in our case it is much
more than just setting the foreign key attribute on the parent model. In Neo4j (and Graph in general) a relationship is an entity itself that can also have attributes of its own, hence the introduction of
[**Edges**](#Edges)

> *Note:* Associated models does not persist relations automatically when calling `associate()`.

```php
$account = Account::find(1986);

// $relation will be Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn
$relation = $user->account()->associate($account);

// Save the relation
$relation->save();
```

The Cypher performed by this statement will be as follows:

```
MATCH (account:`Account`), (user:`User`)
WHERE id(account) = 1986 AND id(user) = 9862
MERGE (account)<-[rel_user_account:ACCOUNT]-(user)
RETURN rel_user_account;
```

### One-To-Many

```php
class User extends NeoEloquent {

    public function posts()
    {
        return $this->hasMany('Post', 'POSTED');
    }
}
```

This represents an `OUTGOING` relationship direction
from the `:User` node to the `:Post` node.

```php
$user = User::find(1);
$post = new Post(['title' => 'The Title', 'body' => 'Hot Body']);
$user->posts()->save($post);
```

Similar to `One-To-One` relationships the returned value from a `save()` statement is an
`Edge[In|Out]`

The Cypher performed by this statement will be as follows:

```
MATCH (user:`User`)
WHERE id(user) = 1
CREATE (user)-[rel_user_post:POSTED]->(post:`Post` {title: 'The Title', body: 'Hot Body', created_at: '15-05-2014', updated_at: '15-05-2014'})
RETURN rel_user_post;
```

##### Defining The Inverse Of This Relation

```php
class Post extends NeoEloquent {

    public function author()
    {
        return $this->belongsTo('User', 'POSTED');
    }
}
```

This represents an `INCOMING` relationship direction from
the `:User` node to this `:Post` node.

### Many-To-Many

```php
class User extends NeoEloquent {

    public function followers()
    {
        return $this->belongsToMany('User', 'FOLLOWS');
    }
}
```

This represents an `INCOMING` relationship between a `:User` node and another `:User`.

```php
$jd = User::find(1012);
$mc = User::find(1013);
```

`$jd` follows `$mc`:

```php
$jd->followers()->save($mc);
```

Or using the `attach()` method:

```php
$jd->followers()->attach($mc);
// Or..
$jd->followers()->attach(1013); // 1013 being the id of $mc ($mc->getKey())
```

The Cypher performed by this statement will be as follows:

```
MATCH (user:`User`), (followers:`User`)
WHERE id(user) = 1012 AND id(followers) = 1013
CREATE (followers)-[:FOLLOWS]->(user)
RETURN rel_follows;
```

`$mc` follows `$jd` back:

```php
$mc->followers()->save($jd);
```

The Cypher performed by this statement will be as follows:

```
MATCH (user:`User`), (followers:`User`)
WHERE id(user) = 1013 AND id(followers) = 1012
CREATE (user)-[rel_user_followers:FOLLOWS]->(followers)
RETURN rel_follows;
```

get the followers of `$jd`

```php
$followers = $jd->followers;
```

The Cypher performed by this statement will be as follows:

```
MATCH (user:`User`), (followers:`User`), (user)-[rel_user_followers:FOLLOWS]-(followers)
WHERE id(user) = 1012
RETURN rel_follows;
```

### Dynamic Properties

```php
class Phone extends NeoEloquent {

    public function user()
    {
        return $this->belongsTo('User');
    }

}

$phone = Phone::find(1006);
$user = $phone->user;
// or getting an attribute out of the related model
$name = $phone->user->name;
```

### Polymorphic

The concept behind Polymorphic relations is purely relational to the bone but when it comes
to graph we are representing it as a [HyperEdge](http://docs.neo4j.org/chunked/stable/cypher-cookbook-hyperedges.html).

Hyper edges involves three models, the **parent** model, **hyper** model and **related** model
represented in the following figure:

![HyperEdges](https://googledrive.com/host/0BznzZ2lBbT0cLW9YcjNldlJkcXc/HyperEdge.png "HyperEdges")

Similarly in code this will be represented by three models `User` `Comment` and `Post`
where a `User` with id 1 posts a `Post` and a `User` with id 6 `COMMENTED` a `Comment` `ON` that `Post`
as follows:

```php
class User extends NeoEloquent {

    public function comments($morph = null)
    {
        return $this->hyperMorph($morph, 'Comment', 'COMMENTED', 'ON');
    }

}
```

In order to keep things simple but still involving the three models we will have to pass the
`$morph` which is any `commentable` model, in our case it's either a `Video` or a `Post` model.

> **Note:** Make sure to have it defaulting to `null` so that we can Dynamicly or Eager load
with `$user->comments` later on.

Creating a `Comment` with the `create()` method.

```php
$user = User::find(6);
$post = Post::find(2);

$user->comments($post)->create(['text' => 'Totally agree!', 'likes' => 0, 'abuse' => 0]);
```

As usual we will have returned an Edge, but this time it's not directed it is an instance of
`HyperEdge`, read more about [HyperEdges here](#hyperedge).

Or you may save a Comment instance:

```php
$comment = new Comment(['text' => 'Magnificent', 'likes' => 0, 'abuse' => 0]);

$user->comments($post)->save($comment);
```

Also all the functionalities found in a `BelongsToMany` relationship are supported like
attaching models by Ids:

```php
$user->comments($post)->attach([$id, $otherId]);
```

Or detaching models:

```php
$user->comments($post)->detach($comment); // or $comment->id
```

Sync too:

```php
$user->comments($post)->sync([$id, $otherId, $someId]);
```

#### Retrieving Polymorphic Relations

From our previous example we will use the `Video` model to retrieve their comments:

```php
class Video extends NeoEloquent {

    public function comments()
    {
        return $this->morphMany('Comment', 'ON');
    }

}
```

##### Dynamicly Loading Morph Model

```php
$video = Video::find(3);
$comments = $video->comments;
```

##### Eager Loading Morph Model

```php
$video = Video::with('comments')->find(3);
foreach ($video->comments as $comment)
{
    //
}
```

#### Retrieving The Inverse of a Polymorphic Relation

```php
class Comment extends NeoEloquent {

    public function commentable()
    {
        return $this->morphTo();
    }

}
```

```php
$postComment = Comment::find(7);
$post = $comment->commentable;

$videoComment = Comment::find(5);
$video = $comment->commentable;

// You can also eager load them
Comment::with('commentable')->get();
```

You may also specify the type of morph you would like returned:

```php
class Comment extends NeoEloquent {

    public function post()
    {
        return $this->morphTo('Post', 'ON');
    }

    public function video()
    {
        return $this->morphTo('Video', 'ON');
    }

}
```

#### Polymorphic Relations In Short

To drill things down here's how our three models involved in a Polymorphic relationship connect:

```php
class User extends NeoEloquent {

    public function comments($morph = null)
    {
        return $this->hyperMorph($morph, 'Comment', 'COMMENTED', 'ON');
    }

}
```

```php
class Post extends NeoEloquent { // Video is the same as this one

    public function comments()
    {
        return $this->morphMany('Comment', 'ON');
    }

}
```

```php
class Comment extends NeoEloquent {

    public function commentable()
    {
        return $this->morphTo();
    }

}

```

### Eager Loading

```php
class Book extends NeoEloquent {

    public function author()
    {
        return $this->belongsTo('Author');
    }
}
```

Loading authors with their books with the least performance overhead possible.

```php
foreach (Book::with('author')->get() as $book)
{
    echo $book->author->name;
}
```

Only two Cypher queries will be run in the loop above:

```
MATCH (book:`Book`) RETURN *;

MATCH (book:`Book`), (book)<-[:WROTE]-(author:`Author`) WHERE id(book) IN [1, 2, 3, 4, 5, ...] RETURN book, author;
```

## Edges

- [EdgeIn](#edgein)
- [EdgeOut](#edgeout)
- [HyperEdge](#hyperedge)
- [Working with Edges](#working-with-edges)
- [Edge Attributes](#edge-attributes)

### Introduction

Due to the fact that relationships in Graph are much different than other database types so
we will have to handle them accordingly. Relationships have directions that can vary between
**In** and **Out** respectively towards the parent node.

#### EdgeIn

Represents an `INCOMING` direction relationship from the related model towards the parent model.

```php
class Location extends NeoEloquent {

    public function user()
    {
        return $this->belongsTo('User', 'LOCATED_AT');
    }

}
```

To associate a `User` to a `Location`:

```php
$location = Location::find(1922);
$user = User::find(3876);
$relation = $location->associate($user);
```

which in Cypher land will map to `(:Location)<-[:LOCATED_AT]-(:User)` and `$relation`
being an instance of `EdgeIn` representing an incoming relationship towards the parent.

And you can still access the models from the edge:

```php
$relation = $location->associate($user);
$location = $relation->parent();
$user = $relation->related();
```

#### EdgeOut

Represents an `OUTGOING` direction relationship from the parent model to the related model.

```php
class User extends NeoEloquent {

    public function posts()
    {
        return $this->hasMany('Post', 'POSTED');
    }

}
```

To save an outgoing edge from `:User` to `:Post` it goes like:

```php
$post = new Post(['...']);
$posted = $user->posts()->save($post);
```

Which in Cypher would be `(:User)-[:POSTED]->(:Post)` and `$posted` being the `EdgeOut` instance.

And fetch the related models:

```php
$edge = $user->posts()->save($post);
$user = $edge->parent();
$post = $edge->related();
```

#### HyperEdge

This edge comes as a result of a [Polymorphic Relation](#polymorphic) representing an edge involving
two other edges **left** and **right** that can be accessed through the `left()` and `right()` methods.

This edge is treated a bit different than the others since it is not a direct relationship
between two models which means it has no specific direction.

```php
$edge = $user->comments($post)->attach($comment);
// Access the left and right edges
$left = $edge->left();
$user = $left->parent();
$comment = $left->related();

$right = $edge->right();
$comment = $right->parent();
$post = $right->related();
```

### Working With Edges

As stated earlier **Edges** are entities to Graph unlike *SQL* where they are a matter of a
foreign key having the value of the parent model as an attribute on the belonging model or in
*Documents* where they are either embeds or ids as references. So we developed them to be *light
models* which means you can work with them as if you were working with an `Eloquent` instance - to a certain extent,
except [HyperEdges](#hyperedges).

```php
// Create a new relationship
$relation = $location->associate($user); // Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn

// Save the relationship to the database
$relation->save(); // true
```

In the case of a `HyperEdge` you can access all three models as follows:

```php
$edge    = $user->comments($post)->save($comment);
$user    = $edge->parent();
$comment = $edge->hyper();
$post    = $edge->related();
```

#### Edge Attributes

By default, edges will have the timestamps `created_at` and `updated_at` automatically set and updated **only if** timestamps are enabled by setting `$timestamps` to `true`
on the parent model.

```php
$located_at = $location->associate($user);
$located_at->since = 1966;
$located_at->present = true;
$located_at->save();

// $created_at and $updated_at are Carbon\Carbon instances
$created_at = $located_at->created_at;
$updated_at = $located_at->updated_at;
```

##### Retrieve an Edge from a Relation

The same way an association will create an `EdgeIn` relationship we can retrieve
the edge between two models by calling the `edge($model)` method on the `belongsTo`
relationship.

```php
$location = Location::find(1892);
$edge = $location->user()->edge();
```

You may also specify the model at the other side of the edge.

> Note: By default NeoEloquent will try to perform the `$location->user` internally to figure
out the related side of the edge based on the relation function name, in this case it's
`user()`.

```php
$location = Location::find(1892);
$edge = $location->user()->edge($location->user);
```

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


## Migration
For migrations to work please perform the following:

- create the folder `app/database/labels`
- modify `composer.json` and add `app/database/labels` to the `classmap` array

Since Neo4j is a schema-less database you don't need to predefine types of properties for labels.
However you will be able to perform [Indexing](http://neo4j.com/docs/stable/query-schema-index.html) and [Constraints](http://neo4j.com/docs/stable/query-constraints.html) using NeoEloquent's pain-less [Schema](#schema).

#### Commands
NeoEloquent introduces new commands under the `neo4j` namespace so you can still use Eloquent's migration commands side-by-side.

Migration commands are the same as those of Eloquent, in the form of `neo4j:migrate[:command]`

    neo4j:make:migration                 Create a new migration file
    neo4j:migrate                        Run the database migrations
    neo4j:migrate:reset                  Rollback all database migrations
    neo4j:migrate:refresh                Reset and re-run all migrations
    neo4j:migrate:rollback               Rollback the last database migration


### Creating Migrations

Like in Laravel you can create a new migration by using the `make` command with Artisan:

    php artisan neo4j:migrate:make create_user_label

Label migrations will be placed in `app/database/labels`

You can add additional options to commands like:

    php artisan neo4j:migrate:make foo --path=app/labels
    php artisan neo4j:migrate:make create_user_label --create=User
    php artisan neo4j:migrate:make create_user_label --label=User


### Running Migrations

##### Run All Outstanding Migrations

    php artisan neo4j:migrate

##### Run All Outstanding Migrations For A Path

    php artisan neo4j:migrate --path=app/foo/labels

##### Run All Outstanding Migrations For A Package

    php artisan neo4j:migrate --package=vendor/package

>Note: If you receive a "class not found" error when running migrations, try running the `composer dump-autoload` command.

#### Forcing Migrations In Production

To force-run migrations on a production database you can use:

    php artisan neo4j:migrate --force

### Rolling Back Migrations

##### Rollback The Last Migration Operation

    php artisan neo4j:migrate:rollback

##### Rollback all migrations

    php artisan neo4j:migrate:reset

##### Rollback all migrations and run them all again

    php artisan neo4j:migrate:refresh

    php artisan neo4j:migrate:refresh --seed

## Schema
NeoEloquent will alias the `Neo4jSchema` facade automatically for you to be used in manipulating labels.

```php
Neo4jSchema::label('User', function(Blueprint $label)
{
    $label->unique('uuid');
});
```

If you decide to write Migration classes manually (not using the generator) make sure to have these `use` statements in place:

- `use Vinelab\NeoEloquent\Schema\Blueprint;`
- `use Vinelab\NeoEloquent\Migrations\Migration;`

Currently Neo4j supports `UNIQUE` constraint and `INDEX` on properties. You can read more about them at

<http://docs.neo4j.org/chunked/stable/graphdb-neo4j-schema.html>

#### Schema Methods

Command                           | Description
------------                      | -------------
`$label->unique('email')`           | Adding a unique constraint on a property
`$label->dropUnique('email')`       | Dropping a unique constraint from property
`$label->index('uuid')`           | Adding index on property
`$label->dropIndex('uuid')`       | Dropping index from property

### Droping Labels

```php
Neo4jSchema::drop('User');
Neo4jSchema::dropIfExists('User');
```

### Renaming Labels

```php
Neo4jSchema::renameLabel($from, $to);
```

### Checking Label's Existence

```php
if (Neo4jSchema::hasLabel('User')) {

} else {

}
```

### Checking Relation's Existence

```php
if (Neo4jSchema::hasRelation('FRIEND_OF')) {

} else {

}
```

You can read more about migrations and schema on:

<http://laravel.com/docs/schema>

<http://laravel.com/docs/migrations>

## Aggregates

In addition to the Eloquent builder aggregates, NeoEloquent also has support for
Neo4j specific aggregates like *percentile* and *standard deviation*, keeping the same
function names for convenience.
Check [the docs](http://docs.neo4j.org/chunked/stable/query-aggregation.html) for more.

> `table()` represents the label of the model

```
$users = DB::table('User')->count();

$distinct = DB::table('User')->countDistinct('points');

$price = DB::table('Order')->max('price');

$price = DB::table('Order')->min('price');

$price = DB::table('Order')->avg('price');

$total = DB::table('User')->sum('votes');

$disc = DB::table('User')->percentileDisc('votes', 0.2);

$cont = DB::table('User')->percentileCont('votes', 0.8);

$deviation = DB::table('User')->stdev('sex');

$population = DB::table('User')->stdevp('sex');

$emails = DB::table('User')->collect('email');
```

## Changelog
Check the [Releases](https://github.com/Vinelab/NeoEloquent/releases) for details.

## Avoid

Here are some constraints and Graph-specific gotchas, a list of features that are either not supported or not recommended.

### JOINS :confounded:

- They make no sense for Graph, plus Graph hates them!
Which makes them unsupported on purpose. If migrating from an `SQL`-based app
they will be your boogie monster.

### Pivot Tables in Many-To-Many Relationships
This is not supported, instead we will be using [Edges](#edges) to work with relationships between models.

### Nested Arrays and Objects

- Due to the limitations imposed by the objects map types that can be stored in a single,
you can never have nested *arrays* or *objects* in a single model,
make sure it's flat. *Example:*

```php
// Don't
User::create(['name' => 'Some Name', 'location' => ['lat' => 123, 'lng'=> -123 ] ]);
```

Check out the [createWith()](#createwith) method on how you can achieve this in a Graph way.

## Tests

- install a Neo4j instance and run it with the default configuration `localhost:7474`
- make sure the database graph is empty to avoid conflicts
- after running `composer install` there should be `/vendor/bin/phpunit`
- run `./vendor/bin/phpunit` after making sure that the Neo4j instance is running

> Tests marked as incomplete means they are either known issues or non-supported features,
check included messages for more info.
