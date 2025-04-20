<?php

// config for NckRtl/RouteMaker
return [
    'paths' => [
        app_path('Http/Controllers'),
    ],

    'method_defaults' => [
        'GET' => ['index', 'show'],
        'POST' => ['store'],
        'PUT' => ['update'],
        'DELETE' => ['destroy'],
        'PATCH' => ['update'],
    ],
];
