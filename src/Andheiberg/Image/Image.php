<?php namespace Andheiberg\Image;

class Image {

	/**
	 * Create a new Image instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		
	}

	/**
	 * Get the cachebuster url for an Image.
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
	 * Get the html embed for a css Image.
	 *
	 * @param  string  $Image
	 * @return string
	 */
	public function css($Image)
	{
		$url = $this->url("/Images/css/{$Image}.css");

		return "<link rel=\"stylesheet\" href=\"{$url}\">";
	}

	/**
	 * Get the html embed for a js Image.
	 *
	 * @param  string  $Image
	 * @return string
	 */
	public function js($Image)
	{
		$url = $this->url("/Images/js/{$Image}.js");

		return "<script src=\"{$url}\"></script>";
	}



}