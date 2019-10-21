<?php

return array(

    'default' => 'neo4j',

    'connections' => array(

        'neo4j' => array(
            'driver' => 'neo4j',
            'host' => 'localhost',
            'port' => 7474,
            'user' => 'neo4j',
            'password' => 'neo4j'
        ),

        'default' => array(
            'driver' => 'neo4j',
            'host' => 'localhost',
            'port' => 7474,
            'user' => '',
            'password' => ''
        )
    )
);
