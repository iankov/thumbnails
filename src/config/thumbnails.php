<?php

return [
    'create_folder_mode' => 0755,
    'quality' => [
        'jpeg' => 90, //Ranges from 0 (worst quality, smaller file) to 100 (best quality, biggest file)
        'png' => 6, //Compression level: from 0 (no compression) to 9.
    ],

    //regex for allowed image directories
    'dirs' => [
        'upload\/images\/[A-Za-z0-9\-\_\.]+',
    ],

    //available sizes
    'sizes' => [
        ['width' => 360, 'height' => 220, 'crop' => 1],
        ['width' => 150, 'height' => 100, 'crop' => 1],
    ],

    'use_package_routes' => true,
];