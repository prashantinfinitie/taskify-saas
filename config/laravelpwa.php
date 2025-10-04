<?php
return [
    'name' => env('APP_NAME', 'Taskify Saas'),
    'manifest' => [
        'name' => env('APP_NAME', 'Taskify Saas'),
        'short_name' => 'Taskify Saas',
        'start_url' => '/',
        'background_color' => '#FFFFFF',
        'description' => 'Taskify SaaS is a project mangangement and task management system for handling tasks and projects. It facilitates collaboration, task allocation, scheduling, and tracking of project progress.',
        'theme_color' => '#000000',
        'display' => 'standalone',
        'orientation' => 'any',
        'status_bar' => 'black',
        'icons' => [
            [
                'path' => '/storage/logos/default_favicon.png',
                'sizes' => '192x192',
                'type' => 'image/png',
                'purpose' => 'any'
            ],
            [
                "src" => "/storage/logos/default_full_logo.png",
                "sizes" => "512x512",
                "type" => "image/png",
                'purpose' => 'any'
            ]
        ],

        'custom' => [
            // Remove default screenshots - will be set dynamically from settings
            'screenshots' => []
        ],
    ]
];