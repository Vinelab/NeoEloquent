<?php namespace Vinelab\NeoEloquent\Tests;

use PHPUnit_Framework_TestCase as PHPUnit;

class TestCase extends PHPUnit {

    public function __construct()
    {
        parent::__construct();

        // load custom configuration file
        $this->dbConfig = require 'config/database.php';
    }

}
