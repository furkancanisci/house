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
        'original' => [
            'width' => 2400, // Ultra high quality for detailed property views
            'height' => 1600,
            'quality' => 95, // Maximum quality for property showcase
            'aspect_ratio' => '3:2',
            'format' => 'webp'
        ],
        'full' => [
            'width' => 1920, // Full HD for main gallery
            'height' => 1280,
            'quality' => 92,
            'aspect_ratio' => '3:2',
            'format' => 'webp'
        ],
        'large' => [
            'width' => 1200,
            'height' => 800,
            'quality' => 88,
            'aspect_ratio' => '3:2',
            'format' => 'webp'
        ],
        'medium' => [
            'width' => 800,
            'height' => 533,
            'quality' => 85,
            'aspect_ratio' => '3:2',
            'format' => 'webp'
        ],
        'thumbnail' => [
            'width' => 400,
            'height' => 300,
            'quality' => 80,
            'aspect_ratio' => '4:3',
            'format' => 'webp'
        ],
        'small' => [
            'width' => 300,
            'height' => 200,
            'quality' => 75,
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
        'max_file_size' => 10 * 1024 * 1024, // 10MB in bytes for high quality property images
        'allowed_mime_types' => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
        'allowed_extensions' => ['jpeg', 'jpg', 'png', 'webp'],
        'min_width' => 400,
        'min_height' => 300,
        'max_width' => 8000, // Maximum width for ultra-high quality images
        'max_height' => 6000, // Maximum height for ultra-high quality images
        'max_images_per_property' => 20,
        'optimize_large_images' => true, // Automatically optimize images over 2MB
        'preserve_quality_threshold' => 2 * 1024 * 1024 // Preserve original quality for images under 2MB
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