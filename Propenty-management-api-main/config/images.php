<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image Quality Settings
    |--------------------------------------------------------------------------
    |
    | Define quality settings for different image sizes used in the property
    | management system. These settings ensure optimal image quality for
    | real estate photos while maintaining reasonable file sizes.
    |
    */
    'quality_settings' => [
        'full' => [
            'width' => 1200,
            'height' => 800,
            'quality' => 90,
            'aspect_ratio' => '3:2',
            'format' => 'webp'
        ],
        'large' => [
            'width' => 800,
            'height' => 533,
            'quality' => 85,
            'aspect_ratio' => '3:2',
            'format' => 'webp'
        ],
        'medium' => [
            'width' => 600,
            'height' => 400,
            'quality' => 80,
            'aspect_ratio' => '3:2',
            'format' => 'webp'
        ],
        'thumbnail' => [
            'width' => 400,
            'height' => 300,
            'quality' => 75,
            'aspect_ratio' => '4:3',
            'format' => 'webp'
        ],
        'small' => [
            'width' => 300,
            'height' => 200,
            'quality' => 70,
            'aspect_ratio' => '3:2',
            'format' => 'webp'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for file upload validation and processing.
    |
    */
    'upload' => [
        'max_file_size' => 5 * 1024 * 1024, // 5MB in bytes
        'allowed_mime_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
        'allowed_extensions' => ['jpeg', 'jpg', 'png', 'webp'],
        'min_width' => 400,
        'min_height' => 300,
        'max_images_per_property' => 20
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for image storage paths and organization.
    |
    */
    'storage' => [
        'disk' => 'public',
        'path' => 'properties',
        'temp_path' => 'temp',
        'conversions_path' => 'conversions'
    ],

    /*
    |--------------------------------------------------------------------------
    | Processing Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for image processing behavior.
    |
    */
    'processing' => [
        'maintain_aspect_ratio' => true,
        'upscale_small_images' => false,
        'auto_orient' => true,
        'strip_metadata' => true,
        'progressive_jpeg' => true
    ]
];