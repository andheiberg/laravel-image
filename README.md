Image
===
Image processing library with on the fly processing of image requests.

Usage
---
	
	// will work
	<img src="/assets/images/logo.png?h=64&w100" alt="">

	// I have some features I would like to implement, that needs a helper class
	<img src="{{ Image::url('/assets/images/logo.png', ['h' => 64, 'w' => 100]) }}" alt="">

Installation
---
Run ```composer require andheiberg/image:1.*```

Add `'Andheiberg\Image\ImageServiceProvider',` to `providers` in `app/config/app.php`

Run ```php artisan config:publish andheiberg/image```

Add the following to you .htaccess or php.ini (add before laravels)

	<IfModule mod_rewrite.c>
		# Resize on the fly
		RewriteCond %{QUERY_STRING}  ^(.+) [NC]
		RewriteRule ^(.+)\.(png|jpg|gif)$ index.php [L]
	</IfModule>

Todo
---
* Make it work with Amazon S3 (AWS S3)
* Bug the creater of "intervention/imagecache" about his implementation
* Add support for more than just resize
* Save to file instead of just cache to increase response time