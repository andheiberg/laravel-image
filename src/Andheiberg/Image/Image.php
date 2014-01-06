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
	protected $input;

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
		if ($cached = $this->fileExists($url, $options))
		{
			return $cached;
		}

		if ( ! empty($options))
		{
			$options = http_build_query($options);

			$url = $url.'?'.$options; 
		}

		return $url;
	}

	/**
	 * Check if file is on disk
	 *
	 * @param  string  $url
	 * @param  array   $options
	 * @return string
	 */
	public function fileExists($url, $options = array())
	{
		$options = $this->processOptions($options);

		$path = $this->getCachedFile($url, $options);

		if ( ! file_exists(public_path().$path))
		{
			return false;
		}

		return $path;
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

		$image = $this->worker->make(public_path().$url)
		->grab($options['resize']['width'], $options['resize']['height']);

		$folder = $this->getCachedFolder($url, true);
		$path = $this->getCachedFile($url, $options, true);

		if ( ! is_dir($folder))
		{
			mkdir($folder, 0777, true);
		}

		$image->save($path);

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
		$parsedOptions = [];

		if (isset($options['preset']))
		{
			$parsedOptions = $this->config->get('image::presets', [])[$options['preset']];
		}

		foreach ($options as $key => $value)
		{
			if (in_array($key, ['h', 'height']))
			{
				$parsedOptions['resize']['height'] = $value;
			}

			if (in_array($key, ['w', 'width']))
			{
				$parsedOptions['resize']['width'] = $value;
			}
		}

		return $parsedOptions;		
	}

	/**
	 * Parse given options and normalize them
	 *
	 * @param  array   $options
	 * @return string
	 */
	public function processOptionsToFileString($options = array())
	{
		return $options['resize']['width'].'x'.$options['resize']['height'];	
	}

	/**
	 * Parse given options and normalize them
	 *
	 * @param  array   $options
	 * @return string
	 */
	public function processOptionsToExtension($options = array())
	{
		return $options['resize']['width'].'x'.$options['resize']['height'];	
	}

	/**
	 * Parse given options and normalize them
	 *
	 * @param  array   $options
	 * @return string
	 */
	public function getCacheFolder($absolute = false)
	{
		$path  = $absolute ? public_path() : '';
		$path .= $this->config->get('image::cache.destination');

		return $path;
	}

	/**
	 * Parse given options and normalize them
	 *
	 * @param  array   $options
	 * @return string
	 */
	public function getExtensionFromUrl($url)
	{
		return substr(strrchr($url,'.'),1);
	}

	/**
	 * Parse given options and normalize them
	 *
	 * @param  array   $options
	 * @return string
	 */
	public function getCachedFolder($url, $absolute = false)
	{
		return $this->getCacheFolder($absolute).$url;
	}

	/**
	 * Parse given options and normalize them
	 *
	 * @param  array   $options
	 * @return string
	 */
	public function getCachedFile($url, $options = array(), $absolute = false)
	{
		$folder  = $this->getCachedFolder($url, $absolute);
		$name = $this->processOptionsToFileString($options);
		$extension = $this->getExtensionFromUrl($url);

		return "{$folder}{$name}.{$extension}";
	}

}