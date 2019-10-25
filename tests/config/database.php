<?php

return [

    'default' => 'default',

    'connections' => [

        'neo4j' => [
            'driver'   => 'neo4j',
            'host'     => 'localhost',
            'port'     => 7474,
            'user'     => 'neo4j',
            'password' => 'neo4j',
        ],

        'default' => [
            'driver'   => 'neo4j',
            'host'     => 'localhost',
            'port'     => 7474,
            'user'     => '',
            'password' => '',
        ],
    ],
];
