<?php

foreach(Config::get('image::routes') as $route) {
	Route::get($route, function() {
		$wildcards = func_get_args();
		return App::make('image')->serve('/'.Request::path(), Input::all());
	});
}