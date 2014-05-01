> **This package is still in the very early development phase, do not use it in production.**

# NeoEloquent

Neo4j Graph Eloquent Driver for Laravel 4

## Models

```php
<?php namespace Vinelab;

class User extends \NeoEloquent {

    // this will be the node's label
    // the default will be the namespaced class name
    // in this case the default is 'VinelabUser'
    protected $label = 'User:Fan'; // or array('User', 'Fan')

    // you may also specify the $table instead
    protected $table = 'User';

    protected $fillable = ['name'];
}

```

### Constraints & Graph-Specific Gotchas

#### Nested Arrays and Objects

Due to the limitations imposed by the objects map types that can be stored in a single Neo4j node,
you can never have nested *arrays* or *objects* in a single `Model`,
make sure it's flattened. *Example:*

```php
// Don't
User::create(['name' => 'Some Name', 'location' => ['lat' => 123, 'lng'=> -123 ] ]);
```
