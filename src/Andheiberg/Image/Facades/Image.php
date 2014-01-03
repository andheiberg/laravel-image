<?php namespace Andheiberg\Image\Facades;

use Illuminate\Support\Facades\Facade as Facade;

/**
 * @see \Andheiberg\Image\Image
 */
class Image extends Facade {

	/**
	 * Get the registered name of the component.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor() { return 'image'; }

}