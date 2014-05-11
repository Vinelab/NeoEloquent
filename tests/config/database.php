<?php

return array(

    'default' => 'default',

    'connections' => array(

        'neo4j' => array(
            'driver' => 'neo4j',
            'host' => 'localhost',
            'port' => 7474
        ),

        'default' => array(
            'driver' => 'neo4j'
        )
    )
);
