> **This package is still in the very early development phase, do not use it in production.**

[![Build Status](https://travis-ci.org/Vinelab/NeoEloquent.svg?branch=master)](https://travis-ci.org/Vinelab/NeoEloquent)

# NeoEloquent

Neo4j Graph Eloquent Driver for Laravel 4

## Models

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

    protected $label = 'User:Fan'; // or array('User', 'Fan')

    protected $fillable = ['name', 'email'];
}

$user = User::craete(['name', 'email']);
```

NeoEloquent has a fallback support for the `$table` variable that will be used if found and there was no `$label` defined on the model.

```php
class User extends NeoEloquent {

    protected $table = 'User:Fan';

}
```

Do not worry about the labels formatting, You may specify them as `array('Label1', 'Label2')` or separate them by a column `:` and prepending them with a `:` is optional.

## Relationships

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

```sql
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

```sql
MATCH (account:`Account`), (user:`User`)
WHERE id(account) = 1986 AND id(user) = 9862
MERGE (account)<-[rel_user_account:ACCOUNT]-(user)
RETURN rel_user_account;
```

The Cypher performed by this statement will be as follows:

```sql
MATCH (phone:Phone) (phone)<-[:PHONE]-(user:User)
WHERE id(phone) = 1006
RETURN user;
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

```sql
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

This represents an `OUTGOING` relationship between a `:User` node and another `:User`.

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
$jd->followers()->attach(1); // 1 being the id of $mc ($mc->getKey())
```

The Cypher performed by this statement will be as follows:

```sql
MATCH (user:`User`), (followers:`User`)
WHERE id(user) = 1012 AND id(followers) = 1013
CREATE (user)-[:FOLLOWS]->(followers)
RETURN rel_follows;
```

`$mc` follows `$jd` back:

```php
$mc->followers()->save($jd);
```

The Cypher performed by this statement will be as follows:

```sql
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

```sql
MATCH (user:`User`), (followers:`User`), (user)-[rel_user_followers:FOLLOWS]-(followers)
WHERE id(user) = 1012
RETURN rel_follows;
```

### Dynamic Properties

```php
class Phone extends Eloquent {

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

### Eager Loading

```php
class Book extends Eloquent {

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

```sql
MATCH (book:`Book`) RETURN *;

MATCH (book:`Book`), (book)<-[:WROTE]-(author:`Author`) WHERE id(book) IN [1, 2, 3, 4, 5, ...] RETURN book, author;
```

## Edges

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

### Working With Edges

As stated earlier **Edges** are entities to Graph unlike *SQL* where they are a matter of a foreign key having the value of the parent model as an attribute on the belonging model or in *Documents* where they are either embeds or ids as references. So we developed them to be *light models* which means you can work with them as if you were working with an `Eloquent` instance - to a certain extent.

```php
// Create a new relationship
$relation = $location->associate($user); // Vinelab\NeoEloquent\Eloquent\Edges\EdgeIn

// Save the relationship to the database
$relation->save(); // true
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

You may also specify the other side of the edge.

> Note: By default NeoEloquent will try to pefrorm the `$location->user` internally to figure
out the related side of the edge based on the relation function name, in this case it's
`user()`.

```php
$location = Location::find(1892);
$edge = $location->user()->edge($location->user);
```

## Avoid

Here are some constraints and Graph-specific gotchas, a list of features that are either not supported or not recommended.

### JOINS :confounded:

- They make no sense for Graph, plus Graph hates them!
Which makes them unsupported on purpose. If migrating from an `SQL`-based app
they will be your boogie monster.

### `_nodeId` property

- The `_node_id` property is reserverd, do not assign a property `_nodeId` to a Node unless you would like it to actually be the Node id,
`NeoEloquent` has a special case when dealing with node ids since it is treated differently
by the Neo4j client.

### Nested Arrays and Objects

- Due to the limitations imposed by the objects map types that can be stored in a single Node,
you can never have nested *arrays* or *objects* in a single model,
make sure it's flat. *Example:*

```php
// Don't
User::create(['name' => 'Some Name', 'location' => ['lat' => 123, 'lng'=> -123 ] ]);
```

Check out [Relationships](#relationships) and [Edges](#edges) on how you can achieve this in a Graph way.

## Tests

- install a Neo4j instance and run it with the default configuration `localhost:7474`
- make sure the database graph is empty to avoid conflicts
- after running `composer install` there should be `/vendor/bin/phpunit`
- run `./vendor/bin/phpunit` after making sure that the Neo4j instance is running

> Tests marked as incomplete means they are either known issues or non-supported features,
check included messages for more info.
