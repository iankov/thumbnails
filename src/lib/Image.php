<?php

namespace Iankov\Thumbnails\Lib;

class Image
{
    protected $path, $resource;

    /**
     * Image constructor.
     * @param $image
     * @throws \Exception
     */
    public function __construct($image)
    {
        if (is_resource($image)) {
            $this->path = null;
            $this->resource = $image;
        } elseif(is_string($image)) {
            $this->path = $image;
            $this->resource = imagecreatefromstring(file_get_contents($this->path));
        } else {
            throw new \Exception('Image not valid');
        }
    }

    public function resource()
    {
        return $this->resource;
    }

    /**
     * Get resized Image object
     *
     * @param int $width
     * @param int $height
     * @param bool $crop - defines if image crop is allowed to fit width and height
     *
     * @return Image
     * @throws \Exception
     */
    public function resize(int $width, int $height, bool $crop = false)
    {
        $resource = $this->resource();
        $imageWidth = imagesx($resource);
        $imageHeight = imagesy($resource);

        $croppedWidth = $imageWidth;
        $croppedHeight = $imageHeight;
        $imageRatio = $imageWidth / $imageHeight;
        $newRatio = $width / $height;
        if ($crop) {
            $k = $imageRatio / $newRatio;
            //300x200 = 1.5;
            //300x250 = 1.2;
            //k = 1.25
            if ($k > 1) {
                //240x200
                $croppedWidth = $imageWidth / $k;
            } else {
                $croppedHeight = $imageHeight * $k;
            }
        } else {
            if ($newRatio > $imageRatio) {
                $width = $height * $imageRatio;
            } else {
                $height = $width / $imageRatio;
            }
        }

        //make transparent image
        $dst = imagecreatetruecolor($width, $height);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 255, 255, 255, 127);
        imagefilledrectangle($dst, 0, 0, $width, $height, $transparent);

        imagecopyresampled(
            $dst,
            $resource,
            0, //x-coordinate of destination point
            0, //y-coordinate of destination point
            floor(($imageWidth - $croppedWidth) / 2), //x-coordinate of source point
            floor(($imageHeight - $croppedHeight) / 2), //y-coordinate of source point
            $width, //Destination width
            $height, //Destination height
            $croppedWidth, //Source width
            $croppedHeight //Source height
        );

        return new static($dst);
    }

    /**
     * Save image to file
     *
     * @param string $path
     * @param int $quality - Ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file)
     */
    public function save(string $path, int $quality)
    {
        $this->saveFormat($this->format($path), $path, $quality);
    }

    public function saveFormat(string $format, string $path, int $quality)
    {
        $this->output($path, $format, $quality);
        $this->path = $path;
    }

    /**
     * Display image
     *
     * @param int $quality - Ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file)
     */
    public function display(int $quality)
    {
        if ($this->path) {
            $this->displayFormat($this->format($this->path), $quality);
        } else {
            $this->displayFormat('jpg', $quality);
        }
    }

    public function displayFormat(string $format, int $quality)
    {
        header('HTTP/1.1 200 OK');
        header('Content-type: '.$this->mime($format));

        $this->output(null, $format, $quality);
    }

    /**
     * Get mime type by format(extension)
     *
     * @param $format
     * @return string
     */
    protected function mime(string $format): string
    {
        switch ($format) {
            case 'png':
                return 'image/png';

            case 'gif':
                return 'image/gif';

//            case 'webp':
//                return 'image/webp';
//
//            case 'wbmp':
//                return 'image/vnd.wap.wbmp';

            case 'jpeg':
            case 'jpg':
            default:
                return 'image/jpeg';
        }
    }

    /**
     * Get format (extension) by image path
     *
     * @param $path
     * @return string
     */
    protected function format(string $path): string
    {
        return strtolower(array_last(explode('.', $path)));
    }

    /**
     * Save to file or display image
     *
     * @param string|null $path
     * @param string $format
     * @param int $quality
     */
    protected function output($path, string $format, int $quality)
    {
        switch ($format) {
            case 'png':
                //Compression level: from 0 (no compression) to 9. Does not effect image quality, only encoding/decoding speed
                @imagepng($this->resource, $path, 7);
                break;
            case 'gif':
                @imagegif($this->resource, $path);
                break;
//            case 'webp':
//                @imagewebp($this->resource, $path, $quality);
//                break;
//            case 'wbmp':
//                @imagewbmp($this->resource, $path);
//                break;
            case 'jpeg':
            case 'jpg':
            default:
                @imagejpeg($this->resource, $path, $quality);
                break;
        }
    }

    public function destroy()
    {
        imagedestroy($this->resource);
    }
}