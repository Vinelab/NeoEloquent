# Movie - NeoEloquent Example
Illustrating the all-time favourite [movie example from the Neo4j docs](http://neo4j.com/docs/stable/cypherdoc-movie-database.html)

### How to Run
- Start with running this Cypher in your database to fill it up with some data:

```cypher
CREATE (matrix1:Movie { title : 'The Matrix', year : '1999-03-31' })
CREATE (matrix2:Movie { title : 'The Matrix Reloaded', year : '2003-05-07' })
CREATE (matrix3:Movie { title : 'The Matrix Revolutions', year : '2003-10-27' })
CREATE (keanu:Actor { name:'Keanu Reeves' })
CREATE (laurence:Actor { name:'Laurence Fishburne' })
CREATE (carrieanne:Actor { name:'Carrie-Anne Moss' })
CREATE (keanu)-[:ACTS_IN { role : 'Neo' }]->(matrix1)
CREATE (keanu)-[:ACTS_IN { role : 'Neo' }]->(matrix2)
CREATE (keanu)-[:ACTS_IN { role : 'Neo' }]->(matrix3)
CREATE (laurence)-[:ACTS_IN { role : 'Morpheus' }]->(matrix1)
CREATE (laurence)-[:ACTS_IN { role : 'Morpheus' }]->(matrix2)
CREATE (laurence)-[:ACTS_IN { role : 'Morpheus' }]->(matrix3)
CREATE (carrieanne)-[:ACTS_IN { role : 'Trinity' }]->(matrix1)
CREATE (carrieanne)-[:ACTS_IN { role : 'Trinity' }]->(matrix2)
CREATE (carrieanne)-[:ACTS_IN { role : 'Trinity' }]->(matrix3)
```

- In the terminal `cd` into this example's directory: `cd Examples/Movies`
- Run `composer install`
- Run `php start.php`

### About the Code
The code is inside the `start.php` file.

Using [composer](http://getcomposer.org) all classes inside `models/` are autoloaded so in case you wanted to play around with this example make sure to run `composer dump-autoload` after you add classes and before you run the example again.

As for customizing configuration check `config/database.php` and modify the `$config` array as you wish.
