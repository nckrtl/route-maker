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

class MiddlewareTestController extends Controller
{
    protected static string $routePrefix = 'middleware-test';
    protected static array $routeMiddleware = ['controller-mw'];

    #[Route(method: HttpMethod::POST, middleware: 'method-mw')]
    public function store(): Response
    {
        return inertia('Middleware/Store');
    }

    #[Route(method: HttpMethod::GET, middleware: ['method-mw', 'another-mw'])]
    public function index(): Response
    {
        return inertia('Middleware/Index');
    }

    #[Route(method: HttpMethod::GET, uri: 'no-method-mw')]
    public function noMethodMiddleware(): Response
    {
        return inertia('Middleware/NoMethod');
    }

    #[Route(method: HttpMethod::PUT, middleware: ['controller-mw', 'unique-method-mw'])]
    public function update(): Response
    {
        return inertia('Middleware/Update');
    }
}
PHP;

    file_put_contents($tempPath.'/MiddlewareTestController.php', $controllerContent);

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

test('it correctly merges controller and method middleware', function () {
    $definitions = RouteMaker::generateRouteDefinitions();

    $namespace = 'NckRtl\\RouteMaker\\Tests\\Http\\Controllers\\temp';

    $expectedPostRoute = "Route::post('/middleware-test', [\\".$namespace."\\MiddlewareTestController::class, 'store'])->name('middleware-test.store')->middleware(['controller-mw', 'method-mw']);";
    $expectedGetRoute = "Route::get('/middleware-test', [\\".$namespace."\\MiddlewareTestController::class, 'index'])->name('middleware-test.index')->middleware(['controller-mw', 'method-mw', 'another-mw']);";
    $expectedNoMethodMwRoute = "Route::get('/no-method-mw', [\\".$namespace."\\MiddlewareTestController::class, 'noMethodMiddleware'])->name('middleware-test.noMethodMiddleware')->middleware('controller-mw');";
    $expectedPutRoute = "Route::put('/middleware-test', [\\".$namespace."\\MiddlewareTestController::class, 'update'])->name('middleware-test.update')->middleware(['controller-mw', 'unique-method-mw']);";

    expect($definitions)->toContain($expectedPostRoute);
    expect($definitions)->toContain($expectedGetRoute);
    expect($definitions)->toContain($expectedNoMethodMwRoute);
    expect($definitions)->toContain($expectedPutRoute);
});
