<?php namespace Vinelab\NeoEloquent\Tests\Functional\AddDropLabels;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\SoftDeletingTrait;

class Labelwiz extends Model {

    protected $label = ':Labelwiz';

    protected $fillable = ['fiz', 'biz', 'triz'];
}

class Bar extends Model {
    protected $label = ':Bar';
    protected $fillable = ['prop'];
}

class Foo extends Model {
    protected $label = ':Foo';
    protected $fillable = ['prop'];
    public function bar()
    {
    return $this->hasOne('Vinelab\NeoEloquent\Tests\Functional\AddDropLabels\Bar', 'OWNS');
    }
}




class AddDropLabelsTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $resolver = M::mock('Illuminate\Database\ConnectionResolverInterface');
        $resolver->shouldReceive('connection')->andReturn($this->getConnectionWithConfig('default'));
        Labelwiz::setConnectionResolver($resolver);
    }

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }


    function testAddingDroppingSingleLabelOnNewModel()
    {
        //create a new model object
        $w = new Labelwiz([
            'fiz'  => 'foo',
            'biz'  => 'boo',
            'triz' => 'troo'
        ]);
        $this->assertTrue($w->save());

        //add the label
        $w->addLabels(array('Superuniqelabel1'));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($w->id);
        $this->assertTrue(in_array('Superuniqelabel1', $nLabels));

        //now drop the label
        $w->dropLabels(array('Superuniqelabel1'));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($w->id);
        $this->assertFalse(in_array('Superuniqelabel1', $nLabels));
    }


    function testAddingDroppingLabelsOnNewModel()
    {
        //create a new model object
        $w = new Labelwiz([
            'fiz'  => 'foo1',
            'biz'  => 'boo1',
            'triz' => 'troo1'
        ]);
        $this->assertTrue($w->save());

        //add the label
        $w->addLabels(array('Superuniqelabel3', 'Superuniqelabel4', 'a1'));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($w->id);

        $this->assertTrue(in_array('Superuniqelabel3', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel4', $nLabels));
        $this->assertTrue(in_array('a1', $nLabels));

        //now drop one of the labels
        $w->dropLabels(array('a1'));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($w->id);
        $this->assertFalse(in_array('a1', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel3', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel4', $nLabels));

        //now drop remaining labels
        $w->dropLabels(array('Superuniqelabel3', 'Superuniqelabel4'));
        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($w->id);
        $this->assertFalse(in_array('a1', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel3', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel4', $nLabels));
    }


    function testAddDroppLabelsRepeatedlyOnNewModel()
    {
        //create a new model object
        $w = new Labelwiz([
            'fiz'  => 'foo2',
            'biz'  => 'boo2',
            'triz' => 'troo2'
        ]);
        $this->assertTrue($w->save());

        //add the label
        $w->addLabels(array('Superuniqelabel5'));
        $w->addLabels(array('Superuniqelabel6'));
        $w->addLabels(array('Superuniqelabel7'));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($w->id);

        $this->assertTrue(in_array('Superuniqelabel5', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel6', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel7', $nLabels));

        //now drop repeatedly
        $w->dropLabels(array('Superuniqelabel5'));
        $w->dropLabels(array('Superuniqelabel6'));
        $w->dropLabels(array('Superuniqelabel7'));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($w->id);

        $this->assertFalse(in_array('Superuniqelabel5', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel6', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel7', $nLabels));
    }

    function testAddDropLabelsRepeatedlyOnNewModels()
    {
        //create a new model object
        $w1 = new Labelwiz([
            'fiz'  => 'foo3',
            'biz'  => 'boo3',
            'triz' => 'troo4'
        ]);
        $this->assertTrue($w1->save());

        //create a new model object
        $w2 = new Labelwiz([
            'fiz'  => 'foo4',
            'biz'  => 'boo4',
            'triz' => 'troo4'
        ]);
        $this->assertTrue($w2->save());

        //create a new model object
        $w3 = new Labelwiz([
            'fiz'  => 'foo5',
            'biz'  => 'boo5',
            'triz' => 'troo5'
        ]);
        $this->assertTrue($w3->save());

        //add the label in sequence
        $w1->addLabels(array('Superuniqelabel8'));
        $w2->addLabels(array('Superuniqelabel8'));
        $w3->addLabels(array('Superuniqelabel8'));

        //add the array of labels
        $w1->addLabels(array( 'Superuniqelabel9','Superuniqelabel10'));
        $w2->addLabels(array( 'Superuniqelabel9', 'Superuniqelabel10'));
        $w3->addLabels(array( 'Superuniqelabel9','Superuniqelabel10'));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($w1->id);

        $this->assertTrue(in_array('Superuniqelabel8', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel9', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel10', $nLabels));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($w2->id);
        $this->assertTrue(in_array('Superuniqelabel8', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel9', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel10', $nLabels));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($w3->id);
        $this->assertTrue(in_array('Superuniqelabel8', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel9', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel10', $nLabels));

        //drop the label in sequence
        $w1->dropLabels(array('Superuniqelabel8'));
        $w2->dropLabels(array('Superuniqelabel8'));
        $w3->dropLabels(array('Superuniqelabel8'));

        //drop the array of labels
        $w1->dropLabels(array('Superuniqelabel9','Superuniqelabel10'));
        $w2->dropLabels(array('Superuniqelabel9', 'Superuniqelabel10'));
        $w3->dropLabels(array('Superuniqelabel9','Superuniqelabel10'));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($w1->id);
        $this->assertFalse(in_array('Superuniqelabel8', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel9', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel10', $nLabels));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($w2->id);
        $this->assertFalse(in_array('Superuniqelabel8', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel9', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel10', $nLabels));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($w3->id);
        $this->assertFalse(in_array('Superuniqelabel8', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel9', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel10', $nLabels));
    }

    function testAddDropLabelsRepeatedlyOnModelsFoundById()
    {
        //create a new model object
        $w1 = new Labelwiz([
            'fiz'  => 'foo6',
            'biz'  => 'boo6',
            'triz' => 'troo6'
        ]);
        $this->assertTrue($w1->save());

        //create a new model object
        $w2 = new Labelwiz([
            'fiz'  => 'foo7',
            'biz'  => 'boo7',
            'triz' => 'troo7'
        ]);
        $this->assertTrue($w2->save());

        //create a new model object
        $w3 = new Labelwiz([
            'fiz'  => 'foo8',
            'biz'  => 'boo8',
            'triz' => 'troo8'
        ]);
        $this->assertTrue($w3->save());

        $f1 = Labelwiz::find($w1->id);
        $f2 = Labelwiz::find($w2->id);
        $f3 = Labelwiz::find($w3->id);

        //add the label in sequence
        $f1->addLabels(array('Superuniqelabel11'));
        $f2->addLabels(array('Superuniqelabel11'));
        $f3->addLabels(array('Superuniqelabel11'));

        //add the array of labels
        $f1->addLabels(array('Superuniqelabel12','Superuniqelabel13'));
        $f2->addLabels(array('Superuniqelabel12', 'Superuniqelabel13'));
        $f3->addLabels(array('Superuniqelabel12','Superuniqelabel13'));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($f1->id);

        $this->assertTrue(in_array('Superuniqelabel11', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel12', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel13', $nLabels));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($f2->id);
        $this->assertTrue(in_array('Superuniqelabel11', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel12', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel13', $nLabels));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($f3->id);
        $this->assertTrue(in_array('Superuniqelabel11', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel12', $nLabels));
        $this->assertTrue(in_array('Superuniqelabel13', $nLabels));

        //drop the label in sequence
        $f1->dropLabels(array('Superuniqelabel11'));
        $f2->dropLabels(array('Superuniqelabel11'));
        $f3->dropLabels(array('Superuniqelabel11'));

        //drop the array of labels
        $f1->dropLabels(array('Superuniqelabel12','Superuniqelabel13'));
        $f2->dropLabels(array('Superuniqelabel12', 'Superuniqelabel13'));
        $f3->dropLabels(array('Superuniqelabel12','Superuniqelabel13'));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($f1->id);
        $this->assertFalse(in_array('Superuniqelabel11', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel12', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel13', $nLabels));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($f2->id);
        $this->assertFalse(in_array('Superuniqelabel11', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel12', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel13', $nLabels));

        //get the labels using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($f3->id);
        $this->assertFalse(in_array('Superuniqelabel11', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel12', $nLabels));
        $this->assertFalse(in_array('Superuniqelabel13', $nLabels));
    }


    function testAddDropLabelsOnRelated()
    {
        //create related nodes
        $foo = Foo::createWith(['prop'=>'I am Foo'], ['bar'=>['prop'=>'I am Bar']]);
        //$this->assertTrue($foo->save());

        //now add labels on related node
        $foo->bar->addLabels(['SpecialLabel1']);
        $foo->bar->addLabels(['SpecialLabel2', 'SpecialLabel3', 'SpecialLabel4']);

        //get the Node using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($foo->bar->id);
        $this->assertTrue(in_array('SpecialLabel1', $nLabels));
        $this->assertTrue(in_array('SpecialLabel2', $nLabels));
        $this->assertTrue(in_array('SpecialLabel3', $nLabels));
        $this->assertTrue(in_array('SpecialLabel4', $nLabels));

        //now drop one label on related node
        $foo->bar->dropLabels(['SpecialLabel1']);

        //get the Node using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($foo->bar->id);
        $this->assertFalse(in_array('SpecialLabel1', $nLabels));
        $this->assertTrue(in_array('SpecialLabel2', $nLabels));
        $this->assertTrue(in_array('SpecialLabel3', $nLabels));
        $this->assertTrue(in_array('SpecialLabel4', $nLabels));

        //now drop anotherlabel on related node
        $foo->bar->dropLabels(['SpecialLabel2']);

        //get the Node using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($foo->bar->id);
        $this->assertFalse(in_array('SpecialLabel1', $nLabels));
        $this->assertFalse(in_array('SpecialLabel2', $nLabels));
        $this->assertTrue(in_array('SpecialLabel3', $nLabels));
        $this->assertTrue(in_array('SpecialLabel4', $nLabels));

        //now drop remaining labels on related node
        $foo->bar->dropLabels(['SpecialLabel3', 'SpecialLabel4']);

        //get the Node using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($foo->bar->id);
        $this->assertFalse(in_array('SpecialLabel1', $nLabels));
        $this->assertFalse(in_array('SpecialLabel2', $nLabels));
        $this->assertFalse(in_array('SpecialLabel3', $nLabels));
        $this->assertFalse(in_array('SpecialLabel4', $nLabels));
    }

    function testDroppingTableLabels()
    {
        $w1 = new Labelwiz([
            'fiz'  => 'foo6',
            'biz'  => 'boo6',
            'triz' => 'troo6'
        ]);
        $this->assertTrue($w1->save());

        $id = $w1->id;

        //now drop the main label Labelwiz
        $w1->dropLabels(['Labelwiz']);

        //get the Node using Everyman lib
        $nLabels = $this->getLabelsUsingEveryman($id);
        $this->assertFalse(in_array('Labelwiz', $nLabels));

        //now find by id should NOT work on this id using Labelwiz model
        $this->assertNull(Labelwiz::find($id));

        //remove this node using everyman as its not accessible to the model now
        //Or the cleanup process could not delete this node
        $connection = $this->getConnectionWithConfig('neo4j');
        $client = $connection->getClient();
        $client->deleteNode($client->getNode($id)) ;
    }

    /*
     * function getLabelsUsingEveryman()
     * this is used to get node labels of a given node id directly using everyman lib
     *
     */
    function getLabelsUsingEveryman($nodeId)
    {
        //get the labels using Everyman lib
        $connection = $this->getConnectionWithConfig('neo4j');
        $client = $connection->getClient();

        //check labels on w1
        $node = $client->getNode($nodeId);
        $this->assertNotNull($node); //it should exist
        $labels = $node->getLabels(); //get labels as array on the Everyman nodes
        $strLabels = array();
        foreach($labels as $lbl)
        {
            $strLabels[] = $lbl->getName();
        }
        return $strLabels;
	}
}
