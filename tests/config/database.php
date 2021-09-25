<?php

return array(

    'default' => 'bolt+routing',

    'connections' => array(

        'neo4j' => array(
            'driver' => 'neo4j',
            'host' => 'neo4j',
            'port' => 7687,
            'username' => 'neo4j',
            'password' => 'test',
        ),

        'default' => array(
            'driver' => 'neo4j',
            'host' => 'neo4j',
            'port' => 7687,
            'username' => 'neo4j',
            'password' => 'test',
        ),
    ),
);
