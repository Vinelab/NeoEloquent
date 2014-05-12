<?php namespace Vinelab\NeoEloquent\Tests\Eloquent;

use Mockery as M, DB;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Builder;

class EloquentBuilderTest extends TestCase {

    public function setUp()
    {
        parent::setUp();

        $this->query = M::mock('Illuminate\Database\Query\Builder');
        $this->model = M::mock('Vinelab\NeoEloquent\Eloquent\Model');

        $this->builder = new Builder($this->query);
    }

    public function testFindingById()
    {
        $resultSet = M::mock('Everyman\Query\ResultSet');

        $this->query->shouldReceive('where')->once()->with('id(n)', '=', 1);
        $this->query->shouldReceive('from')->once()->with('Model')->andReturn(array('Model'));
        $this->query->shouldReceive('take')->once()->with(1)->andReturn($this->query);
        $this->query->shouldReceive('get')->once()->with(array('*'))->andReturn($resultSet);

        $resultSet->shouldReceive('valid')->once()->andReturn(true);

        $this->model->shouldReceive('getKeyName')->once()->andReturn('id');
        $this->model->shouldReceive('getTable')->once()->andReturn('Model');
        $this->model->shouldReceive('getConnectionName')->once()->andReturn('default');

        $collection = new \Illuminate\Support\Collection(array(M::mock('Everyman\Query\ResultSet')));
        $this->model->shouldReceive('newCollection')->once()->andReturn($collection);

        $this->builder->setModel($this->model);

        $result = $this->builder->find(1);

        $this->assertInstanceOf('Everyman\Query\ResultSet', $result);
    }

    public function testFindingByIdWithProperties()
    {
        // the intended Node id
        $id = 6;

        // the expected result set
        $result = array(

            'id'    => $id,
            'name'  => 'Some Name',
            'email' => 'some@mail.net'
        );

        // the properties that we need returned of our model
        $properties = array('id', 'name', 'email');

        $resultSet = $this->createNodeResultSet($result, $properties);

        // usual query expectations
        $this->query->shouldReceive('where')->once()->with('id(n)', '=', $id)
                    ->shouldReceive('take')->once()->with(1)->andReturn($this->query)
                    ->shouldReceive('get')->once()->with($properties)->andReturn($resultSet)
                    ->shouldReceive('from')->once()->with('Model')
                        ->andReturn(array('Model'));

        // our User object that we expect to have returned
        $user = M::mock('User');
        $user->shouldReceive('setConnection')->once()->with('default');

        // model method calls expectations
        $attributes = array_merge($result, array('id' => $id));

        // the Collection that represents the returned result by Eloquent holding the User as an item
        $collection = new \Illuminate\Support\Collection(array($user));

        $this->model->shouldReceive('newCollection')->once()->andReturn($collection)
                    ->shouldReceive('getKeyName')->twice()->andReturn('id')
                    ->shouldReceive('getTable')->once()->andReturn('Model')
                    ->shouldReceive('getConnectionName')->once()->andReturn('default')
                    ->shouldReceive('newFromBuilder')->once()->with($attributes)->andReturn($user);


        // assign the builder's $model to our mock
        $this->builder->setModel($this->model);

        // put things to the test
        $found = $this->builder->find($id, $properties);

        $this->assertInstanceOf('User', $found);
    }

    public function testGettingModels()
    {
        // the expected result set
        $results = array(

            array(

                'id'    => 10,
                'name'  => 'Some Name',
                'email' => 'some@mail.net'
            ),

            array(

                'id'    => 11,
                'name'  => 'Another Person',
                'email' => 'person@diff.io'
            )

        );

        $resultSet = $this->createNodeResultSet($results);

        $this->query->shouldReceive('get')->once()->with(array('*'))->andReturn($resultSet)
                    ->shouldReceive('from')->once()->andReturn('User');

        // our User object that we expect to have returned
        $user = M::mock('User');
        $user->shouldReceive('setConnection')->twice()->with('default');

        $this->model->shouldReceive('getTable')->once()->andReturn('User')
                    ->shouldReceive('getKeyName')->twice()->andReturn('id')
                    ->shouldReceive('getConnectionName')->once()->andReturn('default')
                    ->shouldReceive('newFromBuilder')->once()
                        ->with($results[0])->andReturn($user)
                    ->shouldReceive('newFromBuilder')->once()
                        ->with($results[1])->andReturn($user);

        $this->builder->setModel($this->model);

        $models = $this->builder->getModels();

        $this->assertInternalType('array', $models);
        $this->assertInstanceOf('User', $models[0]);
        $this->assertInstanceOf('User', $models[1]);
    }

