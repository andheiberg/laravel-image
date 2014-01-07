<?php namespace Andheiberg\Image;

use Illuminate\Support\ServiceProvider;
use Intervention\Image\Image as Worker;
use Aws\S3\S3Client;
use Flysystem\Filesystem;
use Flysystem\AdapterInterface;
use Flysystem\Adapter\AwsS3 as AwsS3Adapter;
use Flysystem\Adapter\Local as LocalAdapter;

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
			if ($app['config']->get('image::cache.store') == 's3')
			{
				$client = S3Client::factory(array(
					'key'    => $app['config']->get('image::cache.key'),
					'secret' => $app['config']->get('image::cache.secret'),
				));

				$filesystem = new Filesystem(new AwsS3Adapter(
					$client,
					$app['config']->get('image::cache.bucket'),
					$app['config']->get('image::cache.prefix'),
					['visibility' => AdapterInterface::VISIBILITY_PUBLIC]
				));
			}
			else
			{
				$filesystem = new Filesystem(new LocalAdapter(base_path()));
			}

			return new Image(
				new Filesystem(new LocalAdapter(base_path())),
				$filesystem,
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