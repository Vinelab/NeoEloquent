<?php

return array(

    'default' => 'default',

    'connections' => array(
        'neo4j' => array(
            'name' => 'neo4j',
            'driver' => 'neo4j',
            'host' => 'localhost',
            'port' => 11002,
            'username' => 'neo4j',
            'password' => '123123'
        ),

        'default' => array(
            'name' => 'default',
            'driver' => 'neo4j',
            'host' => 'localhost',
            'port' => 11002,
            'username' => 'neo4j',
            'password' => '123123'
        ),
        'sqlite' => [
            'name' => 'sqlite',
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => ''
        ],
    )
);
