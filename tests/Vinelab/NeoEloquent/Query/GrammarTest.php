<?php namespace Vinelab\NeoEloquent\Tests\Query;

use Illuminate\Database\Query\Expression;
use Illuminate\Database\Query\Processors\Processor;
use Mockery as M;
use Vinelab\NeoEloquent\Query\Builder;
use Vinelab\NeoEloquent\Query\Grammars\Grammar;
use Vinelab\NeoEloquent\Tests\TestCase;

class GrammarTest extends TestCase {

	public function setUp()
    {
        parent::setUp();

        $this->grammar = new Grammar;
        $this->processor = new Processor;
    }

    public function tearDown()
    {
    	M::close();

    	parent::tearDown();
    }

    public function testGettingQueryParameterFromRegularValue()
    {
    	$p = $this->grammar->parameter('value');
    	$this->assertEquals('{value}', $p);
    }

    public function testGettingIdQueryParameter()
    {
    	$p = $this->grammar->parameter('id');
    	$this->assertEquals('{idn}', $p);
    }

    public function testGettingIdParameterWithQueryBuilder()
    {
    	$query = M::mock('Vinelab\NeoEloquent\Query\Builder');
    	$query->from = 'user';
    	$this->grammar->setQuery($query);
    	$this->assertEquals('{iduser}', $this->grammar->parameter('id'));

    	$query->from = 'post';
    	$this->assertEquals('{idpost}', $this->grammar->parameter('id'));

    	$anotherQuery = M::mock('Vinelab\NeoEloquent\Query\Builder');
    	$anotherQuery->from = 'crawler';
    	$this->grammar->setQuery($anotherQuery);
    	$this->assertEquals('{idcrawler}', $this->grammar->parameter('id'));
    }

    public function testGettingWheresParameter()
    {
    	$this->assertEquals('{confusing}', $this->grammar->parameter(['column' => 'confusing']));
    }

    public function testGettingExpressionParameter()
    {
    	$ex = new Expression('id');
    	$this->assertEquals('{idn}', $this->grammar->parameter($ex));
    }

    public function testPreparingLabel()
    {
    	$this->assertEquals(':`user`', $this->grammar->prepareLabels(['user']), 'case sensitive');
    	$this->assertEquals(':`User`:`Artist`:`Official`', $this->grammar->prepareLabels(['User', 'Artist', 'Official']), 'order');
    	$this->assertEquals(':`Photo`:`Media`', $this->grammar->prepareLabels([':Photo', 'Media']), 'intelligent with :');
    	$this->assertEquals(':`Photo`:`Media`', $this->grammar->prepareLabels(['Photo', ':Media']), 'even more intelligent with :');
    }

    public function testPreparingRelationName()
    {
    	$this->assertEquals('`rel_posted_post`:`POSTED`', $this->grammar->prepareRelation('POSTED', 'post'));
    }

    public function testNormalizingLabels()
    {
    	$this->assertEquals('labels_and_more', $this->grammar->normalizeLabels(':Labels:And:More'));
    	$this->assertEquals('labels_and_more', $this->grammar->normalizeLabels('Labels:And:more'));
    }

    public function testWrappingValue()
    {
    	$mConnection = M::mock('Vinelab\NeoEloquent\Connection');
    	$mConnection->shouldReceive('getClient');
    	$query = new Builder($mConnection, $this->grammar, $this->processor);

    	$this->assertEquals('n.value', $this->grammar->wrap('value'));

    	$query->from = ['user'];
    	$this->assertEquals('id(user)', $this->grammar->wrap('id'), 'Ids are treated differently');
    	$this->assertEquals('user.name', $this->grammar->wrap('name'));

    	$this->assertEquals('post.title', $this->grammar->wrap('post.title'), 'do not touch values with dots in them');
    }

    public function testValufying()
    {
    	$this->assertEquals("'val'", $this->grammar->valufy('val'));
    	$this->assertEquals("'\'va\\\l\''", $this->grammar->valufy("'va\l'"));
    	$this->assertEquals("'valu1', 'valu2', 'valu3'", $this->grammar->valufy(['valu1', 'valu2', 'valu3']));
    	$this->assertEquals('\'valu\\\1\', \'valu\\\'2\\\'\', \'val/u3\'', $this->grammar->valufy(['valu\1', "valu'2'", 'val/u3']));
    	$this->assertEquals('\'\\\u123\'', $this->grammar->valufy('\u123'));
    }

    public function testGeneratingNodeIdentifier()
    {
    	$this->assertEquals('n', $this->grammar->modelAsNode());
    	$this->assertEquals('user', $this->grammar->modelAsNode('User'));
    	$this->assertEquals('rock', $this->grammar->modelAsNode(['Rock', 'Paper', 'Scissors']));
    }

    public function testReplacingIdProperty()
    {
    	$this->assertEquals('idn', $this->grammar->getIdReplacement('id'));
    	$this->assertEquals('iduser', $this->grammar->getIdReplacement('id(user)'));

    	$query = M::mock('Vinelab\NeoEloquent\Query\Builder');
    	$query->from = 'cola';
    	$this->grammar->setQuery($query);

    	$this->assertEquals('idcola', $this->grammar->getIdReplacement('id'));
    	$this->assertEquals('iddd', $this->grammar->getIdReplacement('id(dd)'));
    }

}
