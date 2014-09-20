<?php namespace Andheiberg\Image;

use Illuminate\Support\ServiceProvider;
use Imagecow\Image as Worker;
use Aws\S3\S3Client;
use League\Flysystem\Filesystem;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\AwsS3 as AwsS3Adapter;
use League\Flysystem\Adapter\Local as LocalAdapter;

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
		$this->registerFilesystem();
		$this->registerWorker();

		// Register 'Image' instance container to our Image object
		$this->app['image'] = $this->app->share(function($app)
		{
			return new Image(
				new Filesystem(new LocalAdapter(base_path())),
				$app['image.filesystem'],
				$app['config'],
				$app['image.worker']
			);
		});
	}

	/**
	 * Register the filesystem for caching.
	 *
	 * @return void
	 */
	public function registerFilesystem()
	{
		// Register 'Image' instance container to our Image object
		$this->app['image.filesystem'] = $this->app->share(function($app)
		{
			if ($app['config']->get('image::cache.store') == 's3')
			{
				$client = S3Client::factory(array(
					'key'    => $app['config']->get('image::cache.key'),
					'secret' => $app['config']->get('image::cache.secret'),
				));

				return new Filesystem(new AwsS3Adapter(
					$client,
					$app['config']->get('image::cache.bucket'),
					$app['config']->get('image::cache.prefix'),
					['visibility' => AdapterInterface::VISIBILITY_PUBLIC]
				));
			}
			
			return new Filesystem(new LocalAdapter(base_path()));
		});
	}

	/**
	 * Register the image worker.
	 *
	 * @return void
	 */
	public function registerWorker()
	{
		$this->app['image.worker'] = $this->app->share(function($app)
		{
			return Worker::create($app['config']->get('image::worker'));
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