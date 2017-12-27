<?php namespace Vinelab\NeoEloquent\Tests\Functional\Events;

use Mockery as M;
use Vinelab\NeoEloquent\Tests\TestCase;
use Vinelab\NeoEloquent\Eloquent\Model;
use Vinelab\NeoEloquent\Eloquent\SoftDeletes;

class ModelEventsTest extends TestCase {

    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    public function testDispatchedEventsChainCallsObserverMethods()
    {
        OBOne::create(['name' => 'a']);

        $obOne = OBOne::first();

        $this->assertTrue($obOne->ob_creating_event);
        $this->assertTrue($obOne->ob_created_event);
        $this->assertTrue($obOne->ob_saving_event);
        $this->assertTrue($obOne->ob_saved_event);

        // find for deletion
        $obOne = OBOne::first();
        $obOne->delete();

        $this->assertTrue($obOne->ob_deleting_event);
        $this->assertTrue($obOne->ob_deleted_event);

        $obOne = OBOne::onlyTrashed()->first();
        $obOne->restore();

        $this->assertTrue($obOne->ob_restoring_event);
        $this->assertTrue($obOne->ob_restored_event);
    }

    public function testDispatchedEventsChainSetOnBoot()
    {
        User::create(['name' => 'a']);

        $user = User::first();

        $this->assertTrue($user->creating_event);
        $this->assertTrue($user->created_event);
        $this->assertTrue($user->saving_event);
        $this->assertTrue($user->saved_event);

        // find for deletion
        $user = User::first();
        $user->delete();

        $this->assertTrue($user->deleting_event);
        $this->assertTrue($user->deleted_event);

        $user = User::onlyTrashed()->first();
        $user->restore();

        $this->assertTrue($user->restoring_event);
        $this->assertTrue($user->restored_event);
    }

    public function testCreateWithDispatchesEventsOnModelAndRelations()
    {
        $user = User::createWith(['name' => 'user name'], [
            'singleCake' => ['name' => 'single'],
            'cakes' => [
                ['name' => 'c 1'],
                ['name' => 'c 2'],
            ],
        ]);
        $this->assertTrue($user->creating_event);
        $this->assertTrue($user->created_event);
        $this->assertTrue($user->saving_event);
        $this->assertTrue($user->saved_event);
        $this->assertTrue($user->singleCake->creating_event);
        $this->assertTrue($user->singleCake->created_event);
        $this->assertTrue($user->singleCake->saving_event);
        $this->assertTrue($user->singleCake->saved_event);
        foreach ($user->cakes as $cake) {
            $this->assertTrue($cake->creating_event);
            $this->assertTrue($cake->created_event);
            $this->assertTrue($cake->saving_event);
            $this->assertTrue($cake->saved_event);
        }
    }
}

class User extends Model {

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $label = 'User';

    protected $fillable = [
        'name',
        'creating_event',
        'created_event',
        'updating_event',
        'updated_event',
        'saving_event',
        'saved_event',
        'deleting_event',
        'deleted_event',
        'restoring_event',
        'restored_event'
    ];

    // Will hold the events and their callbacks
    protected static $listenerStub = [];

    public function cakes()
    {
        return $this->hasMany('Vinelab\NeoEloquent\Tests\Functional\Events\Cake', 'HAS_CAKE');
    }
    public function singleCake()
    {
        return $this->hasOne('Vinelab\NeoEloquent\Tests\Functional\Events\Cake', 'HAS_SINGLE_CAKE');
    }


    public static function boot()
    {
        // Mock a dispatcher
        $dispatcher = M::mock('Illuminate\Events\dispatcher');
        $dispatcher->shouldReceive('listen')->andReturnUsing(function($event, $callback)
        {
            static::$listenerStub[$event] = $callback;
        });
        $dispatcher->shouldReceive('until')->andReturnUsing(function($event, $model){
            if (isset(static::$listenerStub[$event])) call_user_func(static::$listenerStub[$event], $model);
        });
        $dispatcher->shouldReceive('fire')->andReturnUsing(function($event, $model)
        {
            if (isset(static::$listenerStub[$event])) call_user_func(static::$listenerStub[$event], $model);
        });

        static::$dispatcher = $dispatcher;

        // boot up model
        parent::boot();

        User::creating(function($user)
        {
            $user->creating_event = true;
        });

        User::created(function($user)
        {
            $user->created_event = true;
            $user->save();
        });

        User::saving(function($user)
        {
            $user->saving_event = true;
        });

        User::saved(function($user)
        {
            if ( ! $user->saved_event)
            {
                $user->saved_event = true;
                $user->save();
            }
        });

        User::deleting(function($user)
        {
            $user->deleting_event = true;
        });

        User::deleted(function($user)
        {
            $user->deleted_event = true;
            $user->save();
        });

        User::restoring(function($user)
        {
            $user->restoring_event = true;
        });

        User::restored(function($user)
        {
            $user->restored_event = true;
            $user->save();
        });
    }
}

