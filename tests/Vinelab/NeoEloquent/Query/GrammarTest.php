<?php

namespace Vinelab\NeoEloquent\Tests\Query;

use Illuminate\Database\Query\Expression;
use Mockery as M;
use Vinelab\NeoEloquent\Query\CypherGrammar;
use Vinelab\NeoEloquent\Tests\TestCase;

class GrammarTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->grammar = new CypherGrammar();
    }

    public function tearDown(): void
    {
        M::close();

        parent::tearDown();
    }

    public function testGettingQueryParameterFromRegularValue(): void
    {
        $p = $this->grammar->parameter('value');
        $this->assertEquals('$value', $p);
    }

    public function testGettingIdQueryParameter(): void
    {
        $p = $this->grammar->parameter('id');
        $this->assertEquals('$id', $p);

        $this->grammar->useLegacyIds();
        $p = $this->grammar->parameter('id');
        $this->assertEquals('$idn', $p);
    }

    public function testGettingExpressionParameter(): void
    {
        $ex = new Expression('id');
        $this->grammar->useLegacyIds();

        $this->assertEquals('$idn', $this->grammar->parameter($ex));
    }
}
