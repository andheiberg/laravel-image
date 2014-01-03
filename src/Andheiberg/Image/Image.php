<?php namespace Andheiberg\Image;

use Illuminate\Http\Request;
use Illuminate\Config\Repository as Config;
use Intervention\Image\Image as Worker;

class Image {

	/**
	 * The input implementation.
	 *
	 * @var \Illuminate\Http\Request
	 */
	protected $request;

	/**
	 * The config implementation.
	 *
	 * @var \Illuminate\Config\Repository
	 */
	protected $config;

	/**
	 * The worker implementation.
	 *
	 * @var \Intervention\Image\Image
	 */
	protected $worker;

	/**
	 * Create a new Image instance.
	 *
	 * @return void
	 */
	public function __construct(Request $request, Config $config, Worker $worker)
	{
		$this->input = $request;
		$this->config = $config;
		$this->worker = $worker;
	}

	/**
	 * Create a image url.
	 *
	 * @param  string  $url
	 * @param  array   $options
	 * @return string
	 */
	public function url($url, $options = array())
	{
		$options = http_build_query($options);

		if ($options != '')
		{
			$url = $url.'?'.$options; 
		}

		return $url;
	}

	/**
	 * Find and serve an image
	 *
	 * @param  string  $url
	 * @param  array   $options
	 * @return string
	 */
	public function serve($url, $options = array())
	{
		if ( ! is_file(public_path().$url))
		{
			throw new \Exception("Image doesn't exist.");
		}

		$options = $this->processOptions($options);

		$image = $this->worker->cache(function($image) use ($url, $options) {
			return $image->make(public_path().$url)
			->grab($options['resize']['width'], $options['resize']['height']);
		}, $this->config->get('image::cache.lifetime'), true);

		return $image->response();
	}

	/**
	 * Parse given options and normalize them
	 *
	 * @param  array   $options
	 * @return string
	 */
	public function processOptions($options = array())
	{
		$opt = array('resize' => array('width' => null, 'height' => null));

		foreach ($options as $key => $value)
		{
			if (in_array($key, ['h', 'height']))
			{
				$opt['resize']['height'] = $value;
			}

			if (in_array($key, ['w', 'width']))
			{
				$opt['resize']['width'] = $value;
			}
		}
		
		return $opt;		
	}

}