class Cake extends Model
{
    protected $label = 'Cake';
    protected $fillable = ['name'];
    // Will hold the events and their callbacks
    protected static $listenerStub = [];
    public static function boot()
    {
        static::$dispatcher = User::$dispatcher;
        // boot up model
        parent::boot();
        self::creating(function ($user) {
            $user->creating_event = true;
        });
        self::created(function ($user) {
            $user->created_event = true;
            $user->save();
        });
        self::saving(function ($user) {
            $user->saving_event = true;
        });
        self::saved(function ($user) {
            if (!$user->saved_event) {
                $user->saved_event = true;
                $user->save();
            }
        });
        self::deleting(function ($user) {
            $user->deleting_event = true;
        });
        self::deleted(function ($user) {
            $user->deleted_event = true;
            unset($user->id);
            $user->save();
        });
    }
}

class OBOne extends Model {

    use SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $label = 'OBOne';

    protected static $listenerStub = [];

    protected $fillable = [
        'name',
        'ob_creating_event',
        'ob_created_event',
        'ob_updating_event',
        'ob_updated_event',
        'ob_saving_event',
        'ob_saved_event',
        'ob_deleting_event',
        'ob_deleted_event',
        'ob_restoring_event',
        'ob_restored_event'
    ];

    // We'll just cancel out the events that were put on
    // the User model at boot time so that we make sure
    // we're using the observer ones.
    public static function boot()
    {
        parent::boot();

        // Mock a dispatcher
        $dispatcher = M::mock('Illuminate\Events\dispatcher');
        $dispatcher->shouldReceive('listen')->andReturnUsing(function($event, $callback)
        {
            static::$listenerStub[$event] = $callback;
        });
        $dispatcher->shouldReceive('until')->andReturnUsing(function($event, $model){
            if (isset(static::$listenerStub[$event]) and strpos(static::$listenerStub[$event], '@') !== false)
            {
                list($listener, $method) = explode('@', static::$listenerStub[$event]);
                if (isset(static::$listenerStub[$event])) call_user_func([$listener, $method], $model);
            }
            elseif (isset(static::$listenerStub[$event])) call_user_func(static::$listenerStub[$event], $model);
        });
        $dispatcher->shouldReceive('fire')->andReturnUsing(function($event, $model)
        {
            if (isset(static::$listenerStub[$event]) and strpos(static::$listenerStub[$event], '@') !== false)
            {
                list($listener, $method) = explode('@', static::$listenerStub[$event]);
                if (isset(static::$listenerStub[$event])) call_user_func([$listener, $method], $model);
            }
            elseif (isset(static::$listenerStub[$event])) call_user_func(static::$listenerStub[$event], $model);
        });

        static::$dispatcher = $dispatcher;
        OBOne::observe(new UserObserver);
    }

}

class UserObserver {

    public static function creating($ob)
    {
        $ob->ob_creating_event = true;
    }

    public static function created($ob)
    {
        $ob->ob_created_event = true;
        $ob->save();
    }

    public static function saving($ob)
    {
        $ob->ob_saving_event = true;
    }

    public static function saved($ob)
    {
        if ( ! $ob->ob_saved_event)
        {
            $ob->ob_saved_event = true;
            $ob->save();
        }
    }

    public static function deleting($ob)
    {
        $ob->ob_deleting_event = true;
    }

    public static function deleted($ob)
    {
        $ob->ob_deleted_event = true;
        $ob->save();
    }

    public static function restoring($ob)
    {
        $ob->ob_restoring_event = true;
    }

    public static function restored($ob)
    {
        $ob->ob_restored_event = true;
        $ob->save();
    }
}
