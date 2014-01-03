<?php

return array(

	'routes' => array(
		'/assets/images/{file}',
		'/uploads/{folder}/{file}',
	),

	'cache' => array(
		'lifetime' => 10080, // in minutes
	),

	'presets' => array(
		'profile.micro' => ['resize' =>['width' => 30, 'height' => 30]],
		'profile.small' => ['resize' =>['width' => 100, 'height' => 100]],
		'profile.medium' => ['resize' =>['width' => 150, 'height' => 150]],
		'profile.preset' => ['resize' =>['width' => 350, 'height' => 350]],
	),
 
);