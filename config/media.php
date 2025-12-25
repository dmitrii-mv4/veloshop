<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Настройки медиа сервиса
    |--------------------------------------------------------------------------
    |
    | Конфигурация для работы с медиа файлами (изображения, документы и т.д.)
    |
    */
    
    'default_disk' => env('MEDIA_DISK', 'public'),
    
    'allowed_mimes' => [
        'jpg',
        'jpeg',
        'png',
        'gif',
        'webp',
        'svg',
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
    ],
    
    'max_file_size' => 5242880, // 5MB в байтах
    
    'generate_thumbnails' => true,
    
    'thumbnail_sizes' => [
        'small' => [150, 150],
        'medium' => [300, 300],
        'large' => [800, 800],
    ],
    
    'image_quality' => 85,
    
    'keep_original' => true,
    
    /*
    |--------------------------------------------------------------------------
    | Пути для разных типов файлов
    |--------------------------------------------------------------------------
    */
    
    'paths' => [
        'avatars' => 'users/avatar',
        'documents' => 'documents',
        'images' => 'images',
        'videos' => 'videos',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Настройки драйвера для Intervention Image
    |--------------------------------------------------------------------------
    */
    
    'image_driver' => env('IMAGE_DRIVER', 'gd'), // gd или imagick
    
    /*
    |--------------------------------------------------------------------------
    | Настройки форматов изображений
    |--------------------------------------------------------------------------
    */
    
    'image_formats' => [
        'jpeg' => [
            'encoder' => \Intervention\Image\Encoders\JpegEncoder::class,
            'default_quality' => 85,
        ],
        'png' => [
            'encoder' => \Intervention\Image\Encoders\PngEncoder::class,
        ],
        'webp' => [
            'encoder' => \Intervention\Image\Encoders\WebpEncoder::class,
            'default_quality' => 85,
        ],
        'gif' => [
            'encoder' => \Intervention\Image\Encoders\GifEncoder::class,
        ],
    ],
];