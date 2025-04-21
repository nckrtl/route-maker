<?php

use NckRtl\RouteMaker\Enums\HttpMethod;
use NckRtl\RouteMaker\RouteMaker;
use NckRtl\RouteMaker\Tests\Traits\TestFixtures;

uses(TestFixtures::class);

/**
 * Test that HTTP method defaults work correctly based on method names.
 */
test('it applies correct HTTP method defaults based on method names', function () {
    // Create a reflection method to access the protected getMethodDefault method
    $reflectionClass = new ReflectionClass(RouteMaker::class);
    $getMethodDefaultMethod = $reflectionClass->getMethod('getMethodDefault');
    $getMethodDefaultMethod->setAccessible(true);
    
    // Set up configurations for the test
    config(['route-maker.method_defaults' => [
        'GET' => ['index', 'show'],
        'POST' => ['store'],
        'PUT' => ['update'],
        'DELETE' => ['destroy'],
        'PATCH' => ['edit'],
    ]]);
    
    // Create an instance of RouteMaker to invoke the method
    $routeMaker = new RouteMaker();
    
    // Test each method name default
    $methodDefaults = [
        'index' => HttpMethod::GET,
        'show' => HttpMethod::GET,
        'store' => HttpMethod::POST,
        'update' => HttpMethod::PUT,
        'destroy' => HttpMethod::DELETE,
        'edit' => HttpMethod::PATCH,
        'something_else' => null, // Should return null for unknown methods
    ];
    
    foreach ($methodDefaults as $methodName => $expectedMethod) {
        $result = $getMethodDefaultMethod->invoke(null, $methodName);
        
        if ($expectedMethod === null) {
            expect($result)->toBeNull();
        } else {
            expect($result)->toBeInstanceOf(HttpMethod::class);
            expect($result)->toBe($expectedMethod);
        }
    }
});