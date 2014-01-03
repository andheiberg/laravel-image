<?php

foreach(Config::get('image::routes') as $route) {
	Route::get($route.'{file}', function($file) {
		return App::make('image')->serve('/assets/images/'.$file, Input::all());
	});
}