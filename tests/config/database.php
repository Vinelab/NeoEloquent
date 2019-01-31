<?php

return array(

    'default' => 'default',

    'connections' => array(
        'neo4j' => array(
            'name' => 'neo4j',
            'driver' => 'neo4j',
            'host' => 'localhost',
            'port' => 7474,
            'username' => 'neo4j',
            'password' => 'neo4j'
        ),

        'default' => array(
            'name' => 'default',
            'driver' => 'neo4j',
            'host' => 'localhost',
            'port' => 7474,
            'username' => '',
            'password' => ''
        ),
        'sqlite' => [
            'name' => 'sqlite',
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ],
    )
);
