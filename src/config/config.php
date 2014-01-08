<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Image Worker
	|--------------------------------------------------------------------------
	|
	| The image processing library to be used.
	|
	| Supported: "Imagick", "Gd"
	|
	*/

	'worker' => 'Imagick',

	/*
	|--------------------------------------------------------------------------
	| Route
	|--------------------------------------------------------------------------
	|
	| The route to be used when building image processing url.
	|
	*/

	'route' => '/image',

	/*
	|--------------------------------------------------------------------------
	| Cache Store and Destination
	|--------------------------------------------------------------------------
	|
	| These option controls where the processed images are stored.
	|
	| Supported: "local", "s3", "dropbox", "ftp", "sftp", "WebDAV"
	|
	*/

	'cache' => array(
		'store'       => 'local',
		'destination' => '/public/images',
		'bucket'      => '',
		'key'         => '',
		'secret'      => '',
		'prefix'      => '',
	),

	/*
	|--------------------------------------------------------------------------
	| Presets
	|--------------------------------------------------------------------------
	|
	| This option allows you to reference specific processing settings by name.
	| Resize is currently the only supported attribute, but you could easily
	| extend it and make a pull request if you need more.
	|
	| Supported: "resize"
	|
	*/

	'presets' => array(
		'profile.micro' => ['resize' =>['width' => 30, 'height' => 30]],
		'profile.small' => ['resize' =>['width' => 100, 'height' => 100]],
		'profile.medium' => ['resize' =>['width' => 150, 'height' => 150]],
		'profile.preset' => ['resize' =>['width' => 350, 'height' => 350]],
	),
 
);