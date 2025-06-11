<?php

use NckRtl\RouteMaker\RouteMaker;

test('it handles multiple methods with the same HTTP verb and URI', function () {
    // Create a test controller with both index and show methods
    $testControllerPath = __DIR__.'/TestDuplicateController.php';
    file_put_contents($testControllerPath, '<?php
    namespace NckRtl\RouteMaker\Tests;
    
    use Illuminate\Routing\Controller;
    
    class TestDuplicateController extends Controller
    {
        public function index()
        {
            return "Index";
        }
        
        public function show()
        {
            return "Show";
        }
    }
    ');

    try {
        // Temporarily set controller path to our test location
        RouteMaker::setControllerPath(
            __DIR__,
            'NckRtl\\RouteMaker\\Tests'
        );

        $routes = RouteMaker::generateRouteDefinitions();

        // Convert routes to string for easier inspection
        $routesString = implode("\n", $routes);

        // Check if methods generate routes with different URIs
        expect($routesString)->toContain("Route::get('/test-duplicate', [\\NckRtl\\RouteMaker\\Tests\\TestDuplicateController::class, 'index'])")
            ->and($routesString)->toContain("Route::get('/test-duplicate/{id}', [\\NckRtl\\RouteMaker\\Tests\\TestDuplicateController::class, 'show'])")
            ->and($routesString)->toContain('Controllers.TestDuplicateController.index')
            ->and($routesString)->toContain('Controllers.TestDuplicateController.show');
    } finally {
        // Clean up test file
        if (file_exists($testControllerPath)) {
            unlink($testControllerPath);
        }
    }
});
