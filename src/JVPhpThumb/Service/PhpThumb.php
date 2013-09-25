<?php

// This module has been written based on library Smart Image Resizer 1.4.1
// Resizes images, intelligently sharpens, crops based on width:height ratios, color fills
// transparent GIFs and PNGs, and caches variations for optimal performance

// Created by: Joe Lencioni (http://shiftingpixel.com)
// Date: August 6, 2008
// Based on: http://veryraw.com/history/2005/03/image-resizing-with-php/

###################### Witten version to work in Zend Framework 2 ###################
// Created by: Jaime Marcelo Valasek (http://zf2.com.br / http://www.valasek.com.br)
// Date: September 4, 2013
// Based on: http://shiftingpixel.com/2008/03/03/smart-image-resizer/

namespace JVPhpThumb\Service;

class PhpThumb
{
    const TMP_DIR = '/imagecache/';
    
    protected $_image;
    protected $_folder;
    protected $_size;
    protected $_nameDestination;
    protected $_fileInfo;
    protected $_imageConfig = array('width' => 100, 'height' => 100, 'cropratio' => '1:1', 'quality' => 90, 'color' => '');
    protected $_docRoot;
    protected $_tmpDir;
    protected $_cache;
    
    public function __construct()
    {
        $this->_docRoot = $_SERVER['DOCUMENT_ROOT'];
        $this->_tmpDir = $this->_docRoot . self::TMP_DIR;        
    }
    
    public function setImageConfig(array $imageConfig)
    {
        $this->_imageConfig = $imageConfig;
        
        return $this;
    }
    
    public function setCache($cache)
    {
        $this->_cache = $cache;
        
        return $this;
    }
    
    public function setRatio()
    {
        // verifica se a imagem existe caso não exista exibe image not found
        if (!file_exists($this->_docRoot . $this->_folder . $this->_image))
        {
            $this->_folder = '/img/';
            $this->_image = 'image_not_found.jpg';
        }
            
        // Size image
        $size	= GetImageSize($this->_docRoot . $this->_folder . $this->_image);
        $this->_size = $size;
        
        // get mimi type image
        $mime	= $size['mime'];
        
        $width			= $size[0];
        $height			= $size[1];
        
        $maxWidth		= (int) $this->_imageConfig['width'];
        $maxHeight		= (int) $this->_imageConfig['height'];
        
        if (!empty($_GET['color'])) {
            $color		= (string) $this->_imageConfig['color'];
        } else {
            $color		= FALSE;
        }
        
        if (!$maxWidth && $maxHeight) {
            $maxWidth	= 99999999999999;
        } elseif ($maxWidth && !$maxHeight) {
            $maxHeight	= 99999999999999;
        } elseif ($color && !$maxWidth && !$maxHeight) {
            $maxWidth	= $width;
            $maxHeight	= $height;
        }
        
        // Ratio cropping
        $offsetX = 0;
        $offsetY = 0;
        
        $cropRatio = explode(':', (string) $this->_imageConfig['cropratio']);
        if (count($cropRatio) == 2)
        {
            $ratioComputed		= $width / $height;
            $cropRatioComputed	= (float) $cropRatio[0] / (float) $cropRatio[1];
        
            if ($ratioComputed < $cropRatioComputed)
            { 
                // Image is too tall so we will crop the top and bottom
                $origHeight	= $height;
                $height		= $width / $cropRatioComputed;
                $offsetY	= ($origHeight - $height) / 2;
            }
            else if ($ratioComputed > $cropRatioComputed)
            { 
                // Image is too wide so we will crop off the left and right sides
                $origWidth	= $width;
                $width		= $height * $cropRatioComputed;
                $offsetX	= ($origWidth - $width) / 2;
            }
        }
        
        // Setting up the ratios needed for resizing. We will compare these below to determine how to
        // resize the image (based on height or based on width)
        $xRatio		= $maxWidth / $width;
        $yRatio		= $maxHeight / $height;
        
        if ($xRatio * $height < $maxHeight)
        { 
            // Resize the image based on width
            $tnHeight	= ceil($xRatio * $height);
            $tnWidth	= $maxWidth;
        }
        else 
        {
            // Resize the image based on height
            $tnWidth	= ceil($yRatio * $width);
            $tnHeight	= $maxHeight;
        }
        
        $resizedImageSource		= $tnWidth . 'x' . $tnHeight . 'x' . $this->_imageConfig['quality'];
        if ($color)
            $resizedImageSource	.= 'x' . $color;
        if (isset($this->_imageConfig['cropratio']))
            $resizedImageSource	.= 'x' . (string) $this->_imageConfig['cropratio'];
        $resizedImageSource		.= '-' . $this->_image;
        
        $resizedImage	= md5($resizedImageSource);
        
        $resized		= $this->_tmpDir . $resizedImage;

        $return = array(
            'tnWidth' => $tnWidth, 
            'tnHeight' => $tnHeight,
            'size' => $size,
            'mimi' => $mime,
            'offsetX' => $offsetX,
            'offsetY' => $offsetY,
            'width' => $width,
            'height' => $height,
            'resized' => $resized);
        
        if ($this->_cache && file_exists($resized))
        {
            $imageModified	= filemtime($this->_docRoot . $this->_folder . $this->_image);
            $thumbModified	= filemtime($resized);
            
            if($imageModified < $thumbModified) {
                $data	= file_get_contents($resized);
            
                header("Content-type: $mime");
                header('Content-Length: ' . strlen($data));
                echo $data;
                
                exit;
            }
        }
        
        return $return;
    }
    
