JVPhpThumb
================
Create By: Jaime Marcelo Valasek

Use this module to generate thumbnail to apply the images on your website.

Futures video lessons poderam be developed and posted on the website or in http://www.zf2.com.br/tutoriais Youtube channel http://www.youtube.com/zf2tutoriais

Installation
-----
Download this module into your vendor folder.

After done the above steps, open the file config / application.config.php. And add the module with the name JVPhpThumb.

### With composer

1. Add this project and JVConfig in your composer.json:

```php
"require": {
    "jaimevalasek/jv-php-thumb": "dev-master"
}
```

2. Now tell composer to download JVPhpThumb by running the command:

```php $ php composer.phar update```

### Enabling it in your `application.config.php`.
```php
<?php
return array(
    'modules' => array(
        // ...
        'JVConfig',
        'JVPhpThumb',
    ),
    // ...
);
```

### After the installation includes a configuration module JVConfig - `module.config.php`.

 - Create a folder in the root of the site Imagecache
 - Search the Internet or develop an image to display as image not found in case of missing image folder root / img / image_not_found.jpg
 - Configure the folders of images to use when generating the thumbs, these settings are in the file module.config.php

```php
return array(
	// ...
	'php_thumb' => array (
	    'thumbnail_folder' => array(
	        'produto-pagina-inicial' => '/conteudos/imagens/'
	    ),
	    'cache' => true,
	),
	// ...
);
```

Using the thumb php
-----
    
// View Code
< img alt="imagem" src="/thumbnail/nameModuleConfigFolder/250/150/8:5/imagem.jpg" >

exemplo da url
htttp://www.seusite.com.br/thumbnail/produto-pagina-inicial/250/150/8:5/imagem.jpg
htttp://www.seusite.com.br/thumbnail/name_thumbnail_folder/width/height/ratio/image