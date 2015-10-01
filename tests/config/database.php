<?php

return [

    'default' => 'default',

    'connections' => [

        'neo4j' => [
            'driver' => 'neo4j',
            'host' => 'localhost',
            'port' => 7474,
            'username' => 'neo4j',
            'password' => 'neoeloquent',
        ],

        'default' => [
            'driver' => 'neo4j',
            'username' => 'neo4j',
            'password' => 'neoeloquent',
        ],
    ],
];
