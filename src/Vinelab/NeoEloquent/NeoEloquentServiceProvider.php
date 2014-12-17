<?php namespace Vinelab\NeoEloquent;

use Vinelab\NeoEloquent\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class NeoEloquentServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	* Bootstrap the application events.
	*
	* @return void
	*/
	public function boot()
	{
		Model::setConnectionResolver($this->app['db']);

		Model::setEventDispatcher($this->app['events']);

		$this->package('vinelab/neoeloquent');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['UserInterface'] = $this->app->share(function($app) {
            return new \User();
        });

        $this->app['CaseStudyInterface'] = $this->app->share(function($app) {
            return new \CaseStudy();
        });

        $this->app['PublisherInterface'] = $this->app->share(function($app) {
            return new \Publisher();
        });

        $this->app['JournalInterface'] = $this->app->share(function($app) {
            return new \Journal();
        });

        $this->app['UserEventInterface'] = $this->app->share(function($app) {
            return new \UserEvent();
        });

        $this->app['\Repository\Event\EventRepositaryInterface'] = $this->app->share(function($app) {
            return new \Repository\Event\EventRepositary(new \UserEvent());
        });
       
        $this->app['db']->extend('neo4j', function($config) {
            return new Connection($config);
        });
        
        
		$this->app['db']->extend('neo4j', function($config)
		{
			return new Connection($config);
		});

		$this->app->booting(function(){
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('NeoEloquent', 'Vinelab\NeoEloquent\Eloquent\Model');
		});
	}

}
