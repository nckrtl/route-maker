<?php

use NckRtl\RouteMaker\RouteMaker;
use NckRtl\RouteMaker\Tests\Traits\TestFixtures;

uses(TestFixtures::class);

/**
 * Test the URI generation logic in RouteMaker.
 */
test('it correctly generates URIs for different scenarios', function () {
    // Create a reflection method to access the protected method
    $reflectionClass = new ReflectionClass(RouteMaker::class);
    $generateUriMethod = $reflectionClass->getMethod('generateUri');
    $generateUriMethod->setAccessible(true);

    // Test cases for generateUri
    $testCases = [
        // prefix, customUri, parameters, controllerName, methodName, expected
        [null, null, null, 'UserController', 'index', '/user'],
        [null, null, null, 'UserController', 'show', '/user'],
        ['admin', null, null, 'UserController', 'index', '/admin'],
        [null, 'custom/uri', null, 'UserController', 'index', '/custom/uri'],
        [null, '/custom/uri', null, 'UserController', 'index', '/custom/uri'],
        ['admin', 'custom/uri', null, 'UserController', 'index', '/custom/uri'],
        [null, null, ['id'], 'UserController', 'show', '/user/{id}'],
        [null, null, ['user:uuid'], 'UserController', 'show', '/user/{user:uuid}'],
        ['admin', null, ['id'], 'UserController', 'show', '/admin/{id}'],
        ['admin', null, ['id', 'comment'], 'UserController', 'show', '/admin/{id}/{comment}'],
        ['', null, null, 'UserController', 'index', '/user'],
        [null, '/', null, 'UserController', 'index', '/'],
    ];

    // Create a RouteMaker instance
    $routeMaker = new RouteMaker;

    // Test each case
    foreach ($testCases as [$prefix, $customUri, $parameters, $controllerName, $methodName, $expected]) {
        $uri = $generateUriMethod->invokeArgs(null, [$prefix, $customUri, $parameters, $controllerName, $methodName]);
        expect($uri)->toBe($expected);
    }
});
