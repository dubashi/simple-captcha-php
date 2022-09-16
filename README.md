# simple-captcha-php

Generate captcha image without using fonts, splitted captcha image or make animated GIF captcha.

![Captchas examples](https://github.com/dubashi/simple-captcha-php/blob/main/example.gif)

# Installation

With composer:

```bash
composer require dubashi/simple-captcha-php
```

# Usage

You can create a simple captcha :

```php
<?php
use Dubashi\SimpleCaptcha;

$code = '0123';
$options = [ 'thickness' => 5, 'emboss' => true, ]; // optional

// Output http-response with captcha image
$captcha = (new SimpleCaptcha( $options ))->create( $code )->output();
```

Or can create animated captcha :

```php
<?php
use Dubashi\SimpleCaptcha;

// Create animated GIF captcha and save it into file
$captcha = (new SimpleCaptcha([
      'type'      => \IMAGETYPE_GIF,
      'thickness' => 5,
    ]))
    ->create( $code )       // create captcha with "code"
    ->split( 3 )            // split into 3 parts/frames
    ->outputFile('captcha') // save into file "captcha.gif"
  ;
  
// ..and get base64 data of image for own usage
$dataBase64 = $captcha->getDataURI();

// ..or just output <img> tag into yours html-templaters
$captcha->outputImgHtml();
```

# Options

* **width**, Width of result image(s) in pixels. Default: 100
* **height**, Height of result image(s) in pixels. Default: 50
* **type**, Type of result image(s). One of GD constants. Default: IMAGETYPE_JPEG
* **thickness**, Thinkness line of symbols in pixels. Default: 2
* **colorFont**, Font color. Array Red, Green, Blue color decimal component. Default: array( 30, 40, 50 )
* **colorBackground**, Background color. Default: array( 255, 255, 255 )
* **dashed**, Flag for use dashed line style. Default: false
* **randPos**, Flag for enable random position of verticies each one symbols. Default: false
* **randPointPos**, Flag for enable random position of verticies point scheme. Default: false
* **randSym**, Flag for use random symbols scheme. Default: false
* **emboss**, Flag for use emboss filter for image. Default: false
* **nightmare**, Flag for added more nightmare for bots and your users. Default: false
* **duration**, The duration for GIF frames (in 1/100s). Default: 2
* **loop**, Loop GIF frames. Default: 0 (infinity)

# API

You can use theses functions :

* **create( $code )**, Create Simple Captcha image with specified string code
* **output()**, Output image into stdout or browser
* **outputFile()**, Save Simple Captcha image(s) into file
* **outputDataURI()**, Output base64 encoded data of specified part of image
* **getDataURI()**, Returns base64 encoded data of specified part of image
* **outputImgHtml()**, Output IMG tag
* **getImgHtml()**, Returns IMG tag with captcha image(s)
* **split()**, Split source captcha image to parts
* **setSymbolsScheme()**, Set your own symbols scheme. See description of this method in code

# License

This library is under MIT license, have a look to the LICENSE file
