<?php

namespace Vinelab\NeoEloquent\Tests\Functional;

use Illuminate\Support\Facades\Schema;
use Vinelab\NeoEloquent\Tests\TestCase;

class SchemaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->getConnection()->affectingStatement('MATCH (x) DETACH DELETE x');
    }

    public function testHasColumn(): void
    {
        $this->assertFalse(Schema::hasColumn('User', 'email'));

        $this->getConnection()->affectingStatement('CREATE (:User {email: "test@test"})');

        $this->assertTrue(Schema::hasColumn('User', 'email'));
    }
}