Image
===
Image processing library wrapper with on the fly processing and caching of image requests. Currently supports ImageMagick and Gd.

Basic usage (don't use it this way it's mainly for documentation purposes)
---
A route is set up for URL based image processing. The route can be configured but by default all requests to `/images?src=/path/to/image.png`. You can append verious parameters to this url to trigger different processing.

Supported parameters:

* h or height (desired pixel height of image)
* w or width (desired pixel width of image)
* preset (use a processing preset defined in the config)

**Example**

	<img src="/images?src=/assets/image/logo.png?h=150&w=150" alt="">

	<!-- This will work for images stored on the server -->
	<img src="/assets/image/logo.png?h=150&w=150" alt="">

**Drawbacks**

* By linking to images this way, you demand that php runs on every image request. This is quite resource intensive for your server compared to a static file link and would increase download time.
* This also means that every image request would fail if you image script is unavailible or broken. (can be fixed by using the second example, but only for local images)

Proper usage
---
In order to negate above mentions drawbacks use the url function. It takes a url and an array of options. It will output static file urls if the image is in the cache or a link formated like the examples in "Basic usage" if not.

	<img src="{{ Image::url('/assets/images/logo.png', ['h' => 64, 'w' => 100]) }}" alt="">

Note: images that are not in cache will be processed upon request, and not imidiadly to decrease load time for your application.

Caching
---
Processed images are store to file in your prefered cachestore. Currently supports local and s3, but filestores can easily be added.

Installation
---
Run ```composer require andheiberg/image:1.*```

Add `'Andheiberg\Image\ImageServiceProvider',` to `providers` in `app/config/app.php`

Add `'Image' => 'Andheiberg\Image\Facades\Image',` to `aliases` in `app/config/app.php`

Run ```php artisan config:publish andheiberg/image```

Add the following to you .htaccess or php.ini (add before laravels)

	<IfModule mod_rewrite.c>
		# Resize on the fly
		RewriteCond %{QUERY_STRING}  ^(.+) [NC]
		RewriteRule ^(.+)\.(png|jpg|gif)$ /images?src=/$1.$2 [L,R,QSA]
	</IfModule>

Todo
---
* Add support for more than just resize
* Add support for GraphicsMagick
