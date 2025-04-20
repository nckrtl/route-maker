<?php

use NckRtl\RouteMaker\Enums\HttpMethod;

// config for NckRtl/RouteMaker
return [
    'paths' => [
        app_path('Http/Controllers'),
    ],

    'method_defaults' => [
        HttpMethod::GET => ['index', 'show'],
        HttpMethod::POST => ['store'],
        HttpMethod::PUT => ['update'],
        HttpMethod::DELETE => ['destroy'],
        HttpMethod::PATCH => ['update'],
    ],
];
