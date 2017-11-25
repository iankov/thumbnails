<?php

if (! function_exists('thumbnail_url')) {
    /**
     * return thumbnail url based on original image url
     *
     * @param  string $imageUrl
     * @param  string $size
     * @return string
     */
    function thumbnail_url($imageUrl, $size = '')
    {
        /*if (empty($imageUrl)) {
            if (empty($size))
                return '/upload/default/default_image.jpg';
            else
                return '/upload/default/' . $size . '/default_image.jpg';
        }*/

        if (empty($size))
            return $imageUrl;

        $thumbnail = $imageUrl;
        $pos = strrpos($imageUrl, '/');
        if ($pos !== false)
            $thumbnail = substr_replace($imageUrl, '/' . $size . '/', $pos, 1);

        return $thumbnail;
    }
}