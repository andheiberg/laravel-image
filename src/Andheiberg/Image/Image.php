<?php namespace Andheiberg\Image;

use Illuminate\Http\Request;
use Illuminate\Config\Repository as Config;
use Imagecow\Image as Worker;
use Flysystem\Filesystem;

class Image {

	/**
	 * The filesystem implementation for the local filesystem.
	 *
	 * @var \Illuminate\Config\Repository
	 */
	protected $local;

	/**
	 * The filesystem implementation for the remote filesystem.
	 *
	 * @var \Illuminate\Config\Repository
	 */
	protected $filesystem;

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
	public function __construct(Filesystem $local, Filesystem $filesystem, Config $config, Worker $worker)
	{
		$this->local = $local;
		$this->filesystem = $filesystem;
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
		if ($cached = $this->getUrlFromCache($url, $this->processOptions($options)))
		{
			return $cached;
		}

		if (preg_match('/^(?:(https?:\/\/)|(www\.))(.*)/', $url, $matches))
		{
			$options['src'] = $url;

			$url = $this->config->get('image::route');
		}

		if ( ! empty($options))
		{
			$options = http_build_query($options);

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
		$options = $this->processOptions($options);

		if ($cached = $this->getUrlFromCache($url, $options))
		{
			return $cached;
		}

		if (preg_match('/^(?:(https?:\/\/)|(www\.))(.*)/', $url, $matches))
		{
			$url = $matches[3];

			$image = $this->saveRemoteFileToCache($url, $options);
		}
		elseif ($this->local->has('/public'.$url))
		{
			$image = $this->saveFileToCache(public_path().$url, $url, $options);
		}
		elseif ($this->filesystem->has($url))
		{
			$file = $this->filesystem->get($url);

			$image = $this->saveFileToCache($file->read(), $url, $options);
		}
		else
		{
			throw new \Exception("Image doesn't exist.");
		}

		$image->show();

		// if the script didn't die then it will have an error (Imagecow::show() dies when it returns image data)
		throw new \Exception($image->getError()->getMessage());
	}

	/**
	 * Check if file is on disk
	 *
	 * @param  string  $url
	 * @param  array   $options
	 * @return string
	 */
	public function getUrlFromCache($url, $options = array())
	{
		$path = $this->getCachedFile($url, $options);

		if ( ! $this->filesystem->has($path))
		{
			return false;
		}
		else
		{
			$prefix = '/public';
			if (substr($path, 0, strlen($prefix)) == $prefix)
			{
				$path = substr($path, strlen($prefix));
			}
		}

		if ($this->config->get('image::cache.store') == 's3')
		{
			$path = "http://{$this->config->get('image::cache.bucket')}.s3.amazonaws.com{$path}";
		}

		return $path;
	}

	/**
	 * Parse given options and normalize them
	 *
	 * @param  array   $options
	 * @return string
	 */
	public function processOptions($options = array())
	{
		$options = array_merge(['resize' => ['height' => null, 'width' => null]], $options);

		if (isset($options['preset']))
		{
			$preset = $this->config->get("image::presets", [])[$options['preset']];

			$options = array_merge($options, $preset);
		}

		foreach ($options as $key => $value)
		{
			if (in_array($key, ['h', 'height']))
			{
				$options['resize']['height'] = $value;
			}

			if (in_array($key, ['w', 'width']))
			{
				$options['resize']['width'] = $value;
			}
		}

		return $options;		
	}

	/**
	 * Parse given options to a string that can be part of file name
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
	public function getCacheFolder()
	{
		return $this->config->get('image::cache.destination');
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
	public function getCachedFolder($url)
	{
		if (preg_match('/^(?:(https?:\/\/)|(www\.))(.*)/', $url, $matches))
		{
			$url = '/'.$matches[3];
		}

		return $this->getCacheFolder().$url;
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

		return "{$folder}/{$name}.{$extension}";
	}

	/**
	 * Save a file to cache
	 *
	 * @param  mixed   $file path|file contents
	 * @param  string  $url
	 * @param  array   $options
	 * @return string
	 */
	public function saveFileToCache($file, $url, $options = array())
	{
		$image = $this->worker->load($file)
		->resizeCrop($options['resize']['width'], $options['resize']['height']);

		$path = $this->getCachedFile($url, $options);

		$this->filesystem->put($path, $image->getString(), ['visibility' => 'public']);

		return $image;
	}

	/**
	 * Save a remote file to cache
	 *
	 * @param  string  $url
	 * @param  array   $options
	 * @return string
	 */
	public function saveRemoteFileToCache($url, $options = array())
	{
		$url = explode('/', $url);
		$folder = '/app/storage/tmp/'.implode(array_slice($url, 0, -1));
		$file = end($url);
		$url = implode('/', $url);
		$tmp = $folder.'/'.$file;

		$this->local->put($tmp, '');

		$tmp = base_path().$tmp; // $tmp needs to be absolute from here on

		$ch = curl_init($url);
		$fp = fopen($tmp, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);

		return $this->saveFileToCache($tmp, '/'.$url, $options);
	}

}