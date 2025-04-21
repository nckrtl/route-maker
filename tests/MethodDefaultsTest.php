<?php

use NckRtl\RouteMaker\RouteMaker;

beforeEach(function () {
    $tempPath = __DIR__.'/Http/Controllers/temp';
    if (! is_dir($tempPath)) {
        mkdir($tempPath, 0777, true);
    }

    $controllerContent = <<<'PHP'
<?php

namespace NckRtl\RouteMaker\Tests\Http\Controllers\temp;

use Illuminate\Routing\Controller;
use Inertia\Response;
use NckRtl\RouteMaker\Route;
use NckRtl\RouteMaker\Enums\HttpMethod;

class MethodDefaultsTestController extends Controller
{
    protected static string $routePrefix = 'test';

    // Method with no explicit HTTP method - should use default GET for 'index'
    public function index(): Response
    {
        return inertia('Test/Index');
    }

    // Method with no explicit HTTP method - should use default GET for 'show'
    public function show(): Response
    {
        return inertia('Test/Show');
    }

    // Method with no explicit HTTP method - should use default POST for 'store'
    public function store(): Response
    {
        return inertia('Test/Store');
    }

    // Method with no explicit HTTP method - should use default PUT/PATCH for 'update'
    public function update(): Response
    {
        return inertia('Test/Update');
    }

    // Method with no explicit HTTP method - should use default DELETE for 'destroy'
    public function destroy(): Response
    {
        return inertia('Test/Destroy');
    }

    // Method with explicit HTTP method - should override default
    #[Route(method: HttpMethod::GET)]
    public function store_override(): Response
    {
        return inertia('Test/StoreOverride');
    }
}
PHP;

    file_put_contents($tempPath.'/MethodDefaultsTestController.php', $controllerContent);

    RouteMaker::setControllerPath(
        $tempPath,
        'NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp'
    );
});

afterEach(function () {
    $tempPath = __DIR__.'/Http/Controllers/temp';
    if (is_dir($tempPath)) {
        system('rm -rf '.escapeshellarg($tempPath));
    }
});

test('it applies correct HTTP method defaults based on method names', function () {
    $routes = RouteMaker::generateRouteDefinitions();

    $namespace = 'NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp';

    // Test default GET methods
    $expectedIndexRoute = "Route::get('/test', [\\".$namespace."\\MethodDefaultsTestController::class, 'index'])->name('Controllers.MethodDefaultsTestController.index');";
    $expectedShowRoute = "Route::get('/test', [\\".$namespace."\\MethodDefaultsTestController::class, 'show'])->name('Controllers.MethodDefaultsTestController.show');";

    // Test default POST method
    $expectedStoreRoute = "Route::post('/test', [\\".$namespace."\\MethodDefaultsTestController::class, 'store'])->name('Controllers.MethodDefaultsTestController.store');";

    // Test default PUT method
    $expectedUpdateRoute = "Route::put('/test', [\\".$namespace."\\MethodDefaultsTestController::class, 'update'])->name('Controllers.MethodDefaultsTestController.update');";

    // Test default DELETE method
    $expectedDestroyRoute = "Route::delete('/test', [\\".$namespace."\\MethodDefaultsTestController::class, 'destroy'])->name('Controllers.MethodDefaultsTestController.destroy');";

    // Test explicit method override
    $expectedOverrideRoute = "Route::get('/test', [\\".$namespace."\\MethodDefaultsTestController::class, 'store_override'])->name('Controllers.MethodDefaultsTestController.store_override');";

    expect($routes)->toContain($expectedIndexRoute);
    expect($routes)->toContain($expectedShowRoute);
    expect($routes)->toContain($expectedStoreRoute);
    expect($routes)->toContain($expectedUpdateRoute);
    expect($routes)->toContain($expectedDestroyRoute);
    expect($routes)->toContain($expectedOverrideRoute);
});