    public function createCanvas(array $params)
    {
        $tnWidth = $params['tnWidth'];
        $tnHeight = $params['tnHeight'];
        $offsetX = $params['offsetX'];
        $offsetY = $params['offsetY'];
        $width = $params['width'];
        $height = $params['height'];
        $size = $params['size'];
        $resized = $params['resized'];
        $quality = $this->_imageConfig['quality'];
        $fileInfo = $this->_fileInfo;
        
        // Set up a blank canvas for our resized image (destination)
        $dst	= imagecreatetruecolor($tnWidth, $tnHeight);
        
        // Set up the appropriate image handling functions based on the original image's mime type
        switch ($size['mime'])
        {
            case 'image/gif':
                // We will be converting GIFs to PNGs to avoid transparency issues when resizing GIFs
                // This is maybe not the ideal solution, but IE6 can suck it
                $creationFunction	= 'ImageCreateFromGif';
                $outputFunction		= 'ImagePng';
                $mime				= 'image/png'; // We need to convert GIFs to PNGs
                $doSharpen			= FALSE;
                $quality			= round(10 - ($quality / 10)); // We are converting the GIF to a PNG and PNG needs a compression level of 0 (no compression) through 9
                break;
        
            case 'image/x-png':
            case 'image/png':
                $creationFunction	= 'ImageCreateFromPng';
                $outputFunction		= 'ImagePng';
                $doSharpen			= FALSE;
                $quality			= round(10 - ($quality / 10)); // PNG needs a compression level of 0 (no compression) through 9
                break;
        
            default:
                $creationFunction	= 'ImageCreateFromJpeg';
                $outputFunction	 	= 'ImageJpeg';
                $doSharpen			= TRUE;
                break;
        }
        
        // Read in the original image
        $src	= $creationFunction($this->_docRoot . $this->_folder . $this->_image);
        
        if (in_array($size['mime'], array('image/gif', 'image/png')))
        {
            if (!$color)
            {
                // If this is a GIF or a PNG, we need to set up transparency
                imagealphablending($dst, false);
                imagesavealpha($dst, true);
            }
            else
            {
                // Fill the background with the specified color for matting purposes
                if ($color[0] == '#')
                    $color = substr($color, 1);
        
                $background	= FALSE;
        
                if (strlen($color) == 6)
                    $background	= imagecolorallocate($dst, hexdec($color[0].$color[1]), hexdec($color[2].$color[3]), hexdec($color[4].$color[5]));
                else if (strlen($color) == 3)
                    $background	= imagecolorallocate($dst, hexdec($color[0].$color[0]), hexdec($color[1].$color[1]), hexdec($color[2].$color[2]));
                if ($background)
                    imagefill($dst, 0, 0, $background);
            }
        }
        
        // Resample the original image into the resized canvas we set up earlier
        ImageCopyResampled($dst, $src, 0, 0, $offsetX, $offsetY, $tnWidth, $tnHeight, $width, $height);
        
        if ($doSharpen)
        {
            // Sharpen the image based on two things:
            //	(1) the difference between the original size and the final size
            //	(2) the final size
            $sharpness	= $this->findSharp($width, $tnWidth);
        
            $sharpenMatrix	= array(
                array(-1, -2, -1),
                array(-2, $sharpness + 12, -2),
                array(-1, -2, -1)
            );
            $divisor		= $sharpness;
            $offset			= 0;
            imageconvolution($dst, $sharpenMatrix, $divisor, $offset);
        }
        
        if (!file_exists($this->_tmpDir))
            mkdir($this->_tmpDir, 0755);
        
        if (!is_readable($this->_tmpDir))
        {
            throw new Exception('A pasta temp não está habilitada para leitura');
        }
        else if (!is_writable($this->_tmpDir))
        {
            throw new Exception('A pasta temp não está habilitada para escrita');
        }
        
        // Write the resized image to the cache
        $outputFunction($dst, $resized, $quality);
        
        return $resized;
    }
    
    public function findSharp($orig, $final) // function from Ryan Rud (http://adryrun.com)
    {
    	$final	= $final * (750.0 / $orig);
    	$a		= 52;
    	$b		= -0.27810650887573124;
    	$c		= .00047337278106508946;
    	
    	$result = $a + $b * $final + $c * $final * $final;
    	
    	return max(round($result), 0);
    }
    
    public function setFolder($folder)
    {
        $this->_folder = $folder;
        
        return $this;
    }
    
    public function validationParams()
    {
        if (empty($this->_folder) || empty($this->_image))
        {
            return false;
        }
        
        $imageConfig = $this->_imageConfig;
        unset($imageConfig['color']);
        
        foreach ($imageConfig as $item)
        {
            if (empty($item))
            {
                return $size;
            }
        }
        
        return true;
    }
    
    /**
     * @param array $fileInfo
     * @param string $nameDestinationThumb
     * @param string $permission Permissoes disponíveis private, public-read, public-read-write
     */
    public function getImage($imageName)
    {
        $this->_image = $imageName;
        
        if ($this->validationParams()) {
            
            $params = $this->setRatio();
            $resized = $this->createCanvas($params);
            
            return array($this->_size, $resized);
        }
    }    
}