<?php

// config for NckRtl/RouteMaker
return [
    'method_defaults' => [
        'GET' => ['index', 'show'],
        'POST' => ['store'],
        'PUT' => ['update'],
        'DELETE' => ['destroy'],
        'PATCH' => ['update'],
    ],
];