    public function testGettingModelsWithProperties()
    {
        // the expected result set
        $results = array(
            'id'    => 138,
            'name'  => 'Nicolas Jaar',
            'email' => 'noise@space.see'
        );

        $properties = array('id', 'name');

        $resultSet = $this->createNodeResultSet($results);

        $this->query->shouldReceive('get')->once()->with($properties)->andReturn($resultSet)
                    ->shouldReceive('from')->once()->andReturn('User');

        // our User object that we expect to have returned
        $user = M::mock('User');
        $user->shouldReceive('setConnection')->once()->with('default');

        $this->model->shouldReceive('getTable')->once()->andReturn('User')
                    ->shouldReceive('getKeyName')->once()->andReturn('id')
                    ->shouldReceive('getConnectionName')->once()->andReturn('default')
                    ->shouldReceive('newFromBuilder')->once()
                        ->with($results)->andReturn($user);

        $this->builder->setModel($this->model);

        $models = $this->builder->getModels($properties);

        $this->assertInternalType('array', $models);
        $this->assertInstanceOf('User', $models[0]);
    }

    public function testExtractingPropertiesFromNode()
    {
        $properties = array(
            'id'         => 911,
            'skin'       => 'white',
            'username'   => 'eminem',
            'occupation' => 'white nigga'
        );

        $row = $this->createRowWithNodeAtIndex(0, $properties);
        $row->shouldReceive('current')->once()->andReturn($row->offsetGet(0));

        $this->model->shouldReceive('getKeyName')->once()->andReturn('id');
        $this->model->shouldReceive('getTable')->once()->andReturn('Artist');

        $this->query->shouldReceive('from')->once()->andReturn('Artist');

        $this->builder->setModel($this->model);

        $attributes = $this->builder->getProperties($row);

        $this->assertEquals($properties, $attributes);
    }

    public function testExtractingPropertiesOfChosenColumns()
    {
        $properties = array(
            'id'    => 'mothafucka',
            'arms'  => 2,
            'legs'  => 2,
            'heads' => 1,
            'eyes'  => 2,
            'sex'   => 'male'
        );

        $row = $this->createRowWithPropertiesAtIndex(0, $properties);
        $row->shouldReceive('current')->once()->andReturn($row->offsetGet(0));

        $this->model->shouldReceive('getTable')->once()->andReturn('Human:Male');

        $this->query->columns = array('arms', 'legs');
        $this->query->shouldReceive('from')->once()->andReturn('Human:Male');

        $this->builder->setModel($this->model);

        $attributes = $this->builder->getProperties($row);

        $expected = array('arms' => $properties['arms'], 'legs' => $properties['legs']);

        $this->assertEquals($expected, $attributes);

    }


    /**
     *
     *     Utility methods down below this area
     *
     */


    /**
     * Create a new ResultSet out of an array of properties and values
     *
     * @param  array $data The values you want returned could be of the form
     *             [ [name => something, username => here] ]
     *             or specify the attributes straight in the array
     * @param  array $properties The expected properties (columns)
     * @return  Everyman\Neo4j\Query\ResultSet
     */
    public function createNodeResultSet($data = array(), $properties = array())
    {
        $c = DB::connection('default');

        $rows = array();

        if (is_array(reset($data)))
        {
            foreach ($data as $index => $node)
            {
                $rows[] = $this->createRowWithNodeAtIndex($index, $node);
            }

        } else {

            $rows[] = $this->createRowWithNodeAtIndex(0, $data);
        }

        // the ResultSet $result part
        $result = array(
            'data'    => $rows,
            'columns' => $properties
        );

        // create the result set
        return new \Everyman\Neo4j\Query\ResultSet($c->getClient(), $result);
    }

    /**
     * Get a row with a Node inside of it having $data as properties
     *
     * @param  integer $index The index of the node in the row
     * @param  array   $data
     * @return  \Everyman\Neo4j\Query\Row
     */
    public function createRowWithNodeAtIndex($index, array $data)
    {
        // create the result Node containing the properties and their values
        $node = M::mock('Everyman\Neo4j\Node');

        // the Node id is never returned with the properties so in case
        // that is one of the data properties we need to remove it
        // and add it to when requested through getId()
        if (isset($data['id']))
        {
            $node->shouldReceive('getId')->once()->andReturn($data['id']);

            unset($data['id']);
        }

        $node->shouldReceive('getProperties')->once()->andReturn($data);

        // create the result row that should contain the Node
        $row = M::mock('Everyman\Neo4j\Query\Row');
        $row->shouldReceive('offsetGet')->andReturn($node);

        return $row;
    }

    public function createRowWithPropertiesAtIndex($index, array $properties)
    {
        $row = M::mock('Everyman\Neo4j\Query\Row');
        $row->shouldReceive('offsetGet')->once()->with($index)->andReturn($properties);

        foreach($properties as $key => $value)
        {
            // prepare the row's offsetGet to rerturn the desired value when asked
            // by prepending the key with an n. representing the node in the Cypher query.
            $row->shouldReceive('offsetGet')
                ->between(0, 1)
                ->with("n.{$key}")
                ->andReturn($properties[$key]);
        }

        return $row;
    }

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }
}
