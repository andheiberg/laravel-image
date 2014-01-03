<?php

foreach(Config::get('image::routes') as $route) {
	Route::get(rtrim($route, '/'), function() {
		App::make('image')->serve();
	});
}