<?php namespace Andheiberg\Image;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\Image as Worker;

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
		// Register 'Image' instance container to our Image object
		$this->app['image'] = $this->app->share(function($app)
		{
			return new Image(
				$app['request'],
				$app['config'],
				new Worker
			);
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