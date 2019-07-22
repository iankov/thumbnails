<?php

namespace Iankov\Thumbnails\Lib;

class Validator
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function allowSize($width, $height, $crop)
    {
        foreach($this->config['sizes'] as $size){
            if($size['width'] == $width && $size['height'] == $height && $size['crop'] == $crop){
                return true;
            }
        }

        return false;
    }

    public function allowDir($dir)
    {
        foreach($this->config['dirs'] as $regex){
            if(preg_match('/^'.$regex.'$/', $dir)){
                return true;
            }
        }
    }
}