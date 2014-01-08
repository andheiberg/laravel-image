<?php

Route::get(Config::get('image::route'), function() {
	return App::make('image')->serve(Input::get('src'), Input::all());
});