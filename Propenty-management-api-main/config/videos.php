<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Video Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for video file upload validation and processing for
    | property management system. These settings ensure proper video handling
    | while maintaining reasonable file sizes and quality.
    |
    */
    'upload' => [
        'max_file_size' => 500 * 1024 * 1024, // 500MB in bytes for property videos
        'allowed_mime_types' => [
            'video/mp4',
            'video/avi', 
            'video/quicktime', // .mov files
            'video/x-msvideo', // .avi files
            'video/webm'
        ],
        'allowed_extensions' => ['mp4', 'avi', 'mov', 'wmv', 'webm'],
        'max_videos_per_property' => 1,
        'max_duration' => 600, // 10 minutes in seconds
        'min_duration' => 5, // 5 seconds minimum
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for video storage paths and organization.
    |
    */
    'storage' => [
        'disk' => 'public',
        'path' => 'properties/videos',
        'temp_path' => 'temp/videos',
    ],

    /*
    |--------------------------------------------------------------------------
    | Processing Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for video processing behavior.
    |
    */
    'processing' => [
        'generate_thumbnail' => true,
        'thumbnail_time' => 5, // Generate thumbnail at 5 seconds
        'compress_large_videos' => false, // Disable compression for now
        'auto_convert_format' => false, // Keep original format
    ]
];