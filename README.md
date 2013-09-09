JVPhpThumb - JV PHP Thumb
================
Create By: Jaime Marcelo Valasek

Use this module to generate thumbnail to apply the images on your website.

Futures video lessons can be developed and published on the website or Youtube channel http://www.zf2.com.br/tutoriais http://www.youtube.com/zf2tutoriais

Installation
-----
Download this module into your vendor folder.

After done the above steps, open the file config / application.config.php. And add the module with the name JVPhpThumb.


Using the thumb php
-----

 - Create a folder in the root of the site Imagecache
 - Search the Internet or develop an image to display as image not found in case of missing image folder root / img / image_not_found.jpg

Configure the folders of images to use when generating the thumbs, these settings are in the file module.config.php

```php
'php_thumb' => array (
    'thumbnail_folder' => array(
        'produto-pagina-inicial' => '/conteudos/thumbs/'    
    ),
    'cache' => true,
),
```
    
// View Code
< img alt="imagem" src="/thumbnail/nameModuleConfigFolder/250/150/8:5/imagem.jpg" >

exemplo da url
htttp://www.seusite.com.br/thumbnail/produtos-home/250/150/8:5/imagem.jpg
htttp://www.seusite.com.br/thumbnail/nameModuleConfigFolder/width/height/ratio/image