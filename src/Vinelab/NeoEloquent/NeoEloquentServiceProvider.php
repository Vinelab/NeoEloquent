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
