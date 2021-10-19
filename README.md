<h1 align="center">prig - Php Random Image Generator</h1>

<p align="center">
	<img src="https://polar-mountain-75010.herokuapp.com/image.php?width=300&height=300" alt="prig" width="300" height="300">
</p>

------

This project was born just out of sheer curiosity on finding out how an image generated with randomly 
colored pixels, using PHP, would look like.

After the first successful try, I decided to host this somewhere to be able to use this for demo
placeholder images on other projects easily by just setting an url on an image tag source attribute.

Then, since I was already using a git repository to hold my code, why not make it a downloadable package
to allow usage locally without external requests? 

So, to share it with everyone I released it publicly. I hope it can be of some use for anyone.

# Usage

## Using html image `src`
You can just point the `src` attribute of an image tag to the site where it is currently hosted
(https://polar-mountain-75010.herokuapp.com/image.php)
```html
<img src="https://polar-mountain-75010.herokuapp.com/image.php">
```

## Installing the package and creating the image on your PHP code
For now, until the package is published on packagist, you can installing it using git.
### Requirements
 - PHP 8.0+ (if you need compatibility with lower versions, I can extend the code to allow it, just ask)

```php

<?php

use pjam\prig\ImageGenerator\ImageGenerator;

require_once '../vendor/autoload.php';

$imageGenerator = new ImageGenerator($options);

// returns a GdImage object that you can use for something else
$imageObject = $imageGenerator->createRandomImage(); 

// outputs the image directly to the browser, with the right header already set
$imageGenerator->outputImage($imageObject);

// returns the base64 encoded string of the image that can be used also in the src attribute of an image tag
// or saved to database, for example
$imageGenerator->getEncodedImage($imageObject);
```

## Options

You can pass your desired options either by adding them as `GET` parameters on the query string
of the image tag source attribute or as elements of the options array passed to the class constructor.

Any invalid value will be ignored and defaults will be used as fallback.

### `height` (int, default: 200, min: 1, max: 2000)
The height of the image, in pixels.

Examples:
```html
<img src="https://polar-mountain-75010.herokuapp.com/image.php?height=600">
```
```php
$imageGenerator = new ImageGenerator(['height' => 20]);
```

### `width` (int, default: 200, min: 1, max: 2000)
The width of the image, in pixels.

Examples:
```html
<img src="https://polar-mountain-75010.herokuapp.com/image.php?width=800">
```
```php
$imageGenerator = new ImageGenerator(['width' => 1800]);
```

### `type` (string, default: 'png', accepted values: 'png', 'jpg|jpeg', 'webp')
The type and/or output format of the image.

Examples:
```html
<img src="https://polar-mountain-75010.herokuapp.com/image.php?type=jpeg">
```
```php
$imageGenerator = new ImageGenerator(['type' => 'webp']);
```

### `numColors` (?int, default: null, min: 1, max: 256)
The number of different colors to be used in the image. If not provided, 
it will try to set a different color for each pixel

Examples:
```html
<img src="https://polar-mountain-75010.herokuapp.com/image.php?numColors=2">
```
```php
$imageGenerator = new ImageGenerator(['numColors' => 50]);
```

## License

prig is released under the MIT License. See the bundled LICENSE file for details.

## Contributing
Any contributions are welcome. Just drop in a pull request.
