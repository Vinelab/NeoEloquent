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

### Custom Node Labels

You may specify the label(s) you wish to be used instead of the default generated.

```php
class User extends NeoEloquent {

    protected $label = 'User:Fan'; // or array('User', 'Fan')

    protected $fillable = ['name', 'email'];
}

$user = User::craete(['name', 'email']);
```

> Note: The `$table` is the fallback variable that will be used if found and there was no `$label`.

```php
class User extends NeoEloquent {
    protected $table = 'User:Fan';
}
```

Do not worry about the labels formatting, You may specify them as `array('Label1', 'Label2')` or separate them by a column `:`. Prepending them with a `:` is optional.

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
$phone = $user->phone()->save($phone);
```

The Cypher performed by this statement will be as follows:

```sql
MATCH (user:`User`)
WHERE id(user) = 1
MERGE (user)-[:PHONE]->(phone:`Phone` {code: 961, number: '98765432', created_at: 7543788, updated_at: 7543788})
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

##### Dynamic Loading

```php
$phone = Phone::find(1006);
$user = $phone->user;
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
$post = $user->posts()->save($post);
```

The Cypher performed by this statement will be as follows:

```sql
MATCH (user:`User`)
WHERE id(user) = 1
CREATE (user)-[rel_user_post:POSTED]->(post:`Post` {title: 'The Title', body: 'Hot Body', created_at: '15-05-2014', updated_at: '15-05-2014'})
RETURN post;
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

##### Dynamic Loading

```php
$post = Post::find(1011);
$autor = $post->author;
```

The Cypher performed by this statement will be as follows:

```sql
MATCH (post:`Post`), (post)<-[rel_post_author:POSTED]-(author:`User`)
WHERE id(post) = 1011
RETURN author;
```

### Manty-To-Many

```php
class User extends NeoEloquent {

    public function followers()
    {
        return $this->belongsToMany('User', 'FOLLOWS');
    }
}
```

This represents a `BIDIRECTIONAL` relationship
between the `:User` node itself.

```php
$jd = User::find(1012);
$mc = User::find(1013);
```

`$jd` follows `$mc`:

```php
$jd->followers()->save($mc);
```

The Cypher performed by this statement will be as follows:

```sql
MATCH (user:`User`), (followers:`User`)
WHERE id(user) = 1012 AND id(followers) = 1013
CREATE (user)-[rel_user_followers:FOLLOWS]->(followers)
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
## Avoid

Here are some constraints and Graph-specific gotchas.

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
you can never have nested *arrays* or *objects* in a single `Model`,
make sure it's flat. *Example:*

```php
// Don't
User::create(['name' => 'Some Name', 'location' => ['lat' => 123, 'lng'=> -123 ] ]);
```

Check out [#Relationships](#Relationships) on how you can achieve this.

# TODO
- test Boolean vs. 0 and 1.
- in getModels() the connection of the model should be set per model instead of getting
the connection name of $this->model (might introduce a bug where the connection of the model
    is not persisted over results).
- add support for AND, OR, XOR and NOT operators
- add support for [x] and [x..y] operators
