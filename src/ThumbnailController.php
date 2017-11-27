<?php

namespace Iankov\Thumbnails;

class ThumbnailController extends Controller
{
    public function get($dir, $width, $height, $crop, $filename)
    {
        if(!$this->allowSize($width, $height, $crop)){
            return response('Image size not allowed', 404);
        }

        if(!$this->allowDir($dir)){
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

        $imageResource = $this->resizeImageData(file_get_contents($originalPath), $width, $height, (bool)$crop);
        $ext = strtolower(array_last(explode('.', $filename)));
        switch ($ext) {
            case 'jpeg':
            case 'jpg':
                @imagejpeg($imageResource, $newPath, config('thumbnails.quality.jpeg'));
                break;
            case 'png':
                @imagepng($imageResource, $newPath, config('thumbnails.quality.png'));
                break;
            case 'gif':
                @imagegif($imageResource, $newPath);
                break;
        }

        header('Content-type: image/jpeg');
        imagejpeg($imageResource);
        imagedestroy($imageResource);
        exit();
    }

    protected function allowSize($width, $height, $crop)
    {
        foreach(config('thumbnails.sizes') as $size){
            if($size['width'] == $width && $size['height'] == $height && $size['crop'] == $crop){
                return true;
            }
        }

        return false;
    }

    protected function allowDir($dir)
    {
        foreach(config('thumbnails.dirs') as $regex){
            if(preg_match('/^'.$regex.'$/', $dir)){
                return true;
            }
        }
    }

    protected function resizeImageData($imageData, $newWidth, $newHeight, $crop = false)
    {
        list($width, $height) = getimagesizefromstring($imageData);
        $oWidth = $width;
        $oHeight = $height;
        $ratio = $width / $height;
        $newRatio = $newWidth / $newHeight;
        if ($crop) {
            $k = $ratio/$newRatio;
            if($k > 1){
                $width = $width/$k;
            }else{
                $height = $height*$k;
            }
        } else {
            if ($newRatio > $ratio) {
                $newWidth = $newHeight*$ratio;
            } else {
                $newHeight = $newWidth/$ratio;
            }
        }

        $src = imagecreatefromstring($imageData);

        //make transparent image
        $dst = imagecreatetruecolor($newWidth, $newHeight);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefilledrectangle($dst, 0, 0, $width, $height, $transparent);

        imagecopyresampled(
            $dst,
            $src,
            0, //x-coordinate of destination point
            0, //y-coordinate of destination point
            floor(($oWidth-$width)/2), //x-coordinate of source point
            floor(($oHeight-$height)/2), //y-coordinate of source point
            $newWidth, //Destination width
            $newHeight, //Destination height
            $width, //Source width
            $height //Source height
        );

        return $dst;
    }
}