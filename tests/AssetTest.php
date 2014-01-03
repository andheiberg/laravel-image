<?php

use \Andheiberg\Asset\Asset;
use \Andheiberg\Asset\AssetManager;

class AssetTest extends \PHPUnit_Framework_TestCase {

	/**
	 * The asset instance.
	 *
	 * @var \Andheiberg\Asset\Asset
	 */
	protected $asset;

	/**
	 * Create a new Eloquent model instance.
	 *
	 * @param  array  $attributes
	 * @return void
	 */
	public function __construct()
	{
		$this->asset = new Asset(new AssetManager);
	}

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testUrl()
	{
		$this->assertEquals($this->asset->url('/not-cachebusted/main.js'), '/not-cachebusted/main.js');
	}

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testCss()
	{
		$this->assertEquals($this->asset->css('main'), '<link rel="stylesheet" href="/assets/css/main.css">');
	}

	/**
	 * A basic functional test example.
	 *
	 * @return void
	 */
	public function testJs()
	{
		$this->assertEquals($this->asset->js('main'), '<script src="/assets/js/main.js"></script>');
	}

}