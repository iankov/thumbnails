<?php

namespace Iankov\Thumbnails\Controllers;

use Iankov\Thumbnails\Lib\Image;
use Iankov\Thumbnails\Lib\Validator;

class ThumbnailController extends Controller
{
    public function get($dir, $width, $height, $crop, $filename)
    {
        $validator = new Validator(config('thumbnails'));
        if(!$validator->allowSize($width, $height, $crop)){
            return response('Image size not allowed', 404);
        }

        if(!$validator->allowDir($dir)){
            return response('Directory is not allowed', 404);
        }

        $dir = trim($dir, "\/");
        $originalPath = public_path($dir.'/'.$filename);
        if(!file_exists($originalPath)){
            return response('Image Not Found', 404);
        }

        $newDir = public_path($dir.'/'.$width.'x'.$height.'x'.$crop);
        $newPath = $newDir.'/'.$filename;

        if(!file_exists($newDir)) {
            @mkdir($newDir, config('thumbnails.create_folder_mode'), true);
        }

        if(file_exists($newPath)) {
            return response()->file($newPath);
        }

        $original = new Image($originalPath);
        $image = $original->resize($width, $height, $crop);
        $image->save($newPath, config('thumbnails.quality'));

        if(file_exists($newPath)){
            return response()->file($newPath);
        }

        $image->display(config('thumbnails.quality'));
        $image->destroy();

        return null;
    }
}