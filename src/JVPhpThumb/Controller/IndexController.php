<?php

namespace JVPhpThumb\Controller;

use JVPhpThumb\Library\PhpThumb;

use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $folder = $this->params('folder');
        $width = $this->params('width');
        $height = $this->params('height');
        $cropratio = $this->params('cropratio');
        $image = $this->params('image');
        
        // pegando a configuração de pastas
        $config = $this->getServiceLocator()->get('config');
        $configPhpThumb = $config['php_thumb'];
        
        $folderDir = $configPhpThumb['thumbnail_folder'][$folder];
        $cache = $configPhpThumb['cache'];
        
        $phpThumb = new PhpThumb();
        $return = $phpThumb->setImageConfig(array(
            'width' => $width, 
            'height' => $height, 
            'cropratio' => $cropratio, 
            'quality' => 90, 
            'color' => ''))
        ->setCache($cache)
        ->setFolder($folderDir)
        ->getImage($image);
        
        if ($return) {
            
            $size = $return[0];
            $resized = $return[1];
            
            $imageModified	= filemtime($_SERVER['DOCUMENT_ROOT'] . $folderDir . $image);
            $thumbModified	= filemtime($resized);
            
            if($imageModified < $thumbModified) {
                $data	= file_get_contents($resized);
            
                header("Content-type: {$size['mime']}");
                header('Content-Length: ' . strlen($data));
                echo $data;
            
                exit;
            }
        }
    }
}