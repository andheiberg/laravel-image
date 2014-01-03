<?php namespace Andheiberg\Image;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\CacheManager;

class ImageServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	public function boot()
	{
		$this->package('andheiberg/image');

		include __DIR__.'/routes.php';
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bind('image.cache', function($app) {
			$config = array();
			$config['config']['cache.driver'] = 'file';
			$config['config']['cache.path'] = storage_path() . '/cache/' . $app['config']->get('image::cache.path');
			$config['files'] = $app['files'];
			
			return new CacheManager($config);
		});

		$this->app->bind('image', function($app) {
			return new Image(
				$app['image.cache'],
				$app['config']->get('image::cache.lifetime'),
				$app['config']->get('image::route')
			);
		});

		// Register 'Image' instance container to our Image object
		$this->app['Image'] = $this->app->share(function($app)
		{
			return new Image($app['image.manager']);
		});

		// Shortcut so developers don't need to add an Alias in app/config/app.php
		$this->app->booting(function()
		{
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();
			$loader->alias('Image', 'Andheiberg\Image\Facades\Image');
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('image');
	}

}