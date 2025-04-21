<?php

use NckRtl\RouteMaker\Enums\HttpMethod;
use NckRtl\RouteMaker\Tests\Factories\ControllerFactory;
use NckRtl\RouteMaker\Tests\Traits\TestFixtures;

uses(TestFixtures::class);

/**
 * Test the controller factory for creating test controllers.
 */
test('it can generate controllers dynamically for testing', function () {
    // Create a dynamic controller using the factory
    $factory = ControllerFactory::create('DynamicController')
        ->withRoutePrefix('dynamic')
        ->withMiddleware(['auth', 'api'])
        ->addMethod('index')  // Will use GET by default
        ->addMethod('show', null, ['id'])  // GET with parameter
        ->addMethod('store', HttpMethod::POST, null, 'verified')  // POST with middleware
        ->addMethod('custom', HttpMethod::PUT, ['id'], ['throttle', 'cache'], 'custom.route', 'custom-uri');
    
    // Get the generated code
    $code = $factory->generate();
    
    // Check namespace and class structure
    expect($code)->toMatch('/namespace NckRtl\\\\RouteMaker\\\\Tests\\\\Http\\\\Controllers\\\\temp;/');
    expect($code)->toMatch('/class DynamicController extends Controller/');
    
    // Check controller properties
    expect($code)->toMatch('/protected static string \$routePrefix = \'dynamic\';/');
    expect($code)->toMatch('/protected static array \$routeMiddleware = \[\'auth\', \'api\'\];/');
    
    // Check method generation
    expect($code)->toMatch('/public function index\(\): Response/');
    expect($code)->toMatch('/public function show\(\$param\): Response/');
    
    // Check attribute usage
    expect($code)->toMatch('/#\[Route\(method: HttpMethod::POST, middleware: \'verified\'\)\]/');
    expect($code)->toMatch('/#\[Route\(method: HttpMethod::PUT, parameters: \[\'id\'\], middleware: \[\'throttle\', \'cache\'\], name: \'custom\.route\', uri: \'custom-uri\'\)\]/');
    
    // Check method content - return statements
    expect($code)->toMatch('/return inertia\(\'Index\'\);/');
    expect($code)->toMatch('/return inertia\(\'Show\', \[\s*\'param\' => \$param,\s*\]\);/');
});